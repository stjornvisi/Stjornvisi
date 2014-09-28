# Stjórnvísi #
This is a module for Stjónvísi. This is a group manager, event manager and news manager among other
things written as ZF2 Module.

1. http://framework.zend.com/manual/2.0/en/user-guide/modules.html
2. http://evan.pro/zf2-modules-talk.html\#slide1

Since this is a module, you need to set up the ZF2 Skeleton Application

1. http://framework.zend.com/manual/2.0/en/user-guide/skeleton-application.html
2. https://github.com/zendframework/ZendSkeletonApplication

After that we simply install the module.

## Install  ##
Clone this repository to the `module` and name it *Stjornvisi*

    $ cd path/to/install/module
    $ git clone https://github.com/fizk/Stjornvisi.git Stjornvisi

Copy the `composer.json` and `composer.phar` into the root of the Skeleton Application and then run composer

    $ php composer.phar install

Go into `config/application.config.php` and make sure it says

    'modules' => array(
        'Stjornvisi',
    ),

Create this file `config/autoload/stjornvisi.local.php`, paste in this code and adjust:

    <?php

    return array(
        'db' =>array(
            'dns' => 'mysql:dbname=[DATABASE_NAME];host=127.0.0.1',
            'user' => '[USER]',
            'password' => '[PASSWORD]'
        ),
        'facebook' => array(
            'appId' => '[APP-ID]',
            'secret' => '[SECRET]',
            'fileUpload' => false, // optional
            'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
            'redirect_uri' => 'http://[DOMAIN]/callback'
        ),
    );

Go into `module/Stjornvisi/public` and run

    $ bower install

Make a soft-link from _module public folder_ into the real one

    $ ln -s ./module/Stjornvisi/public/stjornvisi ./public/stjornvisi

Create a database called *stjornvisi_production* and the import the database and migration script

    $ mysql -u [] -p [] stjornvisi_production < ./module/Stjornvisi/assets/db/stjornvisi_production.sql
    $ mysql -u [] -p [] stjornvisi_production < ./module/Stjornvisi/assets/db/migration.sql





## UnitTests ##
Make sure you have a test database by first creating `stjornvisi_test` and then run

    $ mysql -u [] -p [] stjornvisi_production < ./module/Stjornvisi/assets/db/stjornvisi-empty.sql
    $ mysql -u [] -p [] stjornvisi_production < ./module/Stjornvisi/assets/db/migration.sql

The `cd` into the test directory

    $ cd ./module/Stjornvisi/test

And the run phpunit

    $ php ../../../vendor/bin/phpunit

# Commandline #

This module comes with some command line actions.

To run command-line actions you only have to point your PHP runtime to the index file.

    $ php /path/to/public/index.php [arguments]

and then you can pass in some arguments

### arguments ###
* **index entry** Will start a listener that listens for entry CRUD and sends entries to be indexed.
* **image generate [--ignore]** Will resample all images from the `original` folder and overwrite the old ones, no questions asked



## Search Indexing ##

The **Service Layer** contains an EventManager that will be triggered whenever something interesting is happening in the layer.
These are the classic CRUD operations. Whenever the service layer feels that an instance or a state of an entity should
be kept in another storage, it will issue the `index` event. Meaning that the state of the object should be indexed.

There is an external event listener in the application level called `ServiceIndexListener` that is listening for this `index`
event. The only thing that this listener does is to relay the message from the **Service Layer** to a Queue (RabbitMQ).

There, it will travel from the **P**rovider through the `search-index` queue until it is picker up by the **C**onsumer. Where
it is decoded and sent to the _search index api_.

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"viewBox="0 0 1026 306">
<rect x="666" y="9" fill="#DFDFE5" width="351" height="288"/>
<rect x="297" y="9" fill="#DFDFE5" width="234" height="288"/>
<rect x="9" y="9" fill="#CCCCCC" width="279" height="288"/>
<text transform="matrix(1 0 0 1 224.9998 126)" font-family="'MyriadPro-Regular'" font-size="12">trigger(’index’,$data)</text>
<text transform="matrix(1 0 0 1 81 72)" font-family="'MyriadPro-Regular'" font-size="12">create/update/delete()</text>
<g>
	<rect x="18.5" y="17.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="99" height="18"/>
	<text transform="matrix(1 0 0 1 21.7065 29.6548)" font-family="'MyriadPro-Regular'" font-size="12">&lt;AbstractService&gt;</text>
</g>
<g>
	<rect x="126.5" y="17.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="153" height="18"/>
	<text transform="matrix(1 0 0 1 130.167 29.7721)" font-family="'MyriadPro-Regular'" font-size="12">&lt;&lt;EventManagerInterface&gt;&gt;</text>
</g>
<g>
	<rect x="305.5" y="17.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="117" height="18"/>
	<text transform="matrix(1 0 0 1 312.0503 30.511)" font-family="'MyriadPro-Regular'" font-size="12">ServiceIndexListener</text>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="62.5" y1="35" x2="62.5" y2="37"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="62.5" y1="40" x2="62.5" y2="50"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="62.5" y1="52" x2="62.5" y2="53"/>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="358.5" y1="36" x2="358.5" y2="38"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="358.5" y1="40" x2="358.5" y2="132"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="358.5" y1="134" x2="358.5" y2="135"/>
	</g>
</g>
<rect x="53.5" y="53.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="18" height="207"/>
<rect x="197.5" y="35.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="18" height="252"/>
<rect x="349.5" y="134.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="18" height="72"/>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="216" y1="143.5" x2="336" y2="143.5"/>
		<g>
			<polygon points="334,148.486 342.635,143.5 334,138.514 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="79" y1="234.5" x2="198" y2="234.5"/>
		<g>
			<polygon points="81,239.486 72.365,234.5 81,229.514 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="358.5" y1="207" x2="358.5" y2="209"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="358.5" y1="211" x2="358.5" y2="285"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="358.5" y1="287" x2="358.5" y2="288"/>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="72" y1="90.5" x2="191" y2="90.5"/>
		<g>
			<polygon points="189,95.486 197.635,90.5 189,85.514 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="62.5" y1="288" x2="62.5" y2="287"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="62.5" y1="284" x2="62.5" y2="264"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="62.5" y1="263" x2="62.5" y2="261"/>
	</g>
</g>
<g>
	<g>
		<path fill="#3333CC" d="M471.239,156.512c-2.279,0-4.02,0.18-4.92,0.42v15.72c1.141,0.3,2.58,0.42,4.32,0.42
			c6.3,0,10.14-3.061,10.14-8.64C480.779,159.092,476.999,156.512,471.239,156.512z M472.5,126c-27.338,0-49.5,22.162-49.5,49.5
			c0,27.338,22.162,49.5,49.5,49.5s49.5-22.162,49.5-49.5C522,148.162,499.838,126,472.5,126z M482.879,172.831
			c-2.76,2.939-7.26,4.439-12.359,4.439c-1.56,0-3-0.06-4.2-0.359v16.199h-5.22v-39.959c2.521-0.42,5.82-0.779,10.02-0.779
			c5.16,0,8.94,1.199,11.34,3.359c2.22,1.92,3.54,4.86,3.54,8.46C485.999,167.852,484.919,170.731,482.879,172.831z"/>
	</g>
</g>
<rect x="540" y="153" fill="#CC3333" width="117" height="45"/>
<path fill="#3333CC" d="M724.5,126c-27.338,0-49.5,22.162-49.5,49.5c0,27.338,22.162,49.5,49.5,49.5s49.5-22.162,49.5-49.5
	C774,148.162,751.838,126,724.5,126z M730.319,189.33c3.18,0,6.42-0.66,8.52-1.68l1.08,4.14c-1.92,0.96-5.76,1.92-10.68,1.92
	c-11.399,0-19.979-7.2-19.979-20.459c0-12.659,8.579-21.239,21.119-21.239c5.039,0,8.22,1.08,9.6,1.8l-1.26,4.26
	c-1.98-0.96-4.8-1.68-8.16-1.68c-9.479,0-15.779,6.06-15.779,16.68C714.78,182.971,720.48,189.33,730.319,189.33z"/>
<g>
	<rect x="809.5" y="17.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="117" height="18"/>
	<text transform="matrix(1 0 0 1 817.2612 30.011)" font-family="'MyriadPro-Regular'" font-size="12">&lt;&lt;IndexInterface&gt;&gt;</text>
</g>
<g>
	<rect x="936.5" y="17.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="72" height="18"/>
	<text transform="matrix(1 0 0 1 946.0591 30.011)" font-family="'MyriadPro-Regular'" font-size="12">Search API</text>
</g>
<text transform="matrix(1 0 0 1 900 90)" font-family="'MyriadPro-Regular'" font-size="12">unindex()</text>
<text transform="matrix(1 0 0 1 909 162)" font-family="'MyriadPro-Regular'" font-size="12">index()</text>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="872.5" y1="35" x2="872.5" y2="37"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="872.5" y1="40" x2="872.5" y2="50"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="872.5" y1="52" x2="872.5" y2="53"/>
	</g>
</g>
<rect x="863.5" y="53.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="18" height="207"/>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="872.5" y1="288" x2="872.5" y2="287"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="872.5" y1="284" x2="872.5" y2="264"/>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="872.5" y1="263" x2="872.5" y2="261"/>
	</g>
</g>
<rect x="963.5" y="90.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="18" height="36"/>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="882" y1="99.5" x2="956" y2="99.5"/>
		<g>
			<polygon points="954,104.486 962.635,99.5 954,94.514 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="889" y1="117.5" x2="963" y2="117.5"/>
		<g>
			<polygon points="891,122.486 882.365,117.5 891,112.514 			"/>
		</g>
	</g>
</g>
<rect x="963.5" y="162.5" fill="#FFFFFF" stroke="#000000" stroke-miterlimit="10" width="18" height="36"/>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="882" y1="171.5" x2="956" y2="171.5"/>
		<g>
			<polygon points="954,176.486 962.635,171.5 954,166.514 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-miterlimit="10" x1="889" y1="189.5" x2="963" y2="189.5"/>
		<g>
			<polygon points="891,194.486 882.365,189.5 891,184.514 			"/>
		</g>
	</g>
</g>
<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="971.5" y1="36" x2="971.5" y2="90"/>
<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="971.5" y1="126" x2="971.5" y2="162"/>
<line fill="none" stroke="#000000" stroke-miterlimit="10" stroke-dasharray="3,3" x1="971.5" y1="198" x2="971.5" y2="288"/>
<text transform="matrix(1 0 0 1 566.29 144)" font-family="'MyriadPro-Regular'" font-size="12">search-index</text>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-width="2" stroke-miterlimit="10" x1="368" y1="172" x2="422" y2="172"/>
		<g>
			<polygon points="418,177.811 439.682,172 418,166.189 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-width="2" stroke-miterlimit="10" x1="774" y1="172" x2="846" y2="172"/>
		<g>
			<polygon points="842,177.811 863.682,172 842,166.189 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-width="2" stroke-miterlimit="10" x1="504" y1="172" x2="534" y2="172"/>
		<g>
			<polygon points="530,177.811 551.682,172 530,166.189 			"/>
		</g>
	</g>
</g>
<g>
	<g>
		<line fill="none" stroke="#000000" stroke-width="2" stroke-miterlimit="10" x1="636" y1="172" x2="666" y2="172"/>
		<g>
			<polygon points="662,177.811 683.682,172 662,166.189 			"/>
		</g>
	</g>
</g>
</svg>



rabbitmq-server

# https://zf2.readthedocs.org/en/latest/modules/zendqueue.custom.html
