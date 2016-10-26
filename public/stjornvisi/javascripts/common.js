/**
 * Created by einar on 18/01/15.
 */

;(function () {
    "use strict";


    var mock = {
        addEventListener: function () {
        }
    };
    var active = undefined;
    (document.querySelector('.categories--groups') || mock).addEventListener('click', function (event) {
        event.preventDefault();
        document.querySelector('ul.navigation').style.marginLeft = 0;
        if (active == undefined) {
            event.target.classList.add('active');
            document.body.classList.add('state-open');
            active = event.target;
        }
        else if (active == event.target) {
            event.target.classList.remove('active');
            document.body.classList.remove('state-open');
            active = undefined;
        } else {
            active.classList.remove('active');
            active = event.target;
            active.classList.add('active');
        }


    }, false);
    (document.querySelector('.categories--users') || mock).addEventListener('click', function (event) {
        event.preventDefault();
        document.querySelector('ul.navigation').style.marginLeft = '-100%';
        if (active == undefined) {
            event.target.classList.add('active');
            document.body.classList.add('state-open');
            active = event.target;
        }
        else if (active == event.target) {
            event.target.classList.remove('active');
            document.body.classList.remove('state-open');
            active = undefined;
        } else {
            active.classList.remove('active');
            active = event.target;
            active.classList.add('active');
        }
    }, false);
    (document.querySelector('.categories--config') || mock).addEventListener('click', function (event) {
        event.preventDefault();
        document.querySelector('ul.navigation').style.marginLeft = '-200%';
        if (active == undefined) {
            event.target.classList.add('active');
            document.body.classList.add('state-open');
            active = event.target;
        }
        else if (active == event.target) {
            event.target.classList.remove('active');
            document.body.classList.remove('state-open');
            active = undefined;
        } else {
            active.classList.remove('active');
            active = event.target;
            active.classList.add('active');
        }
    }, false);

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
