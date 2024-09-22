<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\LiqPayController;
use App\Http\Controllers\PaymobController;
use App\Http\Controllers\PaytabsController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\RazorPayController;
use App\Http\Controllers\SenangPayController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\BkashPaymentController;
use App\Http\Controllers\FlutterwaveV3Controller;
use App\Http\Controllers\PaypalPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Models\BusinessSetting;
use App\Models\DMVehicle;
use App\Models\Item;
use App\Models\Module;
use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\FirebaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::post('/subscribeToTopic', [FirebaseController::class, 'subscribeToTopic']);
Route::get('/', 'HomeController@index')->name('home');
Route::get('lang/{locale}', 'HomeController@lang')->name('lang');
Route::get('terms-and-conditions', 'HomeController@terms_and_conditions')->name('terms-and-conditions');
Route::get('about-us', 'HomeController@about_us')->name('about-us');
Route::get('contact-us', 'HomeController@contact_us')->name('contact-us');
Route::post('send-message', 'HomeController@send_message')->name('send-message');
Route::get('privacy-policy', 'HomeController@privacy_policy')->name('privacy-policy');
Route::get('cancelation', 'HomeController@cancelation')->name('cancelation');
Route::get('refund', 'HomeController@refund_policy')->name('refund');
Route::get('shipping-policy', 'HomeController@shipping_policy')->name('shipping-policy');
Route::post('newsletter/subscribe', 'NewsletterController@newsLetterSubscribe')->name('newsletter.subscribe');
Route::get('subscription-invoice/{id}', 'HomeController@subscription_invoice')->name('subscription_invoice');

Route::get('login/{tab}', 'LoginController@login')->name('login');
Route::post('login_submit', 'LoginController@submit')->name('login_post')->middleware('actch');
Route::get('logout', 'LoginController@logout')->name('logout');
Route::get('/reload-captcha', 'LoginController@reloadCaptcha')->name('reload-captcha');
Route::get('/reset-password', 'LoginController@reset_password_request')->name('reset-password');
Route::post('/vendor-reset-password', 'LoginController@vendor_reset_password_request')->name('vendor-reset-password');
Route::get('/password-reset', 'LoginController@reset_password')->name('change-password');
Route::post('verify-otp', 'LoginController@verify_token')->name('verify-otp');
Route::post('reset-password-submit', 'LoginController@reset_password_submit')->name('reset-password-submit');
Route::get('otp-resent', 'LoginController@otp_resent')->name('otp_resent');

Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthenticated.']);
    return response()->json([
        'errors' => $errors,
    ], 401);
})->name('authentication-failed');

Route::group(['prefix' => 'payment-mobile'], function () {
    Route::get('/', 'PaymentController@payment')->name('payment-mobile');
    Route::get('set-payment-method/{name}', 'PaymentController@set_payment_method')->name('set-payment-method');
});

Route::get('payment-success', 'PaymentController@success')->name('payment-success');
Route::get('payment-fail', 'PaymentController@fail')->name('payment-fail');
Route::get('payment-cancel', 'PaymentController@cancel')->name('payment-cancel');

$is_published = 0;
try {
$full_data = include('Modules/Gateways/Addon/info.php');
$is_published = $full_data['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {}

if (!$is_published) {
    Route::group(['prefix' => 'payment'], function () {

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment']);
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay']);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'callback'])->name('response')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback');
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
        });

        //BKASH

        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            // Payment Routes for bKash
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback');

            // Refund Routes for bKash
            // Route::get('refund', 'BkashRefundController@index')->name('bkash-refund');
            // Route::post('refund', 'BkashRefundController@refund')->name('bkash-refund');
        });

        //Liqpay
        Route::group(['prefix' => 'liqpay', 'as' => 'liqpay.'], function () {
            Route::get('payment', [LiqPayController::class, 'payment'])->name('payment');
            Route::any('callback', [LiqPayController::class, 'callback'])->name('callback');
        });

        //MERCADOPAGO
        Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::post('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment');
            Route::get('success', [MercadoPagoController::class, 'success'])->name('success');
            Route::get('failed', [MercadoPagoController::class, 'failed'])->name('failed');
        });

        //PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback');
        });

        //PAYTABS
        Route::group(['prefix' => 'paytabs', 'as' => 'paytabs.'], function () {
            Route::any('pay', [PaytabsController::class, 'payment'])->name('pay');
            Route::any('callback', [PaytabsController::class, 'callback'])->name('callback')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::any('response', [PaytabsController::class, 'response'])->name('response');
        });
    });
}


Route::get('/test', function () {
    // Artisan::call('migrate', ['--force' => true]);
    // dd('h');

    $setting =  BusinessSetting::where('key','offline_payment_status')->first()?->value;
    if($setting == "1"){
        dd('jh');
    }

    $reference =Uuid::uuid1()->toString();
    $data = [
        "transactionName" => "Test#PRODUCT",
        "amount" => 10,
        "currency" => "ZMW",
        "transactionReference" => $reference,
        "customerFirstName" => "Test Rashed",
        "customerLastName" => "rashed",
        "customerEmail" => "rnrashedrn@gmail.com",
        "customerPhone" => "01827801715",
        "customerAddr" => "Test",
        "customerCity" => "Lusaka",
        "customerState" => "Lusaka",
        "customerCountryCode" => "ZM",
        "customerPostalCode" => "12345",
        "merchantPublicKey" => "53f8a39359bd4ffc8adda701a2b3542f",
        "webhookUrl" => route('paytabs.callback'),
        'returnUrl' => route('paytabs.callback'),
        "autoReturn" => true
];
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://checkout.broadpay.io/gateway/api/v1/checkout',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
      ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response,true);
    if(isset($result) && $result['paymentUrl']){
        return redirect()->to($result['paymentUrl']);
    }
    return 'payment failed';

    Item::where('store_id',27)->update([
        'store_id' => 2727,
]);

    $url = 'https://sms.engicell.com/api/http/sms/send';

    $data = array(
        'api_token' => '2|kxADuTJLJQNiE5uKBYjOCAc5JjFYL7iUlCYKsJmed267f444',
        'recipient' => '260974079881',
        'sender_id' => 'MIXMRT',
        'type' => 'plain',
        'message' => 'This is a test message from 6amTech'
    );

    $headers = array(
        'Content-Type: application/json',
        'Accept: application/json'
    );

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result,true);
    dd($response);
    dd($response['status'] == 'success');

    //pg
    //public key : 53f8a39359bd4ffc8adda701a2b3542f
    //secret key : 1771b42081d2473ba0c794dcd80e0dc5

$client = new Client();
$headers = [
  'Content-Type' => 'application/json'
];
$body = '{
  "transactionName": "Item Name",
  "amount": 1,
  "currency": "USD",
  "transactionReference": "2901f6b9-9eb4-4b96-b9f9-1b5c478ab209",
  "customerFirstName": "Mundia",
  "customerLastName": "Mwala",
  "customerEmail": "mundia@getsparco.com",
  "customerPhone": "0961453688",
  "merchantPublicKey": "53f8a39359bd4ffc8adda701a2b3542f"
}';
$request = new Request('POST', 'https://checkout.sparco.io/gateway/api/v1/checkout', $headers, $body);
$res = $client->sendAsync($request)->wait();
dd($res->getBody());



    $data = [
            "transactionName" => "Test#567ghj",
            "amount" => 1,
            "currency" => "ZMW",
            "transactionReference" => "5dd7-4899-994d-af8df0b14f",
            "customerFirstName" => "Test Rashed",
            "customerLastName" => "rasjhed",
            "customerEmail" => "test@gmail.com",
            "customerPhone" => "260974079881",
            "customerAddr" => "Test",
            "customerCity" => "Lusaka",
            "customerState" => "Lusaka",
            "customerCountryCode" => "ZM",
            "customerPostalCode" => "12345",
            "merchantPublicKey" => "53f8a39359bd4ffc8adda701a2b3542f",
            "webhookUrl" => "https://webhook.site/5e82155c-7c4f-4263-9f95-2cb7182db7f4",
            "autoReturn" => true
    ];
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://checkout.sparco.io/gateway/api/v1/checkout',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "transactionName": "Item Name",
    "amount": 1,
    "currency": "ZMW",
    "transactionReference": "eetcv4c20-b027-479c-83ff-f7e055a47871",
    "customerFirstName": "Mundia",
    "customerLastName": "Mwala",
    "customerEmail": "mundia@getsparco.com",
    "customerPhone": "0961453688",
    "merchantPublicKey": "53f8a39359bd4ffc8adda701a2b3542f"
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
// return view('payment-canceled',compact('response'));
dd($response);

    //2|Vj8mv2b6N5DrBkSaA2c0xoRF3qukws9GKXwxleOHa6698243
    $url = 'https://sms.engicell.com/api/v3/sms/send';

$data = array(
    'recipient' => '260974079881',
    'sender_id' => 'MIXMRT',
    'type' => 'plain',
    'message' => 'This is a test message from 6amTech'
);

$headers = array(
    'Authorization: Bearer 2|Vj8mv2b6N5DrBkSaA2c0xoRF3qukws9GKXwxleOHa6698243',
    'Content-Type: application/json',
    'Accept: application/json'
);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
curl_close($ch);
$response = json_decode($result,true);
dd($response);
 dd($response['status'] == 'success');
    dd('Hello tester');
});

Route::get('module-test', function () {
});

//Restaurant Registration
Route::group(['prefix' => 'store', 'as' => 'restaurant.'], function () {
    Route::get('apply', 'VendorController@create')->name('create');
    Route::post('apply', 'VendorController@store')->name('store');
    Route::get('get-all-modules', 'VendorController@get_all_modules')->name('get-all-modules');
    Route::get('download-store-agreement', 'VendorController@download_store_agereement')->name('download-store-agreement');
    Route::get('download-courier-company-agreement', 'VendorController@download_courier_company_agereement')->name('download-courier-company-agreement');
    Route::get('back', 'VendorController@back')->name('back');
    Route::post('business-plan', 'VendorController@business_plan')->name('business_plan');
    Route::post('payment', 'VendorController@payment')->name('payment');
    Route::get('final-step', 'VendorController@final_step')->name('final_step');
});
Route::group(['prefix' => 'company', 'as' => 'company.'], function () {
    Route::get('apply', 'VendorController@company_create')->name('create');
    Route::post('apply', 'VendorController@company_store')->name('store');


});

//Deliveryman Registration
Route::group(['prefix' => 'deliveryman', 'as' => 'deliveryman.'], function () {
    Route::get('apply', 'DeliveryManController@create')->name('create');
    Route::post('apply', 'DeliveryManController@store')->name('store');
    Route::get('download-delivery-man-agreement', 'DeliveryManController@download_dm_agereement')->name('download-delivery-man-agreement');
});

Route::get('show-agreement/{key}', 'VendorController@showAgreement')->name('show-agreement');
