/**
 * Created by einar on 18/01/15.
 */

;(function(){
    "use strict";


    var mock = {addEventListener:function(){}};
    var active = undefined;
    (document.querySelector('.categories--groups')||mock).addEventListener('click',function(event){
        event.preventDefault();
        document.querySelector('ul.navigation').style.marginLeft = 0;
        if(active == undefined){
            event.target.classList.add('active');
            document.body.classList.add('state-open');
            active = event.target;
        }
        else if( active == event.target ){
            event.target.classList.remove('active');
            document.body.classList.remove('state-open');
            active = undefined;
        }else{
            active.classList.remove('active');
            active = event.target;
            active.classList.add('active');
        }


    },false);
    (document.querySelector('.categories--users')||mock).addEventListener('click',function(event){
        event.preventDefault();
        document.querySelector('ul.navigation').style.marginLeft = '-100%';
        if(active == undefined){
            event.target.classList.add('active');
            document.body.classList.add('state-open');
            active = event.target;
        }
        else if( active == event.target ){
            event.target.classList.remove('active');
            document.body.classList.remove('state-open');
            active = undefined;
        }else{
            active.classList.remove('active');
            active = event.target;
            active.classList.add('active');
        }
    },false);
    (document.querySelector('.categories--config')||mock).addEventListener('click',function(event){
        event.preventDefault();
        document.querySelector('ul.navigation').style.marginLeft = '-200%';
        if(active == undefined){
            event.target.classList.add('active');
            document.body.classList.add('state-open');
            active = event.target;
        }
        else if( active == event.target ){
            event.target.classList.remove('active');
            document.body.classList.remove('state-open');
            active = undefined;
        }else{
            active.classList.remove('active');
            active = event.target;
            active.classList.add('active');
        }
    },false);

    (document.querySelector('.main--burger')||mock).addEventListener('click',function(event){
        event.preventDefault();
        document.body.classList.toggle('state-mobile-menu-open');
    },false);

    (document.querySelector('.main--home')||mock).addEventListener('click',function(event){
        event.preventDefault();
        document.body.classList.toggle('state-open');
    },false);


})();
