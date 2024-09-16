"use strict";
$(document).on('ready', function () {
    $('#discount_type').on('change', function() {
        if($('#discount_type').val() == 'amount')
        {
            $('#max_discount').attr("readonly","true");
            $('#max_discount').val(0);
        }
        else
        {
            $('#max_discount').removeAttr("readonly");
        }
    });
    let module_id = 0;
    $('#module_select').on('change', function(){
        if($(this).val())
        {
            module_id = $(this).val();
        }
    });
});
$("#date_from").on("change", function () {
    $('#date_to').attr('min',$(this).val());
});

$("#date_to").on("change", function () {
    $('#date_from').attr('max',$(this).val());
});

$('#coupon_type').on('change',function () {
    let coupon_type = $(this).val();
    coupon_type_change(coupon_type)
})
function coupon_type_change(coupon_type) {
    if(coupon_type=='zone_wise')
    {
        $('#store_wise').hide();
        $('#zone_wise').show();
        $('#customer_wise').hide();
    }
    else if(coupon_type=='store_wise')
    {
        $('#store_wise').show();
        $('#zone_wise').hide();
        $('#customer_wise').show();
    }
    else if(coupon_type=='first_order')
    {
        $('#zone_wise').hide();
        $('#store_wise').hide();
        $('#customer_wise').hide();
        $('#coupon_limit').val(1);
        $('#coupon_limit').attr("readonly","true");
    }
    else{
        $('#zone_wise').hide();
        $('#store_wise').hide();
        $('#customer_wise').show();
        $('#coupon_limit').val('');
        $('#coupon_limit').removeAttr("readonly");
    }

    if(coupon_type=='free_delivery')
    {
        $('#discount_type').attr("disabled","true");
        $('#discount_type').val("").trigger( "change" );
        $('#max_discount').val(0);
        $('#max_discount').attr("readonly","true");
        $('#discount').val(0);
        $('#discount').attr("readonly","true");
    }
    else{
        $('#max_discount').removeAttr("readonly");
        $('#discount_type').removeAttr("disabled");
        $('#discount').removeAttr("readonly");
    }
}

$('#reset_btn').click(function(){
    location.reload(true);
})
