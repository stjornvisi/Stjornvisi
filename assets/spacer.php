<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 19/03/15
 * Time: 13:31
 */


	$pdo = new PDO('mysql:dbname=stjornvisi_tracker;host=127.0.0.1','root','',array(
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
	));

	$statement = $pdo->prepare("
		UPDATE `Email` set modified = NOW(), touched = 1, agent = :agent, headers = :headers
		WHERE user_hash = :user_hash"
	);

	$statement->execute(array(
		'agent' => isset( $_SERVER['HTTP_USER_AGENT'] )
			? $_SERVER['HTTP_USER_AGENT']
			: null,
		'headers' => isset( $_SERVER )
			? json_encode( $_SERVER )
			: null,
		'user_hash' => isset( $_GET['id'] )
			? $_GET['id']
			: 0
	));

	header('Content-type: image/gif');
	readfile('spacer.gif');
	exit(0);
