<?php

	$db_config = parse_ini_file(".env");

	$host = $db_config["DB_HOST"];
	$port = $db_config["DB_PORT"];
	$database = $db_config["DB_DATABASE"];
	$username = $db_config["DB_USERNAME"];
	$password = $db_config["DB_PASSWORD"];


	$db= mysqli_connect($host, $username, $password, $database, $port);
	//var_dump(mysqli_connect_error());

?>