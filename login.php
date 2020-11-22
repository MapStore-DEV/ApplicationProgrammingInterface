<?php

	include_once 'database.php';

	//var_dump($db);
	
	function generate_csrf_token($length = 30)
	{

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;

	}

	function hash_string( $target_string )
	{

		$return_string = $target_string;

		for ($i=0; $i < 5; $i++) $return_string = md5($return_string);

		return $return_string;

	}


	function search_user_by_name( $username )
	{

		global $db;

		$return_arr = [];

		$qry = mysqli_query( $db, " SELECT * FROM Users WHERE name='".$username."' " );

		if( mysqli_num_rows($qry) > 0 ) $return_arr = mysqli_fetch_array($qry);

		return $return_arr;

	}

	function search_user_by_email( $email )
	{

		global $db;

		$safe_email_param = mysqli_real_escape_string($email);
		$return_arr = [];
		$query = mysqli_query($db, "SELECT * FROM Users WHERE email='".$email."'");

		if (mysqli_num_rows($query) > 0) $return_arr = mysqli_fetch_array($query);

		return $return_arr;

	}

	function search_user_by_csrf_token( $csrf_token )
	{

		global $db;

		$return_arr = [];

		$qry = mysqli_query( $db, " SELECT * FROM Users WHERE csrf_token='".$csrf_token."' " );

		if( mysqli_num_rows($qry) > 0 ) $return_arr = mysqli_fetch_array($qry);

		return $return_arr;

	}

	//echo generate_csrf_token();


?>