(function ($) {
    "user strict";
    $(window).on("load", () => {
        $("#landing-loader").fadeOut(1000);
    });
    $(document).ready(function () {
        //Header Bar
        $(".nav-toggle").on("click", () => {
            $(".nav-toggle").toggleClass("active");
            $(".menu").toggleClass("active");
        });

        $(".counter-item").each(function () {
            $(this).isInViewport(function (e) {
                if ("entered" === e)
                    for (
                        var i = 0;
                        i < document.querySelectorAll(".odometer").length;
                        i++
                    ) {
                        var n = document.querySelectorAll(".odometer")[i];
                        n.innerHTML = n.getAttribute("data-odometer-final");
                    }
            });
        });
        var header = $("header");
        $(window).on("scroll", function () {
            if ($(this).scrollTop() > 300) {
                header.addClass("active");
            } else {
                header.removeClass("active");
            }
        });

        if ($(".wow").length) {
            var wow = new WOW({
                boxClass: "wow",
                animateClass: "animated",
                offset: 0,
                mobile: true,
                live: true,
            });
            wow.init();
        }

        $(".learn-feature-wrapper").on("scroll", function () {
            $(".learn-feature-item-group").addClass("stop-animation");
        });
        $(".learn-feature-wrapper").on("mouseover mouseleave", function () {
            $(".learn-feature-item-group").removeClass("stop-animation");
        });

        $(".show-password").on("click", function () {
            var input = $(this).closest("label").find("input");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    });
})(jQuery);
