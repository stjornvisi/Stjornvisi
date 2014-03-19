/**
 * Created by einarvalur on 07/03/14.
 */

;(function(){
    "use strict";

    /**
     * Upload media file
     * @param HTMLInputElement
     */
    var uploadMedia = function(item){
        if( item.value != '' ){
            item.style.backgroundImage = 'url(/css/images/logo-files.png)';
        }
        item.addEventListener('change',function(event){
            if( event.target.value == '' ){
                item.style.backgroundImage = 'none';
            }
        },false);
        item.addEventListener('click',function(event){
            var formData = new FormData();
            var input = document.createElement('input');
            input.type = 'file';
            input.setAttribute('multiple','');
            //input.setAttribute('accept','image/png, image/x-png, image/gif, image/jpeg');
            input.addEventListener('change',function(event){
                item.classList.add('progress');
                [].forEach.call(event.target.files,function(i){
                    formData.append('media[]',i);
                });
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress',function(event){
                    console.log(Math.round(event.loaded * 100 / event.total));
                },false);
                xhr.addEventListener('load',function(event){
                    var object = JSON.parse( event.target.responseText);
                    item.style.backgroundImage = 'url(/css/images/logo-files.png)';
                    item.value = object.info.media[0].name;
                    input.parentNode.removeChild(input);
                    item.classList.remove('progress');

                },false);
                xhr.addEventListener('error',function(event){

                },false);
                xhr.open('post','/skrar/skra',true);
                xhr.send(formData);

            },false);
            document.body.appendChild(input);
            input.click();
        },false);
    };

    /**
     *
     * @param HTMLInputElement
     */
    var uploadImage = function(item){
        if( item.value != '' ){
            item.style.backgroundImage = 'url(/stjornvisi/images/60/'+item.value+')';
        }
        item.addEventListener('change',function(event){
            if( event.target.value == '' ){
                item.style.backgroundImage = 'none';
            }
        },false);
        item.addEventListener('click',function(event){
            var formData = new FormData();
            var input = document.createElement('input');
                input.type = 'file';
                input.setAttribute('multiple','');
                input.setAttribute('accept','image/png, image/x-png, image/gif, image/jpeg');
                input.addEventListener('change',function(event){
                    item.classList.add('progress');
                    [].forEach.call(event.target.files,function(i){
                        formData.append('image[]',i);
                    });
                    var xhr = new XMLHttpRequest();
                        xhr.upload.addEventListener('progress',function(event){
                            console.log(Math.round(event.loaded * 100 / event.total));
                        },false);
                        xhr.addEventListener('load',function(event){
                            var object = JSON.parse( event.target.responseText);
                            item.style.backgroundImage = 'url(/stjornvisi/images/60/'+object.info.media[0].name+')';
                            item.value = object.info.media[0].name;
                            input.parentNode.removeChild(input);
                            item.classList.remove('progress');

                        },false);
                        xhr.addEventListener('error',function(event){

                        },false);
                        xhr.open('post','/skrar/mynd',true);
                        xhr.send(formData);

                },false);
            document.body.appendChild(input);
            input.click();
        },false);
    };

    var editPage = function(item){
        item.addEventListener('click',function(event){
            event.preventDefault();
            var page = document.getElementById('editable-static-page-container');
                page.setAttribute('contenteditable','true');

                page.addEventListener('blur',function(event){
                    var form = new FormData();
                        form.append('body',page.innerHTML);
                    var xhr = new XMLHttpRequest();
                        xhr.open('post',item.dataset.target);
                        xhr.send(form);
                    page.removeAttribute('contenteditable');
                },false);

        },false);
    };

    /**
     * Attache upload functionality to input elements
     * that are marked accordingly.
     *
     * Will run when the DOM is ready.
     */
    document.addEventListener('DOMContentLoaded',function(event){
        [].forEach.call(document.querySelectorAll('input.avatar'),function(item){
            uploadImage(item);
        });

        [].forEach.call(document.querySelectorAll('input.media'),function(item){
            uploadMedia(item);
        });


        [].forEach.call(document.querySelectorAll('.editable-static-page-container-trigger'),function(item){
            editPage(item);
        });

    },false);


    $(function(){

        //AUTOCOMPLETE
        //
        var bestPictures = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            prefetch: '../data/films/post_1960.json',
            remote: '/leita/forval/%QUERY'
        });
        bestPictures.initialize();
        /*
        $('#custom-templates .typeahead').typeahead(null, {
            name: 'best-pictures',
            displayKey: 'value',
            source: bestPictures.ttAdapter()
        });
        */

        $('#custom-templates .typeahead').typeahead(null, {
            name: 'best-pictures',
            displayKey: 'value',
            source: bestPictures.ttAdapter(),
            templates: {
                empty: [
                    '<div class="empty-message">',
                    'unable to find any Best Picture winners that match the current query',
                    '</div>'
                ].join('\n'),
                suggestion: Handlebars.compile('<p><strong><a href="{{href}}">{{value}}</a></strong> – {{type}}</p>')
            }
        });



    });

})();



