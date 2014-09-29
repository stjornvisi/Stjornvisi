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
* **process index** Will start a listener that listens for entry CRUD and sends entries to be indexed.
* **process notify** Will start a listener for notifications.
* **image generate [--ignore]** Will resample all images from the `original` folder and overwrite the old ones, no questions asked



rabbitmq-server

# https://zf2.readthedocs.org/en/latest/modules/zendqueue.custom.html
