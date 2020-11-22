<?php

    include_once 'database.php';

    class Map {
        private $type;
        private $data;

        public function __construct($p_type, $p_data) {
            $this->type = $p_type;
            $this->data = $p_data;
        }

        public function get_type() {
            return $this->type;
        }

        public function get_data() {
            return $this->data;
        }

        /*
            Header: number of blocks (int, most likely 2 bytes will be enough)
            Block_array:
                name (string, unpack and take as is)
                id (int, 2 bytes)
                height: (assume it is px, remove measurement units, hold 4bytes)
                width: (same as before)
                borderRadius: (assume it is px, remove measurement units, hold 4 bytes)
                background_color: (3 ints, 1 byte each)
                transform: (string, unpack and take as is)
                font_size: (again, pixel, hold 1 byte, don't think it will exceed 255)
                font_color: (3 ints, 1 byte each)
                products: Product array:
                    parent_block: (this is completely useless, we can remove this)
                    x: (double, costly but 8 bytes will be necesarry)
                    y: (same as before)
                    name: (string, unpack and take as is)
                    image: (string, might be better if we create a lookup table for this and replace it with an id, 2-4 bytes should cut it)
        */
        public function encode_map() {
            if ($this->type != 'map/json') {
                return 1;
            }
            $map_encoded = [];
            $block_count = count($this->data->blocks);
            $map_encoded .= strval($block_count);
            $map_encoded .= " ";
            foreach ($this->data->blocks as $block) {
                
            }

            return $map_encoded;
        }
    }

    function is_valid_json($str) {
        json_decode($str);
        return json_last_error() == JSON_ERROR_NONE;
    }

    function generate_map_hash( $length )
    {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;

    }

    function search_map_by_hash( $map_hash )
    {

        global $db;

        $return_arr = [];

        $qry = mysqli_query( $db, " SELECT * FROM Maps WHERE map_hash='".$map_hash."' " );

        if( mysqli_num_rows($qry) > 0 ) $return_arr = mysqli_fetch_array($qry);

        return $return_arr;

    }

    function search_map_by_name( $map_name ) {
        global $db;

        $return_arr = [];

        $qry = mysqli_query( $db, " SELECT map_hash FROM Maps WHERE name='$map_name'");

        if ( mysqli_num_rows($qry) > 0 ) $return_arr = mysqli_fetch_array($qry);

        return $return_arr["map_hash"];
    }

    function get_all_map_hashes() {
        global $db;

        $return_arr = [];
        $qry = mysqli_query($db, "SELECT name, map_hash FROM Maps");

        while ($row = mysqli_fetch_array($qry) ) {
            $return_arr[$row["name"]] = $row["map_hash"];
        }

        return $return_arr;
    }

    function update_qr_path( $map_hash, $qr_path ) {

        global $db;

        $qry = mysqli_query($db, " UPDATE Maps SET qr_path='$qr_path' WHERE map_hash='$map_hash'");

        return mysqli_errno($db);

    }

    function add_product_to_map( $map_hash, $name, $target_block, $coordinate_x, $coordinate_y, $image_url) {
        global $db;
        
        $new_product = array("block"=>$target_block, "x"=>$coordinate_x, "y"=>$coordinate_y, "name"=>$name, "image"=>$image_url);
        $query_string = "SELECT encoded_map FROM Maps WHERE map_hash='$map_hash'";
        $query = mysqli_query($db, $query_string);

        if ( mysqli_num_rows($query) != 1 ) {
            return 1; // replace with error code
        }

        $encoded_map = mysqli_fetch_array($query)["encoded_map"];
        $map = json_decode($encoded_map);

        foreach ($map->blocks as $block) {
            if ($block->id == $target_block) {
                array_push($block->products, $new_product);
                break;
            }
        }

        $new_encoded_map = json_encode($map);

        mysqli_query($db, "UPDATE Maps SET encoded_map='$new_encoded_map' WHERE map_hash='$map_hash'");

        if (mysqli_errno($db) != 0) {
            return mysqli_errno($db);
        }

        return 0;

    }

    function get_product_locations_list($map_hash, $product_list)
    {

        $return_arr = [];

        $map_json_raw = search_map_by_hash($map_hash)["encoded_map"];
		if (is_valid_json($map_json_raw)) {
            $map_json = json_decode($map_json_raw);
            $blocks = $map_json->blocks;
            foreach ($product_list as $product_name)
            {
                foreach ($blocks as $block) {
                    $products = $block->products;

                    foreach ($products as $product) {
                        if ($product->name == $product_name) {
                            array_push($return_arr, $product);
                        }
                    }
                }
            }

            echo json_encode($return_arr);
		} else {
			echo "map_json_corrupted";
		}

    }


?>