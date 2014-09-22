/**
 * Created by einar on 18/09/14.
 */
;(function(){


    var categorySelected = undefined;
    var categories = document.querySelectorAll('nav[role=navigation] .categories a.control');
    var navigation = document.querySelector('ul.navigation');
    Array.prototype.forEach.call(categories,function(item,index){
        item.addEventListener('click',function(event){
            event.preventDefault();
            navigation.style.marginLeft = -(index*100)+'%';
            if( item == categorySelected ){
                document.body.classList.remove('menu');
                item.classList.remove('active');
                categorySelected = undefined;
            }else{
                document.body.classList.add('menu');
                if( categorySelected ){ categorySelected.classList.remove('active'); }
                categorySelected = item;
                item.classList.add('active');
            }

        },false);
    });


})();