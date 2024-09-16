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

    $('#date_from').attr('min',(new Date()).toISOString().split('T')[0]);
    $('#date_to').attr('min',(new Date()).toISOString().split('T')[0]);
    let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
        select: {
            style: 'multi',
            classMap: {
                checkAll: '#datatableCheckAll',
                counter: '#datatableCounter',
                counterInfo: '#datatableCounterInfo'
            }
        },
    });

    // INITIALIZATION OF SELECT2
    // =======================================================
    $('.js-select2-custom').each(function () {
        let select2 = $.HSCore.components.HSSelect2.init($(this));
    });
});
$("#date_from").on("change", function () {
    $('#date_to').attr('min',$(this).val());
});

$("#date_to").on("change", function () {
    $('#date_from').attr('max',$(this).val());
});
$('#zone_wise').hide();
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
    }
    else{
        $('#zone_wise').hide();
        $('#store_wise').hide();
        $('#customer_wise').show();
        $('#coupon_limit').val('');
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
        $('#discount_type').attr("required","true");
        $('#discount').removeAttr("readonly");
    }
}

$('#reset_btn').click(function(){
    $('#module_select').val(null).trigger('change');
    $('#store_id').val(null).trigger('change');
    $('#store_wise').show();
    $('#zone_wise').hide();
})
