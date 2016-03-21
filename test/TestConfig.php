<?php
return array(
	'modules' => array(
		'Stjornvisi',
	),
	'module_listener_options' => array(
		'config_glob_paths'    => array(
			'../../../config/test/{,*.}{global,local}.php',
		),
		'module_paths' => array(
			'module',
			'vendor',
		),
	),
	'db' => array(
		'dns' => 'mysql:dbname=stjornvisi_test;host=127.0.0.1',
		'user' => 'stjornvisi_t',
		'password' => 'asdf_test'
	),
);
