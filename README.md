## Install  ##

open terminal,
cd your self to your desktop or documents,
run this command

    $ cd ~/Desktop/
    $ curl -s https://getcomposer.org/installer | php --
    $ php composer.phar create-project -sdev --repository-url="https://packages.zendframework.com" zendframework/skeleton-application ~/workspace/StjornvisiApplication

Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? Y
Yes, you want to do that

Now you have to navigate to ~/workspace/StjornvisiApplication and edit the composer.json file

merge this into the file:

    "require": {
        "imagine/imagine": "~0.5.0"
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


Now for development.

clone this repository to where ever you want to do your development.

    $ cd ~/workspace
    $ git clone https://github.com/fizk/Stjornvisi.git Stjornvisi

now softlink this repository to you module folder in the StjornvisiApplication
if both the module and the application are in the ~/workspace folder, this would look like this

    $ ln -s ~/workspace/Stjornvisi ~/workspace/StjornvisiApplication/module/Stjornvisi

This is the softlink to install the module, next we need to install the html/css/js 
files, that's another softlink

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

create an empty database in your local MySQL install. Le's say it's name is stjornvisi_development

then from your terminal run

    $ mysql -u root -p stjornvisi_development < ~/workspace/Stjornvisi/assets/stjornvisi_production.sql

next go into ~/workspace/Stjornvisi/config and config what you need.

To get all css/js dependencies we need Bower http://bower.io/

    $ cd ~/workspace/Stjornvisi/public/stjornvisi/
    $ bower install

Now everything should be up and running

For your development to work we need to run composer one last time. This time from the module

    $ cd ~/workspace/Stjornvisi
    $ php composer.phar install

