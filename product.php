<?php

    include_once 'database.php';

    function autocomplete_results( $partial_name, $map_hash ) {

        global $db;

        $return_arr = [];

        $partial_name = strtolower($partial_name);
        //var_dump($partial_name);
        $product_query = mysqli_query($db, "SELECT * FROM Products WHERE name LIKE '{$partial_name}%' AND map_hash='$map_hash'");
        
        while ( $row = mysqli_fetch_array($product_query) ) {
            array_push($return_arr, strtolower($row["name"]));
        }

        return $return_arr;

    }

    function autocorrect_result( $keyword, $map_hash ) {
        global $db;

        $keyword = strtolower($keyword);
        $query = mysqli_query($db, "SELECT name FROM Products WHERE map_hash='$map_hash'");

        $shortest = -1;
        while ( $word = mysqli_fetch_array($query)) {
            $lev = levenshtein($keyword, $word["name"]);

            if ($lev == 0) {
                $closest = $word["name"];
                $shortest = 0;

                break;
            }

            if ($lev < $shortest || $shortest < 0) {
                $closest = $word["name"];
                $shortest = $lev;
            }
        }

        if ($shortest == 0) {
            echo $keyword;
        } else {
            echo $closest;
        }
    }

    function search_products_by_category( $category, $map_hash ) {
        global $db;

        $return_arr = [];
        
        $query_string = "SELECT name FROM Products WHERE category='$category' AND map_hash='$map_hash'";
        var_dump($query_string);
        $query = mysqli_query($db, $query_string);
        
        while ( $row = mysqli_fetch_array($query) ) {
            array_push($row);
        }

        return $return_arr;
    }

    function add_product( $map_hash, $name, $image) {
        global $db;
        
        $upload_ok = true;
        $target_dir = "../product_img/";
		$target_file = $target_dir . uniqid() . ".png";
		$image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
		$check = getimagesize($image["tmp_name"]);
		if ($check !== false) { // fake image check
			$upload_ok = true;
		} else {
            $upload_ok = false;
            echo "YUBooli?";
            return NULL; // replace with error code
        }

        if (file_exists($target_file)) { // file exist check
            $upload_ok = false;
            echo "file_already_exists";
            return NULL; // replace with error code
        }

        if ($image["size"] > 500000) { // file size check
            echo "file_is_too_large";
            $upload_ok = false;
            return NULL; // replace with error code
        }

        if ($upload_ok == true) { // try save on server
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                echo "image_uploaded @".$target_file;
            }
            // try add sql entry
            mysqli_query($db, "INSERT INTO Products (name, img_path, map_hash) VALUES ('$name', '$target_file', '$map_hash')");
            if (mysqli_errno($db) != 0) {
                echo "sql_error_occured";
                return mysqli_error($db);
            } else {
                return $target_file; // remove magic numbers
            }
        } else {
            echo "image_upload_failed";
        }

    }


?>