<?php

	//header('Access-Control-Allow-Origin: http://localhost');

	include 'phpqrcode/qrlib.php';

	include_once 'database.php';
	include_once 'login.php';
	include_once 'map.php';
	include_once 'product.php';

	//setting things up
	session_start();
	
	$logged_in_user = "";

	$query = "";

	$params = [];

	if(!isset($_SESSION['logged_in_user'])) $_SESSION['logged_in_user'] = "n0ne";
	$logged_in_user = $_SESSION['logged_in_user'];

	// print_r($_POST);
	//var_dump($_GET);
	// var_dump($_FILES);

	// var_dump($_POST);
	// foreach($_POST as $key => $value) {
	// 	echo "POST parameter $key has $value";
	// }

	if(isset($_POST['q'])) $query = $_POST['q'];
	else if (isset($_GET['q'])) $query = $_GET['q'];

	if(isset($_POST['p'])) $params = explode(";;", $_POST['p']);
	else if (isset($_GET['p'])) $params = explode(";;", $_GET['p']);

	//I don't like switch :)
	if($query == "show_testing_vals")
	{
		echo $query." ";
		foreach ($params as $param) {
			echo $param." ";
		}
	}

	else if($query == "CEmdT9qYmSZiYATES2Q6") //search product
	{
		$current_map = $params[0];
		$product_name = $params[1];

		$map_json_raw = search_map_by_hash($current_map)["encoded_map"];
		if (is_valid_json($map_json_raw)) {
			$map_json = json_decode($map_json_raw);
			//var_dump($map_json);

			$blocks = $map_json->blocks;
			foreach ($blocks as $block) {
				$products = $block->products;

				foreach ($products as $product) {
					if ($product->name == $product_name) {
						echo json_encode($product);
					}
				}
			}
		} else {
			echo "map_json_corrupted";
		}
		
	}


	else if($query == "P3Snm8DdlNwyg069LC1h") //search product suggestions
	{

		$map_hash = $params[0];
		$partial_name = $params[1];

        $suggestions = autocomplete_results($partial_name, $map_hash);

        foreach ($suggestions as $word) {
            echo $word;
            echo ";;";
        }

	}

	else if ($query == "JzM01hLCF2BrTE9bFsEa") // search for multiple products
	{

		$map_hash = $params[0];
		$product_list = explode(",", $params[1]);

		$list_positions = get_product_locations_list($map_hash, $product_list);

	}


	else if($query == "SaYxXXTVvSviuB3t0Qes") //search product categories list
	{

		$map_hash = $params[0];
        $category = $params[1];

		$result = search_products_by_category($category, $map_hash);
		var_dump($result);

		foreach ($result as $word) {
            echo $word;
            echo ";;";
        }

	}

	else if ($query == "rEBp5oGdqm0SJu4OPJXb") //search product autocomplete
    {

        $map_hash = $params[0];
        $keyword = $params[1];

        $suggestion = autocorrect_result($keyword, $map_hash);

        echo $suggestion;

	}
	
	else if ( $query == "tTGjsXp8VRD573uqUh9L" ) //add product 
	{

		if ($logged_in_user == "n0ne") {
			echo "YUBooli?!";
		} else {

			$upload_ok = true;
			$map_hash = $params[0];
			$name = $params[1];
			$target_block = $params[2];
			$coordinate_x = $params[3];
			$coordinate_y = $params[4];
			$image = $_FILES["file"];
	
			$image_url = add_product($map_hash, $name, $image);
			add_product_to_map( $map_hash, $name, $target_block, $coordinate_x, $coordinate_y, $image_url);

		}
	}

	else if( $query == "tiJrTMM0wVD0F1f57cx6" ) //create new map
	{

		$name = $params[0];
		$new_map_hash = generate_map_hash( 10 );

		mysqli_query($db,"INSERT INTO Maps (name,encoded_map,map_hash) VALUES ('$name','','".$new_map_hash."') ");

		echo $new_map_hash;

	}


	else if( $query == "AuTstCDGNPXZYqSah7rw" ) //update map
	{

		$target_map_hash = $params[0];
		$encoded_map = $params[1];

		if( strlen($encoded_map) > 0 && is_valid_json($encoded_map) )
		{

			mysqli_query( $db,"UPDATE Maps SET encoded_map='".$encoded_map."' WHERE map_hash='".$target_map_hash."'; " );

			echo "map_updated_success";

		}

	}


	else if($query == "C1FkVUvyruw0cWW9kDOt") //turn update map off
	{



	}	

	else if($query == "g02mO6i6vUnNXN3At8mB") //get all map hashes
	{

		echo json_encode(get_all_map_hashes());

	}

	else if($query == "mlywI6MSvGdff8WZdU2R") //get map hash
	{

		$map_name = $params[0];

		echo search_map_by_name($map_name);

	}


	else if($query == "ZgmIgQtPgtKIqn4dl7Sg") //get map
	{

		$target_map_hash = $params[0];

		$target_map_arr = search_map_by_hash( $target_map_hash );

		if( $target_map_hash != [] )
		{

			echo $target_map_arr['encoded_map'];

		}
		else
		{
			echo "map_not_found";
		}

	}


	else if($query == "FgKMSYVTqAG850PeSABs") //create user
	{

		$user_details = search_user_by_name($params[0]);

		if( count($user_details) == 0 )
		{

			$new_csrf_token = generate_csrf_token();
			$username = $params[0];
			$pass = hash_string($params[1]);
			$email = $params[2];

			//echo $username." ".$pass." ".$new_csrf_token;

			mysqli_query($db,"INSERT INTO Users (name,password,email,csrf_token) VALUES ('".$username."','".$pass."','".$email."','".$new_csrf_token."') ");

			$_SESSION['logged_in_user'] = $new_csrf_token;

			echo "account_created_successfully";

		}
		else
		{
			echo "username_already_exists";
		}



	}


	else if($query == "XfPO7bazdWnNG9jrVEZW") //login
	{

		$user_name = $params[0];
		$pass = hash_string($params[1]);

		$user_data = search_user_by_name($user_name);

		if ($user_data != []) {
			if ($user_data["password"] == $pass) { 
				//returning csrf token after generating a new one
				$new_csrf_token = generate_csrf_token();
				mysqli_query($db,"UPDATE Users SET csrf_token='".$new_csrf_token."' WHERE name='".$user_name."' ");
				$_SESSION['logged_in_user'] = $new_csrf_token;
				echo $new_csrf_token;
			} else {
				echo "login_wrong_password";
			}
		}
		else
		{
			echo "user_not_found";
		}

	}


	else if($query == "3UgpoCEEOMsEjC2PlUEr") //get logged in user
	{

		echo $logged_in_user;

	}


	else if($query == "GcwLIXJHfcV3G2FX93hf") //get logged in user details
	{

		if( $logged_in_user != "n0ne" )
		{
			//now search by csrf
			$target_csrf_token = $logged_in_user;
			$user_data = search_user_by_csrf_token($target_csrf_token);

			if( $user_data != [] )
			{
				echo $user_data['name'].";;".$user_data['email'];
			}
			else
			{
				echo "csrf_user_not_found";
			}

		}
		else
		{
			echo "not_logged_in";
		}


	}

	else if( $query == "exhRQ7ks2VTnpj77IOJS" ) //logout destroy session
	{

		$_SESSION['logged_in_user'] = "n0ne";
		$logged_in_user = "n0ne";
		echo "1";

	}
	else if ( $query == "6phXdNQ9kpBqi5cLHmqH" ) //generate QR code
	{

		$qr_path = "/var/www/html/qr_img/";
		$store_hash = $params[0];
		$data = "";

		$map = search_map_by_hash($store_hash);
		if ($map == []) {
			echo "map_not_found";
        } else if ($map["qr_path"]) {
            echo $map["qr_path"];
        } else {
			$hash = $map["map_hash"];

			$data = "ZgmIgQtPgtKIqn4dl7Sg;;" . $hash;
			$img_name = md5(uniqid()).".png";
			$file = $qr_path.$img_name;
			$ecc = 'L';
			$pixel_size = 60;
			$frame_size = 5;
            $update_exit_code = update_qr_path($store_hash, "qr_img/".$img_name);
			if ($update_exit_code != 0) {
				echo "ERR:".$update_exit_code;
			} else {
				QRcode::png($data, $file, $ecc, $pixel_size, $frame_size);
				echo "qr_img/".$img_name;
			}

		}
		

	}
	else {
		echo $query;
	}


?>