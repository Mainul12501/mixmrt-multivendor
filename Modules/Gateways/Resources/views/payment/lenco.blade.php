<!DOCTYPE html>
<html lang="en">
<head>
  <title>LENCO</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <button id="payWithLenco" style="display:none;" onclick="getPaidWithLenco()"></button>
   @if ($config['mode'] == 'test')
   <script src="https://pay.sandbox.lenco.co/js/v1/inline.js"></script>
       @else
       <script src="https://pay.lenco.co/js/v1/inline.js"></script>
   @endif

<script>
function getPaidWithLenco() {
	LencoPay.getPaid({
		key: "{{ $config['public_key'] }}", // your Lenco public key
		reference: "{{ $payment_data['transaction_id'] }}", // a unique reference you generated
		email: "{{ $customer['email'] }}", // the customer's email address
		amount: {{ $payment_data['payment_amount'] }}, // the amount the customer is to pay
		currency: "ZMW",
		channels: ["card", "mobile-money"],
		customer: {
			firstName: "{{ $customer['fname'] }}",
			lastName: "{{ $customer['lname'] }}",
			phone: "{{ $customer['phone'] }}",
		},
		onSuccess: function (response) {
             const reference = response.reference;
                 window.location = "{{ route('lenco.callback') }}?reference=" + reference;
		},
		onClose: function () {
            const reference = "{{ $payment_data['id'] }}";
            window.location = "{{ route('lenco.cancel') }}?reference=" + reference;
		},
	});
}
document.addEventListener('DOMContentLoaded', function() {

getPaidWithLenco();

});
</script>
</body>
</html>

