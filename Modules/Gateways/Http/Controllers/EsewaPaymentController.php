<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\Processor;
use Modules\Gateways\Entities\PaymentRequest;
use Ramsey\Uuid\Nonstandard\Uuid;

class EsewaPaymentController extends Controller
{
    use Processor;

    private mixed $config_values;
    private $merchantCode;
    private string $config_mode;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('esewa', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if ($config) {
            $this->merchantCode = $this->config_values->merchantCode;
            $this->config_mode = ($config->mode == 'test') ? 'test' : 'live';
        }

        $this->payment = $payment;
    }

    public function payment(Request $req): View|Application|Factory|JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        $validator = Validator::make($req->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $req['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $config_val = $this->config_values;
        $config_mode = $this->config_mode;

        $uuid = Uuid::uuid4()->toString();

        $data->transaction_id = $uuid;
        $data->save();

        $code = $config_val->merchantCode;
        $key = $config_val->merchant_secret;
        $amount = $data->payment_amount;
        $message = "total_amount=$amount,transaction_uuid=$uuid,product_code=$code";
        $s = hash_hmac('sha256', $message, $key, true);
        $signature = base64_encode($s);

        return view('Gateways::payment.esewa', compact('data', 'config_val', 'config_mode','signature','uuid'));
    }

    public function verify(Request $request, $payment_id): Application|JsonResponse|int|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $config_val = $this->config_values;
        $config_mode = $this->config_mode;
        $url = $config_mode == 'test' ? "https://uat.esewa.com.np/api/epay/transaction/status/" : "https://esewa.com.np/api/epay/transaction/status/";

        $payment_data = $this->payment::where(['transaction_id' => $payment_id])->first();
        if (!isset($payment_data)) {
            return response()->json(['message' => 'Payment failed'], 403);
        }

        $product_code = $config_val->merchantCode;
        $total_amount = $payment_data->payment_amount;
        $transaction_uuid = $payment_id;

        // Build the URL with parameters
        $url .= '?product_code=' . urlencode($product_code);
        $url .= '&total_amount=' . urlencode($total_amount);
        $url .= '&transaction_uuid=' . urlencode($transaction_uuid);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL session
        $response = curl_exec($ch);
        // Close cURL session
        curl_close($ch);

        // Display the response
        $result = json_decode($response,true);
        if (isset($result['status']) && $result['status'] == 'COMPLETE') {
            $this->payment::where(['transaction_id' => $payment_id])->update([
                'payment_method' => 'esewa',
                'is_paid' => 1,
                'transaction_id' => $result['ref_id'],
            ]);
            $payment_data = $this->payment::where(['transaction_id' => $result['ref_id']])->first();
            if (isset($payment_data) && function_exists($payment_data->success_hook)) {
                call_user_func($payment_data->success_hook, $payment_data);
            }

            return $this->payment_response($payment_data, 'success');
        }
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }

}
