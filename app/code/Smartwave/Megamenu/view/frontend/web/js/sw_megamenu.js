(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    $.fn.swMegamenu = function() {
        $(".navigation.sw-megamenu li.classic .submenu, .navigation.sw-megamenu li.staticwidth .submenu, .navigation.sw-megamenu li.classic .subchildmenu .subchildmenu").each(function(){
            $(this).css("left","-9999px");
            $(this).css("right","auto");
        });
        $(this).find("li.classic .subchildmenu > li.parent").mouseover(function(){
            var popup = $(this).children("ul.subchildmenu");
            var w_width = $(window).innerWidth();
            
            if(popup) {
                var pos = $(this).offset();
                var c_width = $(popup).outerWidth();
                if(w_width <= pos.left + $(this).outerWidth() + c_width) {
                    $(popup).css("left","auto");
                    $(popup).css("right","100%");
                    $(popup).css("border-radius","6px 0 6px 6px");
                } else {
                    $(popup).css("left","100%");
                    $(popup).css("right","auto");
                    $(popup).css("border-radius","0 6px 6px 6px");
                }
            }
        });
        $(this).find("li.staticwidth.parent,li.classic.parent").mouseover(function(){
            var popup = $(this).children(".submenu");
            var w_width = $(window).innerWidth();
            
            if(popup) {
                var pos = $(this).offset();
                var c_width = $(popup).outerWidth();
                if(w_width <= pos.left + $(this).outerWidth() + c_width) {
                    $(popup).css("left","auto");
                    $(popup).css("right","0");
                    $(popup).css("border-radius","6px 0 6px 6px");
                } else {
                    $(popup).css("left","0");
                    $(popup).css("right","auto");
                    $(popup).css("border-radius","0 6px 6px 6px");
                }
            }
        });
        $(window).resize(function(){
            $(".navigation.sw-megamenu li.classic .submenu, .navigation.sw-megamenu li.staticwidth .submenu, .navigation.sw-megamenu li.classic .subchildmenu .subchildmenu").each(function(){
                $(this).css("left","-9999px");
                $(this).css("right","auto");
            });
        });
        $(".nav-toggle").off('click').on('click',function(e){
            if(!$("html").hasClass("nav-open")) {
                $("html").addClass("nav-before-open");
                setTimeout(function(){
                    $("html").addClass("nav-open");
                }, 300);
            }
            else {
                $("html").removeClass("nav-open");
                setTimeout(function(){
                    $("html").removeClass("nav-before-open");
                }, 300);
            }
        });
        $("li.ui-menu-item > .open-children-toggle").off("click").on("click", function(){
            if(!$(this).parent().children(".submenu").hasClass("opened")) {
                $(this).parent().children(".submenu").addClass("opened");
                $(this).parent().children("a").addClass("ui-state-active");
            }
            else {
                $(this).parent().children(".submenu").removeClass("opened");
                $(this).parent().children("a").removeClass("ui-state-active");
            }
        });
    };
}));