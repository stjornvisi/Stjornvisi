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


/*
    var eventFormListerners = function(form){

        //SUBMIT
        //
        form.addEventListener('submit',function(event){
            event.preventDefault();
            var xhr = new XMLHttpRequest();
            xhr.open('post',form.action);
            xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
            xhr.responseType = 'document';
            xhr.addEventListener('load',function(event){
                form.parentNode.removeChild(form);
                var selection = document.querySelector('.event-wrapper > section');
                var el = selection.firstChild;
                while( el ){
                    selection.removeChild( selection.firstChild );
                    el = selection.firstChild;
                }
                var content = event.target.response.body;
                var it = content.firstElementChild;
                while( it ){
                    selection.appendChild( it );
                    it = content.firstElementChild;
                }

                var mapCanvas = document.getElementById("map_canvas");
                var latlng = new google.maps.LatLng(
                    parseFloat(mapCanvas.getAttribute('data-lat')),
                    parseFloat(mapCanvas.getAttribute('data-lng'))
                );
                var myOptions = {
                    zoom: 15,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                    };
                var map = new google.maps.Map(mapCanvas,myOptions);
                var marker = new google.maps.Marker({
                    position: latlng
                    });

                // To add the marker to the map, call setMap();
                marker.setMap(map);


            },false);
            xhr.send( new FormData( form ) );
        },false);

        //CANCEL
        //
        form.querySelector('.btn.cancel').addEventListener('click',function(event){
            event.preventDefault();
            form.parentNode.removeChild(form);
        },false);

        //IMAGE UPLOAD
        //
        var media = form.querySelector('.avatar');
        media.addEventListener('click',function(event){
            event.preventDefault();
            var url = media.dataset.url;
            var input = document.createElement('input');
            input.type = 'file';
            input.addEventListener('change',function(event){
                var files = event.target.files;
                var formData = new FormData();
                formData.append("file", files[0]);
                var xhr = new XMLHttpRequest();
                xhr.open('post',url);
                media.classList.remove('done');
                xhr.upload.addEventListener("progress", function(event){
                    var status = (event.loaded / event.total * 100 );
                    media.style.backgroundImage = 'linear-gradient(90deg, ' +
                        'rgba(215,225,235,1) 0%, rgba(215,225,235,1) '+status+'%, ' +
                        'rgba(255,255,255,1) 0%, rgba(255,255,255,1) 100%)';
                }, false);
                xhr.addEventListener('load',function(event){
                    media.classList.add('done');
                    var object = JSON.parse( event.target.responseText );
                    media.style.backgroundImage = 'url(/images/60/'+object.info.media[0].name+')';
                    media.style.backgroundSize = 'auto 60px';
                    media.style.backgroundRepeat = 'no-repeat';
                    media.value = object.info.media[0].name;
                    input.parentNode.removeChild(input);
                },false);
                xhr.send(formData);
            },false);
            document.body.appendChild(input);
            input.click();
        },false);


        return form;
    };


    var updateEvent = document.querySelector('.event-wrapper .event-event-aside .control-update');
    var container = document.querySelector('.event-wrapper > section');
    (updateEvent || {addEventListener:function(){}}).addEventListener('click',function(event){
        event.preventDefault();

        var xhr = new XMLHttpRequest();
        xhr.open('get',event.target.href);
        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
        xhr.responseType = 'document';
        xhr.addEventListener('load',function(event){

            var form = event.target.response.body.firstElementChild;
            var i = 0;

            container.insertBefore(eventFormListerners(form),container.firstChild);
            setTimeout(function(){
                form.classList.add('viewable');
            },300);

        },false);
        xhr.addEventListener('error',function(event){

        },false);
        xhr.send();


    },false);
*/





    var media = document.querySelector('.avatar') || { addEventListener: function(){} };
    media.addEventListener('click',function(event){
        event.preventDefault();
        var url = media.dataset.url;
        var input = document.createElement('input');
        input.type = 'file';
        input.addEventListener('change',function(event){
            var files = event.target.files;
            var formData = new FormData();
            formData.append("file", files[0]);
            var xhr = new XMLHttpRequest();
            xhr.open('post',url);
            media.classList.remove('done');
            xhr.upload.addEventListener("progress", function(event){
                var status = (event.loaded / event.total * 100 );
                media.style.backgroundImage = 'linear-gradient(90deg, ' +
                    'rgba(215,225,235,1) 0%, rgba(215,225,235,1) '+status+'%, ' +
                    'rgba(255,255,255,1) 0%, rgba(255,255,255,1) 100%)';
            }, false);
            xhr.addEventListener('load',function(event){
                media.classList.add('done');
                var object = JSON.parse( event.target.responseText );
                media.style.backgroundImage = 'url('+object.info.media[0].thumb+')';
                media.style.backgroundSize = 'auto 60px';
                media.style.backgroundRepeat = 'no-repeat';
                media.value = object.info.media[0].name;
                input.parentNode.removeChild(input);
            },false);
            xhr.send(formData);
        },false);
        document.body.appendChild(input);
        input.click();
    },false);




})();