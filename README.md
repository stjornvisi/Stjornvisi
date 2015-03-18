
##Get Composer

First of all we need [Composer](https://getcomposer.org/), it will handle all our dependencies.
The best thing is to install it globally, that way it's easier to run it from the command-line.

	$ curl -sS https://getcomposer.org/installer | php
	$ sudo mv composer.phar /usr/local/bin/composer

(if you can't run `composer` from the terminal you may need to do `sudo chmod a+x /usr/local/bin/composer` )

##Get ZF2

Navigate to your _workspace_ directory. Then run the following command. It will use _composer_ to fetch
the latest version of the _ZF2 Skeleton App_ which is just a simple git repository holding the correct
folder structure for a _MVC ZF2 Application_.

	$ composer create-project --stability="dev" zendframework/skeleton-application Stjornvisi

	(Do you want to remove the existing VCS (.git, .svn..) history? [Y,n]? Y)

This will fetch the skeleton application and store it under _Stjornvisi_ directory. Now remove all rubbish

	$ cd Stjornvisi
	$ rm .gitignore
	$ rm -Rf .gitmodules
    $ rm -Rf public/css
    $ rm -Rf public/fonts
    $ rm -Rf public/img
    $ rm -Rf public/js
	$ rm -Rf module/Application


##Get Stjornvisi

Now we are ready to get the actual Stjornvisi module code.

	$ cd module
	$ git clone https://github.com/fizk/Stjornvisi.git Stjornvisi

This will clone our Stjornvisi module into the `module` directory. When we start to develop, this is what we
will change and commit back to GitHub.

Now we need to config out system so that it can connect to Database, Facebook, RabbitMQ and other services.
_ZF2 MVC_ applications looks for files that follow this naming pattern `<workspace>/Stjornvisi/config/autoload/*.local.php`
and load them in as config files. We want to make our own.

Create a new file `<workspace>/Stjornvisi/config/autoload` and call it `stjornvisi.local.php`, add this to it:

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

Now we want to to tell our system about our module and that we want our 3rd party libraries to be
loaded from its `vendor` directory, not in the root.

Open `</workspace>/Stjornvisi/config/application.config.php` and change accordingly.

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

Change this file as well `init_autoloader.php`, so it says:

```php
if (file_exists('module/Stjornvisi/vendor/autoload.php')) {
    $loader = include 'module/Stjornvisi/vendor/autoload.php';
}
```

We have to create a config directory for our testing environment.

Copy/paste the whole `<workspace>/Stjornvisi/config/autoload` directory and name it `test`, change `stjornvisi.local.php`
in that directory to reflect testing environment.

Now go into the module and get all dependencies

	$ cd module/Stjornvisi
	$ composer install

Since (Apache's) httpd folder is `<workspace>/Stjornvisi/public` but all our js/css code
is located in `<workspace>/Stjornvisi/module/Stjonvisi/public`, we have to connect the resources folder to the
httpd folder.

	$ ln -s <full/path/to/workspace>/Stjornvisi/module/Stjornvisi/public/stjornvisi <full/path/to/workspace>/Stjornvisi/public/stjornvisi

Create a new directory in the root of your _workspace_ directory. you can call it `images`,
have the structure like this. The idea here is to keep images in a neutral place. That way we can have many
instances of Stjonvisi running on our computer that all reference the same image folder (since the image folder
can get huge).

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

Make sure you have [Bower](http://bower.io/) set up, and the go to

    $ cd <workspace>/Stjornvisi/module/Stjornvisi/public/stjornvisi/
    $ bower install
    $ bower install bootstrap-sass-official

##Get database
Go to the running production server and do `mysqldump` on the old database. Copy it to your local
machine and install it. (make sure that there exists a database called `stjornvisi_production`)

    $ mysql -u root stjornvisi_production < /<path/to/dump.sql>

Then run the migration script on top of it

    $ mysql -u root stjornvisi_production < <workspace>/Stjornvisi/module/Stjornvisi/assets/db/migrate.sql

Now you need the testing database (make sure that there exists a database called `stjornvisi_test`)

    $ mysql -u root stjornvisi_test < <workspace>/Stjornvisi/module/Stjornvisi/assets/db/stjornvisi-empty.sql



##RabbitMQ
The Stjornvisi module is dependent on RabbitMQ to do it's long running tasks. Installing RabbitMQ is
easily done with brew.

###Install the Server
Before installing make sure you have the latest brews:

    $ brew update

Then, install RabbitMQ server with:

    $ brew install rabbitmq

####Run RabbitMQ Server

    $ rabbitmq-server

The RabbitMQ server scripts are installed into /usr/local/sbin. This is not automatically added to your path, so you may wish to add
PATH=$PATH:/usr/local/sbin to your .bash_profile or .profile. The server can then be started with rabbitmq-server.

All scripts run under your own user account. Sudo is not required.

##Run

Now go back to the root _public_ folder and run the builtin-server

    $ cd <workspace>/Stjornvisi/public
    $ php -S 0.0.0.0:8080


###PHPStorm
I find it better to run the builtin-server from PHPStorm. This is how my config looks like
![alt](https://cloud.githubusercontent.com/assets/386336/5754975/5ef64ad0-9cf3-11e4-8045-e3a81ecde12a.png)

###Other services
But Stjonvisi is a complicated application and it need more processes that just the WebService one. Every time
a `notify` event is fired from a controller, a message is sent to a queue (RabbitMQ). A php process needs to
be started that pulls messages out of this queue. To start that process, create a _Run Configuration_ for
PHPStorm that looks like this
![alt](https://cloud.githubusercontent.com/assets/386336/5755091/99aa1872-9cf4-11e4-97f3-e23eff51ad29.png)
and then actually start it. You can and
should [read more about all the processes](https://github.com/fizk/Stjornvisi/wiki/Processes)

## UnitTests ##
It is really important to be able to unit-test this code. Do the following:

###Database
Make sure you have database called `stjornvisi_test` and that it's exactly the same as the production
database except that's empty.

One way of doing this is to import the `stjornvisi-empty.sql` located in `assets`

    $ mysql -u root stjornvisi_tests < <workspace>/Stjornvisi/module/Stjornvisi/assets/stjornvisi-empty.sql

This may, or may not be the most up to date version of the schema. To make sure that the schemas are up to
date you have to import the migration script. This can be done my running the migration script.

    $ mysql -u root stjornvisi_tests < <workspace>/Stjornvisi/module/Stjornvisi/assets/migrate.sql

This can on the other hand produce errors. The only way to make sure that all migration commands have run is to
open `migrate.sql` in *MySQL Workbench* and run each statement one by one, just to make sure that all of them get
executed.

###Config
Next we have to make sure that the system is set up for unit-test environment. Under the skeleton root
there should be a folder called `<workspace>/Stjornvisi/config/test`, it should mimic the `autoload` folder.

Make sure that `stjornvisi.local.php` file is pointing to the test database

```php
<?php

return array(
	'db' => array(
		'dns' => 'mysql:dbname=stjornvisi_test;host=127.0.0.1',
		'user' => 'root',
		'password' => ''
	),
```

###PHPStorm
Now it's time to config PHPStorm to run PHPUnit tests. Go to *Preferences* and point to _autoloader_ and
_phpunit config_ file

![alt](https://cloud.githubusercontent.com/assets/386336/5752537/ceb28f64-9ccb-11e4-810f-17bcc6957f10.png)
Now you can right-click on any test file and run it as a PHPUnit

![alt](https://cloud.githubusercontent.com/assets/386336/5754360/e4c2d474-9ceb-11e4-8ddb-108e64508086.png)

# Commandline #
This module comes with some command line actions.

To run command-line actions you only have to point your PHP runtime to the index file.

    $ php <workspace>/Stjornvisi/public/index.php [arguments]

and then you can pass in some arguments.

You can read the [Process documentation](https://github.com/fizk/Stjornvisi/wiki/Processes) for
detailed examples.


(LOCK TABLES)(([\w\W]*?))(UNLOCK TABLES;)

AUTO_INCREMENT=[0-9]*


http://brewformulas.org/Wkhtmltopdf

http://sourceforge.net/projects/wkhtmltopdf/?source=typ_redirect

https://github.com/cangelis/php-pdf
https://github.com/wkhtmltopdf/wkhtmltopdf