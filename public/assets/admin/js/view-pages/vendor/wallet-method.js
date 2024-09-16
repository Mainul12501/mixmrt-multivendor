"use strict";


$('.showMyModal').on('click', function (){
    let data = $(this).data('message');
    showMyModal(data);
})

function showMyModal(data) {
    $(".modal-body #hiddenValue").html(data);
    $('#exampleModal').modal('show');
}

$('.withdrawal-methods-disable').on('click', function (){
    toastr.info( $(this).data('message') , {
        CloseButton: true,
        ProgressBar: true
    });
})
$(document).on('ready', function () {
// INITIALIZATION OF SELECT2
// =======================================================
$('.js-select2-custom').each(function () {
var select2 = $.HSCore.components.HSSelect2.init($(this));
});

$('#payment_method').on('change', function() {
console.log('payment_method');
if($('#payment_method').val() == 'offline')
{
    $('#offline_payment').removeClass("d-none");
    $('#online_payment').addClass("d-none");
    $("input[type=radio][name=payment_gateway]").prop('checked', false);
}
else if($('#payment_method').val() == 'online')
{
    $('#offline_payment').addClass("d-none");
    $('#online_payment').removeClass("d-none");
    $("input[type=radio][name=payment_gateway]").prop('checked', false);
}
else if($('#payment_method').val() == 'select_payment_type')
    {
        $('#offline_payment').addClass("d-none");
        $('#online_payment').addClass("d-none");
        $("input[type=radio][name=payment_gateway]").prop('checked', false);
    }
});
});