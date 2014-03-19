## Install  ##

open terminal
cd your self to your desktop or documents
run this command

`
$ cd ~/Deskttop
$ curl -s https://getcomposer.org/installer | php --
php composer.phar create-project -sdev --repository-url="https://packages.zendframework.com" zendframework/skeleton-application ~/workspace/StjornvisiApplication
`

Now you have to navigate to ~/workspace/StjornvisiApplication and edit the composer.json file

merge this into the file:

    "require": {
        "imagine/imagine": "~0.5.0"
    },
    "require-dev": {
        "phpunit/phpunit": "3.7.*",
        "phpunit/dbunit": ">=1.2",
        "phpunit/php-code-coverage": "*",
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

in ~/workspace/StjornvisiApplication, do

$ php composer.phar install

you will only run this command once, after that you will run $ php composer.phar update

Now for development.

clone this repository to where ever you want to do your development.

now softlink this repository to you module folder in the StjornvisiApplication
if both the module and the application are in the ~/workspace folder, this would look like this

$ ln -s ~/workspace/Stjornvisi ~/workspace/StjornvisiApplication/module/Stjornvisi

This is the softlink to install the module, next we need to install the html/css/js 
files, that's another softlink

$ ln -s ~/workspace/Stjornvisi/public/stjornvisi ~/workspace/StjornvisiApplication/public/stjornvisi

Now to install the database.

create an emply database in your local MySQL install. Le's say it's name is stjornvisi_development

then from your terminal run

$ mysql -u root -p stjornvisi_development < ~/workspace/Stjornvisi/assets/stjornvisi_production.sql

next go into ~/workspace/Stjornvisi/config and config what you need


