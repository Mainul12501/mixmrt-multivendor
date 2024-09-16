<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\Processor;
use Modules\Gateways\Entities\PaymentRequest;

class XenditPaymentController extends Controller
{
    use Processor;

    private mixed $config_values;

    private PaymentRequest $payment;
    private $api_key;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('xendit', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if ($config) {
            $this->api_key = $this->config_values->api_key;
        }

        $this->payment = $payment;
    }

    public function payment(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $payer = json_decode($payment_data['payer_information']);

        $params = [
            'external_id' => 'xendit_' . $request['payment_id'],
            'payer_email' => $payer->email == "" ? 'test@example.com' : $payer->email,
            'description' => 'Xendit',
            'amount' => $payment_data->payment_amount * 1000,
            'customer' => [
                'given_names' => $payer->name,
                'email' => $payer->email == "" ? 'test@example.com' : $payer->email,
                'mobile_number' => $payer->phone,
            ],
            'customer_notification_preference' => [
                'invoice_created' => [
                    'whatsapp',
                    'sms',
                    'email',
                ],
                'invoice_paid' => [
                    'whatsapp',
                    'sms',
                    'email',
                ],
            ],

            'success_redirect_url' => route('xendit.callback', ['payment_id' => $request['payment_id']]),
            'failure_redirect_url' => route('xendit.callback', ['payment_id' => $request['payment_id']]),

        ];

        $headers = [];
        $headers[] = 'Content-Type: application/json';

        $curl = curl_init();
        $payload = json_encode($params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERPWD, $this->api_key.":");
        curl_setopt($curl, CURLOPT_URL, 'https://api.xendit.co/v2/invoices');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_close($curl);
        $response = json_decode(curl_exec($curl));

        if(isset($response->error_code)){
            return $this->payment_response($payment_data, 'fail');
        }

        $this->payment::where(['id' => $request['payment_id']])->update([
            'transaction_id' => $response->id,
        ]);
        return redirect()->to($response->invoice_url);
    }

    public function callBack(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->first();

        $curl = curl_init();
        $headers = [];
        $headers[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_USERPWD, $this->api_key.":");
        curl_setopt($curl, CURLOPT_URL, 'https://api.xendit.co/v2/invoices/'.$data->transaction_id);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_close($curl);

        $response = json_decode(curl_exec($curl));
        if(isset($response->error_code)){
            return $this->payment_response($data, 'fail');
        }

        $external_id = $response->external_id;

        if ($response->status == 'PAID') {
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'xendit',
                'is_paid' => 1,
                'transaction_id' => $external_id,
            ]);
            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }

            return $this->payment_response($data, 'success');
        }
        if (isset($data) && function_exists($data->failure_hook)) {
            call_user_func($data->failure_hook, $data);
        }
        return $this->payment_response($data, 'fail');
    }
}
