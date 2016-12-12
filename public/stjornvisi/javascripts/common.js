/**
 * Created by einar on 18/01/15.
 */

;(function () {
    "use strict";


    var mock = {
        addEventListener: function () {
        }
    };
    (document.querySelector('.main--burger') || mock).addEventListener('click', function (event) {
        event.preventDefault();
        document.body.classList.toggle('state-mobile-menu-open');
    }, false);

    (document.querySelector('.main--home') || mock).addEventListener('click', function (event) {
        event.preventDefault();
        document.body.classList.toggle('state-open');
    }, false);
})();

$(function () {
    $('.navigation > li > a', '.adminbar').on('mouseup touchstart', function(e) {
        var $a = $(this),
            $li = $a.parents('li');

        if (e.type === 'touchstart') {
            if (!$(e.target).hasClass('navigation--home')) {
                e.preventDefault();
            }

            $('.navigation--open').removeClass('navigation--open');
            $li.addClass('navigation--open');
        }
    });
    $('.navigation > li', '.adminbar').on('mouseleave mouseenter', function(e) {
        var $li = $(this);

        if(e.type === 'mouseenter') {
            $('.navigation--open').removeClass('navigation--open');
            $li.addClass('navigation--open');
        }
        else if (e.type === 'mouseleave') {
            $('.navigation--open').removeClass('navigation--open');
        }
    });
    $('body').on('click', function() {
        $('.navigation--open').removeClass('navigation--open');
    });

    $('.entry__title', '.entry--hoverable').on('mouseup touchstart mouseenter mouseleave', function (e) {
        if (e.target.className !== 'btn btn-default') {
            var $parent = $(this.parentNode);
            e.preventDefault();
            if (e.type === 'mouseup' || e.type === 'touchstart') {
                $('.entry--hoverable').removeClass('open');
                $('body').removeClass('hoverable--open');

                $parent.addClass('open');
                $('body').addClass('hoverable--open');
            }
            else if (e.type === 'mouseenter') {
                $('.entry--hoverable').removeClass('open');
                $('body').removeClass('hoverable--open');

                $parent.addClass('open');
                $('body').addClass('hoverable--open');
            }
            else if (e.type === 'mouseleave') {
                $('.entry--hoverable').removeClass('open');
                $('body').removeClass('hoverable--open');
            }
        }
    });

    $('.boardmembers__toggle').on('click', function () {
        var $toggle = $(this),
            $members = $toggle.next('.boardmembers');

        if ($members.hasClass('boardmembers--closed')) {
            $members.addClass('boardmembers--open').removeClass('boardmembers--closed');
        }
        else {
            $members.addClass('boardmembers--closed').removeClass('boardmembers--open');
        }
    });

    $('.poof__button').on('click', function(e) {
        e.preventDefault();
        var $button = $(this),
            $toggle = $button.parents('.poof__toggle'),
            $content = $toggle.next('.poof__content');

        $toggle.addClass('poof--toggled');
        $content.addClass('poof--toggled');
    });
});
