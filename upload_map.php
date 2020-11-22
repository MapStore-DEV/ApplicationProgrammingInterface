<?php

    require 'map.php';

    $db= mysqli_connect('127.0.0.1', 'admin', 'UGVx9YxkcNNPyfe!', 'StoreMapDB', '3306');
    var_dump(mysqli_connect_error());
    
    if (isset($_POST['map'])) {
        $json_map = $_POST['map'];
        if (strlen($json_map) > 0 && is_valid_json($json_map)) {
            $query = "INSERT INTO Maps (name, encoded_map) VALUES ('testmap', '$json_map');";
            mysqli_query($db, $query);
            var_dump(mysqli_error());
        }
    }

?>