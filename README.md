
##Get Composer

open a Terminal

	$ curl -sS https://getcomposer.org/installer | php
	$ sudo mv composer.phar /usr/local/bin/composer

(if you can't run `composer` from the terminal you may need to do `sudo chmod a+x /usr/local/bin/composer` )

##Get ZF2

navigate to your _workspace_ directory

	$ composer create-project --stability="dev" zendframework/skeleton-application Stjornvisi

	(Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? Y)

This will fetch a skeleton application and store it under _Stjornvisi_ directory. Now remove all rubbish

	$ cd Stjornvisi
	$ rm .gitignore
	$ rm -Rf .gitmodules
    $ rm -Rf public/css
    $ rm -Rf public/fonts
    $ rm -Rf public/img
    $ rm -Rf public/js
	$ rm -Rf module/Application


##Get Stjornvisi

	$ cd module
	$ git clone https://github.com/fizk/Stjornvisi.git Stjornvisi


Open `<root>/config/application.config.php` and change accordingly.


```php
return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        'Stjornvisi',
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => array(
            './module',
            './module/Stjornvisi/vendor',
        ),
```

Create a new file `<root>/config/autoload` and call it `stjornvisi.local.php`, add this to it:

```php
<?php

return array(
	'db' => array(
		'dns' => 'mysql:dbname=stjornvisi_production;host=127.0.0.1',
		'user' => 'root',
		'password' => ''
	),
	'queue' => array(
		'host' => 'localhost',
		'port' => 5672,
		'user' => 'guest',
		'password' => 'guest',
	),
	'facebook' => array(
		'appId' => '1429359840619871',
		'secret' => '40bd72b736684cf4bc1ee786d1786da0',
		'fileUpload' => false, // optional
		'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
	),
	'linkedin' => array(
		'appId' => '7710a9lfze4o6b',
		'secret' => '7RMpNiWE6Y4V1X7J',
	),
);
```

Change this file as well : init_autoloader.php, so it says:
```php
if (file_exists('module/Stjornvisi/vendor/autoload.php')) {
    $loader = include 'module/Stjornvisi/vendor/autoload.php';
}
```


Copy/paste the whole `<root>/config/autoload` directory and name it `test`, change `stjornvisi.local.php` in that directory  to reflect testing enviroment.

Now go into the module and get all dependencies

	$ cd module/Stjornvisi
	$ composer install


Connect the resources folder

	$ ln -s <full/path/to/workspace>/Stjornvisi/module/Stjornvisi/public/stjornvisi <full/path/to/workspace>/Stjornvisi/public/stjornvisi

Create a new directory in the root of your _workspace_ directory. you can call it `images`, have the structure like this

	images
		|
		+ --- 60
		|
		+ --- 100
		|
		+ --- 300
		|
		+ --- 300-square
		|
		+ --- original

Make sure that it's read and writable

	$ chmod -R a+x  <workspace>/images

and connect that to the resources folder

	$ ln -s <full/path/to/workspace>/images <full/path/to/workspace>/Stjornvisi/module/Stjornvisi/public/stjornvisi/images


##Get resources

Make sure you have bower set up, and the go to

    $ <full/path/to/workspace>/Stjornvisi/module/Stjornvisi/public/stjornvisi/
    $ bower install
    $ bower install bootstrap-sass-official

##Get database
Go to the running production server and do `mysqldump` on the old database. Copy it to your local machine and install it. (make sure that there exists a database called `stjornvisi_production`)

    $ mysql -u root stjornvisi_production < /<path/to/dump.sql>

Then run the migration script on top of it

    $ mysql -u root stjornvisi_production < <full/path/to/workspace>/Stjornvisi/module/Stjornvisi/assets/db/migrate.sql

Then you need to run the testing database (make sure that there exists a database called `stjornvisi_test`)

    $ mysql -u root stjornvisi_test < <full/path/to/workspace>/Stjornvisi/module/Stjornvisi/assets/db/stjornvisi-empty.sql

##Run

Now go back to the root _public_ folder and run the builtin-server

    $ cd <full/path/to/workspace>/Stjornvisi/public
    $ php -S 0.0.0.0:8080

##RabbitMQ

###Install the Server
Before installing make sure you have the latest brews:

    $ brew update

Then, install RabbitMQ server with:

    $ brew install rabbitmq

####Run RabbitMQ Server
The RabbitMQ server scripts are installed into /usr/local/sbin. This is not automatically added to your path, so you may wish to add
PATH=$PATH:/usr/local/sbin to your .bash_profile or .profile. The server can then be started with rabbitmq-server.

All scripts run under your own user account. Sudo is not required.




















## UnitTests ##

![screen shot 2015-01-15 at 15 33 39](https://cloud.githubusercontent.com/assets/386336/5752537/ceb28f64-9ccb-11e4-810f-17bcc6957f10.png)

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
