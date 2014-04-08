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
I assume that you will be working in a folder called ~/workspace

### Setup WebApplication ###
open terminal,
cd your self to your desktop or documents (or any folder you like),
run this command

    $ cd ~/Desktop/
    $ curl -s https://getcomposer.org/installer | php --
    $ php composer.phar create-project -sdev --repository-url="https://packages.zendframework.com" zendframework/skeleton-application ~/workspace/StjornvisiApplication

Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? Y
Yes, you want to do that

Now you have to navigate to ~/workspace/StjornvisiApplication and edit the composer.json file

merge this into the file:

    {
        "name": "zendframework/skeleton-application",
        "description": "Skeleton Application for ZF2",
        "license": "BSD-3-Clause",
        "keywords": [
            "framework",
            "zf2"
        ],
        "prefer-stable": true,
        "minimum-stability": "dev",
        "homepage": "http://framework.zend.com/",
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/zendframework/ZendSearch"
            }
        ],
        "require": {
            "php": ">=5.3.3",
            "zendframework/zendframework": "2.3.*",
            "imagine/imagine": "~0.5.0",
            "zendframework/zend-queue": "dev-master",
            "facebook/php-sdk" : "*",
            "zendframework/zendsearch": ">=0.1"
        }
    }

If you are deploying to production, merge this as well,

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/fizk/Stjornvisi"
        }
    ],
    "require": {
        "fizk/Stjornvisi":"dev-master"
    }

else if you are doing development we are going to do things differently.

Once we have the composer.json file we will run it.

    $ cd ~/workspace/StjornvisiApplication/
    $ php composer.phar update


### Setup for development ###

clone this repository to where ever you want to do your development.

    $ cd ~/workspace
    $ git clone https://github.com/fizk/Stjornvisi.git Stjornvisi

now softlink this repository to you module folder in the StjornvisiApplication
if both the module and the application are in the ~/workspace folder, this would look like this

    $ ln -s ~/workspace/Stjornvisi ~/workspace/StjornvisiApplication/module/Stjornvisi

This is the softlink to install the module, next we need to install the html/css/js 
files, that's another softlink. This has to be done for development as well as production

    $ ln -s ~/workspace/Stjornvisi/public/stjornvisi/ ~/workspace/StjornvisiApplication/public/stjornvisi


Now manually remove some files.

    $ rm -r ~/workspace/StjornvisiApplication/module/Application/

    $ rm -r ~/workspace/StjornvisiApplication/public/css/
    $ rm -r ~/workspace/StjornvisiApplication/public/fonts/
    $ rm -r ~/workspace/StjornvisiApplication/public/img/
    $ rm -r ~/workspace/StjornvisiApplication/public/js

Let StjornvisiApplication know about the module by adding its name to ./StjornvisiApplication/config/application.config.php

    return array(
        // This should be an array of module namespaces used in the application.
        'modules' => array(
            'Stjornvisi', // Replace Application with Stjornvisi
        ),

Now to install the database.

create an empty database in your local MySQL install. Let's say it's name is stjornvisi_development

then from your terminal run

    $ mysql -u root -p stjornvisi_development < ~/workspace/Stjornvisi/assets/stjornvisi_production.sql

next go into ~/workspace/StjornvisiApplication/config/autoload. Create a file called 'stjornvisi.local.php' or 'stjornvisi.global.php'
if you are deploying to production and add this: (and configure to your needs)

    <?php

    return array(
        'db' =>array(
            'dns' => 'mysql:dbname=stjornvisi_production;host=127.0.0.1',
            'user' => 'USER_NAME',
            'password' => 'PASSWORD'
        ),
        'facebook' => array(
            'appId' => 'APP_ID',
            'secret' => 'SECRET',
            'fileUpload' => false, // optional
            'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
            'redirect_uri' => 'http://DOMAIN/callback'
      ),
    );

To get all css/js dependencies we need Bower http://bower.io/

    $ cd ~/workspace/Stjornvisi/public/stjornvisi/
    $ bower install

Now everything should be up and running

For your development to work we need to run composer one last time. This time from the module

    $ cd ~/workspace/Stjornvisi
    $ php composer.phar install


## Commandline ##

This module comes with some command line actions.

To run command-line actions you only have to point your PHP runtime to the index file.

    $ php /path/to/public/index.php [arguments]

and then you can pass in some arguments

### arguments ###
* **search index** Will rebuild search index
* **queue events** will queue up weekly event digest




# https://zf2.readthedocs.org/en/latest/modules/zendqueue.custom.html
