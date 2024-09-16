<?php

namespace Modules\Gateways\Http\Controllers;


use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\Processor;
use Modules\Gateways\Entities\PaymentRequest;
use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;

class LencoPayController extends Controller
{
    use Processor;

    private mixed $config_values;

    private PaymentRequest $payment;
    private User $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('lenco', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values,true);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values,true);
        }
        $this->payment = $payment;
        $this->user = $user;
    }

    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payer = json_decode($payment_data['payer_information']);
        $reference = $payment_data->attribute_id .time();
        $payment_data->transaction_id = $reference;
        $payment_data->save();
        $config = $this->config_values;
        $fullname =  $payer->name;
        $nameParts = explode(" ", $fullname);
        $customer['fname'] = $nameParts[0];
        $customer['lname'] = isset($nameParts[1]) ? $nameParts[1] : 'rashed';
        $customer['email'] = $payer->email;
        $customer['phone'] = $payer->phone ?? "01827800000";

        return view('Gateways::payment.lenco', compact('payment_data', 'payer', 'customer','config'));

    }

    public function cancel(Request $request)
    {
        $payment_data = $this->payment::where(['id' => $request->reference])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');

    }
    public function callback(Request $request)
    {
        info('success');
        info(request()->all());
        $reference =$request->reference; // Replace with your actual reference
        $config = $this->config_values;
        $apiSecretKey = $config['secret_key']; // Replace with your actual API secret key
        $url = $config['mode'] == 'test' ? 'https://sandbox.lenco.co/access/v2/collections/status/' . $reference : 'https://api.lenco.co/access/v2/collections/status/' . $reference;

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $apiSecretKey,
            'Accept: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
       $result =  json_decode($response,true);
     if(isset($result) && isset($result['data']) && $result['data']['status'] == 'successful'){
        $reference_id = $result['data']['reference'];
        $this->payment::where(['transaction_id' => $reference_id])->update([
            'payment_method' => 'broadpay',
            'is_paid' => 1,
            'transaction_id' => $request->reference,
        ]);

        $payment_data = $this->payment::where(['transaction_id' => $reference_id])->first();

        if (isset($payment_data) && function_exists($payment_data->success_hook)) {
            call_user_func($payment_data->success_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'success');
     }
     $payment_data = $this->payment::where(['transaction_id' => $request->merchantReference])->first();
     if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
         call_user_func($payment_data->failure_hook, $payment_data);
     }
     return $this->payment_response($payment_data, 'fail');
    }
}
