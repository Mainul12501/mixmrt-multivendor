"use strict";
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#viewer').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$("#customFileEg1").change(function () {
    readURL(this);
});

var zone_id = [];

$(document).on('ready', function () {
    $('#zone').on('change', function(){
        if($(this).val())
        {
            zone_id = $(this).val();
            get_items();
        }
        else
        {
            zone_id = [];
        }
    });

    // INITIALIZATION OF DATATABLES
    // =======================================================
    var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
        select: {
            style: 'multi',
            classMap: {
                checkAll: '#datatableCheckAll',
                counter: '#datatableCounter',
                counterInfo: '#datatableCounterInfo'
            }
        },
    });

    $('#datatableSearch').on('mouseup', function (e) {
        var $input = $(this),
            oldValue = $input.val();

        if (oldValue == "") return;

        setTimeout(function(){
            var newValue = $input.val();

            if (newValue == ""){
                // Gotcha
                datatable.search('').draw();
            }
        }, 1);
    });

    // INITIALIZATION OF SELECT2
    // =======================================================
    $('.js-select2-custom').each(function () {
        var select2 = $.HSCore.components.HSSelect2.init($(this));
    });
});

$('#item_wise').hide();
$('#default').hide();
$('#banner_type').on('change', function () {
    let order_type = $(this).val();
    if (order_type == 'item_wise') {
        $('#store_wise').hide();
        $('#item_wise').show();
        $('#default').hide();
    } else if (order_type == 'store_wise') {
        $('#store_wise').show();
        $('#item_wise').hide();
        $('#default').hide();
    } else if (order_type == 'default') {
        $('#default').show();
        $('#store_wise').hide();
        $('#item_wise').hide();
    } else {
        $('#item_wise').hide();
        $('#store_wise').hide();
        $('#default').hide();
    }
})