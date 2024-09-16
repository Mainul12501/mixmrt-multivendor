

"use strict";

$('.blinkings').on('mouseover', ()=> $('.blinkings').removeClass('active'))
$('.blinkings').addClass('open-shadow')
setTimeout(() => {
    $('.blinkings').removeClass('active')
}, 10000);
setTimeout(() => {
    $('.blinkings').removeClass('open-shadow')
}, 5000);




$(document).on('click', '.next-tour', function () {
    next_tour();

});

function next_tour() {
    tour.next();
}


$(function(){
    let owl = $('.single-item-slider');
    owl.owlCarousel({
        autoplay: false,
        items:1,
        onInitialized  : counter,
        onTranslated : counter,
        autoHeight: true,
        dots: true
    });

    function counter(event) {
        let element   = event.target;         // DOM element, in this example .owl-carousel
            let items     = event.item.count;     // Number of items
            let item      = event.item.index + 1;     // Position of the current item

        // it loop is true then reset counter from 1
        if(item > items) {
            item = item - items
        }
        $('.slide-counter').html(+item+"/"+items)
    }
});

$(document).on('ready', function () {
    // ONLY DEV
    // =======================================================
    if (window.localStorage.getItem('hs-builder-popover') === null) {
        $('#builderPopover').popover('show')
            .on('shown.bs.popover', function () {
                $('.popover').last().addClass('popover-dark')
            });

        $(document).on('click', '#closeBuilderPopover', function () {
            window.localStorage.setItem('hs-builder-popover', true);
            $('#builderPopover').popover('dispose');
        });
    } else {
        $('#builderPopover').on('show.bs.popover', function () {
            return false
        });
    }
    // END ONLY DEV
    // =======================================================

    // BUILDER TOGGLE INVOKER
    // =======================================================
    $('.js-navbar-vertical-aside-toggle-invoker').click(function () {
        $('.js-navbar-vertical-aside-toggle-invoker i').tooltip('hide');
    });

    // INITIALIZATION OF NAVBAR VERTICAL NAVIGATION
    // =======================================================
    let sidebar = $('.js-navbar-vertical-aside').hsSideNav();


    // INITIALIZATION OF TOOLTIP IN NAVBAR VERTICAL MENU
    // =======================================================
    $('.js-nav-tooltip-link').tooltip({boundary: 'window'})

    $(".js-nav-tooltip-link").on("show.bs.tooltip", function (e) {
        if (!$("body").hasClass("navbar-vertical-aside-mini-mode")) {
            return false;
        }
    });


    // INITIALIZATION OF UNFOLD
    // =======================================================
    $('.js-hs-unfold-invoker').each(function () {
        let unfold = new HSUnfold($(this)).init();
    });


    // INITIALIZATION OF FORM SEARCH
    // =======================================================
    $('.js-form-search').each(function () {
        new HSFormSearch($(this)).init()
    });


    // INITIALIZATION OF SELECT2
    // =======================================================
    $('.js-select2-custom').each(function () {
        let select2 = $.HSCore.components.HSSelect2.init($(this));
    });


    // INITIALIZATION OF DATERANGEPICKER
    // =======================================================
    $('.js-daterangepicker').daterangepicker();

    $('.js-daterangepicker-times').daterangepicker({
        timePicker: true,
        startDate: moment().startOf('hour'),
        endDate: moment().startOf('hour').add(32, 'hour'),
        locale: {
            format: 'M/DD hh:mm A'
        }
    });

    let start = moment();
    let end = moment();

    function cb(start, end) {
        $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format('MMM D') + ' - ' + end.format('MMM D, YYYY'));
    }

    $('#js-daterangepicker-predefined').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);


    // INITIALIZATION OF CLIPBOARD
    // =======================================================
    $('.js-clipboard').each(function () {
        let clipboard = $.HSCore.components.HSClipboard.init(this);
    });
});
let tour = new Tour({
    backdrop: true,
    delay: true,
    redirect: true,
    name:'tour',
    steps: [
        {
            element: "#tourb-0",
            title: "Module",
            placement: 'right',
            content: "From here you can switch to multiple modules."
        },
        {
            element: "#tourb-1",
            title: "Module Selection",
            content: "You can select a module from here.",
        },
        {
            element: "#navbar-vertical-content",
            title: "Module Sidebar",
            content: "This is the module wise sidebar."
        },
        {
            element: "#tourb-3",
            title: "Settings",
            content: "From here you can go to settings option."
        },
        {
            element: "#tourb-4",
            title: "Settings Menu",
            content: "From here you can select any settings option.",
        },
        {
            element: "#navbar-vertical-content",
            title: "Settings Sidebar",
            content: "This is the settings sidebar. Different from module",
        },
        {
            element: "#tourb-6",
            title: "User Section",
            content: "You can manage all the users by selecting this option.",
        },
        {
            element: "#tourb-7",
            title: "Transaction and Report",
            content: "You can manage all the Transaction and Report by selecting this option."
        },
        {
            element: "#tourb-8",
            title: "Dispatch Management",
            content: "You can manage all dispatch orders by selecting this option."
        },
        {
            element: "#tourb-9",
            title: "Profile and Logout",
            content: "You can visit your profile or logut from this panel.",
            placement:'top'
        }
    ],
    onEnd: function() {
        $('body').css('overflow','')
    },
    onShow: function() {
        $('body').css('overflow','hidden')
    }
});
$(document).on('click', '.instruction-Modal-Close', function () {
    $('#instruction-modal').hide();
        tour.init();
        tour.start();
});

$(document).on('click', '.email-Modal-Close', function () {
    $('#email-modal').hide();

});

$(".store-filter").on("change", function () {
    const id = $(this).val();
    const url = $(this).data('url');
    let nurl = new URL(url);
    nurl.searchParams.delete('page');
    nurl.searchParams.set('store_id', id);
    location.href = nurl;
});


$(".payment-method-filter").on("change", function () {
    const id = $(this).val();
    const url = $(this).data('url');
    let nurl = new URL(url);
    nurl.searchParams.set('payment_method_id', id);
    location.href = nurl;
});


function set_filter(url, id, filter_by) {
    let nurl = new URL(url);
    nurl.searchParams.set(filter_by, id);
    location.href = nurl;
    tour.next();
}

$(".set-module").on("click", function () {
    const url = $(this).data('url');
    const id = $(this).data('module-id');
    const filter_by = $(this).data('filter');
    let nurl = new URL(url);
    nurl.searchParams.set(filter_by, id);
    location.href = nurl
});

$(document).ready(function(){
    $('button[type=submit]').on("click", function(){
        setTimeout(function () {
            $('button[type=submit]').prop('disabled', true);
            }, 0);
        setTimeout(function () {
            $('button[type=submit]').prop('disabled', false);
            }, 1000);
    });
});

function getUrlParameter(sParam) {
    let sPageURL = window.location.search.substring(1);
    let sURLVariables = sPageURL.split('&');
    for (let i = 0; i < sURLVariables.length; i++) {
        let sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1];
        }
    }
}
