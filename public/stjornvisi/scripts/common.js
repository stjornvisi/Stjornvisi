/**
 * Created by einarvalur on 07/03/14.
 */

;(function(){
    "use strict";

    /**
     * Create EditWidget Object.
     *
     * @param {HTMLElement} context
     * @this {EditWidget}
     * @constructor
     */
    var EditWidget = function( context ){
        this.context = context;
        this.tabpanel = context.querySelector('[role=tabpanel]');
        this.tablist = this.tabpanel.querySelector('[role=tablist]');
        this.tabs = this.tablist.querySelectorAll('[role=tab]');
        this.tabs[1].classList.add('off-canvas');
    };
    /**
     * Display widget in edit mode.
     * @returns {EditWidget}
     */
    EditWidget.prototype.editMode = function(){
        this.tablist.classList.add('edit-mode');
        this.entryPanel().classList.add('off-canvas');
        this.formPanel().classList.remove('off-canvas');
        return this;
    };
    /**
     * Display widget in display mode
     * @returns {EditWidget}
     */
    EditWidget.prototype.displayMode = function(){
        this.tablist.classList.remove('edit-mode');
        this.entryPanel().classList.remove('off-canvas');
        this.formPanel().classList.add('off-canvas');
        return this;
    };
    /**
     * Return the 'entry' panel
     * @returns {HTMLElement}
     */
    EditWidget.prototype.entryPanel = function(){
        return this.tabs[0];
    };
    /**
     * Return the 'form' panel
     * @returns {HTMLElement}
     */
    EditWidget.prototype.formPanel = function(){
        return this.tabs[1];
    };
    /**
     * Toggle pre-load mode.
     *
     * @param {boolean} on
     * @returns {EditWidget}
     */
    EditWidget.prototype.preloadMode = function(on){
        if( on ){
            this.tabpanel.classList.add('preload');
        }else{
            this.tabpanel.classList.remove('preload');
        }
        return this;
    };





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


    /**
     * Slide in aside-navigation
     */
    document.addEventListener('DOMContentLoaded',function(){

        var selected = undefined;
        Array.prototype.forEach.call(document.body.querySelectorAll('.layout-aside a'),function(item){
            item.addEventListener('click',function(event){
                event.preventDefault();
                var nav = document.body.querySelector('.navigation');
                if(item.classList.contains('groups')){
                    nav.style.marginLeft = '0%';
                }else if(item.classList.contains('news')){
                    nav.style.marginLeft = '-100%';
                }else if( item.classList.contains('events') ){
                    nav.style.marginLeft = '-200%';
                }else if( item.classList.contains('user') ){
                    nav.style.marginLeft = '-300%';
                }else if( item.classList.contains('config') ){
                    nav.style.marginLeft = '-400%';
                }

                if( item.classList.contains('active') ){
                    document.body.classList.remove('nav-open');
                    selected.classList.remove('active');
                    selected = undefined;
                }else{
                    if( selected ){ selected.classList.remove('active') }

                        selected = item;
                        selected.classList.add('active');
                        document.body.classList.add('nav-open');

                }




            },false);
        });


    },false)


    /**
     *
     */
    document.addEventListener('DOMContentLoaded',function(){
        var controls =  document.body.querySelector('.control-update') || document.createElement('a');
            controls.addEventListener('click',function(event){
            event.preventDefault();
            var widget = new EditWidget( document.body.querySelector('.section-aside-grid') );
                widget.preloadMode(true);

            var xhr = new XMLHttpRequest();
                xhr.open('get',this.href);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                xhr.addEventListener('load',function(event){
                    widget.formPanel().innerHTML = event.target.responseText;
                    widget.editMode();
                    widget.preloadMode(false);
                    var cancel = widget.formPanel().querySelector('.cancel');
                        cancel.addEventListener('click',function(event){
                            event.preventDefault();
                            widget.displayMode();
                        },false);
                    var form = widget.formPanel().querySelector('form')
                        form.addEventListener('submit',function(event){
                            widget.preloadMode(true);
                            event.preventDefault();
                            var xhr = new XMLHttpRequest();
                                xhr.open('post',form.action);
                                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                                xhr.addEventListener('load',function(event){
                                    widget.entryPanel().innerHTML = event.target.responseText;
                                    widget.displayMode().preloadMode(false);
                                },false);
                                xhr.addEventListener('error',function(event){
                                    alert(event.type); //TODO
                                    widget.displayMode().preloadMode(false);
                                },false);
                                xhr.send( new FormData(form) );

                        },false);
                },false);
                xhr.addEventListener('error',function(event){
                    alert(event.type); //TODO
                    widget.displayMode().preloadMode(false);
                },false);
                xhr.send();

        },false);
    },false);


    /**
     * Hint that there is a menu under the content
     */
    window.addEventListener('load',function(event){
        if( this.screen.width <= 480 ){
            //document.body.classList.add('nav-open');
            setTimeout(function(){
                //document.body.classList.remove('nav-open');
            },80);
        }
    },false);

    document.addEventListener('DOMContentLoaded',function(event){
        var x, y;
        var main = document.body.querySelector('main');
            main.addEventListener('touchstart',function(event){
                x = event.touches[0].clientX;
                y = event.touches[0].clientY;
                console.log(event);
            },false);
            main.addEventListener('touchmove',function(event){
                var moveX = event.touches[0].clientX;
                if((moveX - x)>100 ){
                    document.body.classList.add('nav-open');
                }
                if( (moveX - x)<-100 ){
                    document.body.classList.remove('nav-open');
                }
            },false);
            main.addEventListener('touchend',function(event){
                console.log(event);
            },false);
    },false);


})();



