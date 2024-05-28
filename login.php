<?php
    const NAME_DB = 'aguaVIVA';
    const USERNAME_DB = 'jotavsevla';
    const PW_DB = 'sapinho12';
    const HOST_DB = 'localhost';

    try {
        $conn = new PDO('mysql:host=localhost; dbname=aguaVIVA', USERNAME_DB, PW_DB);

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $data = $conn->query('SELECT * FROM users WHERE user ='. $conn->quote('raul_correa'));

        foreach ($data as $row) {
            print_r($row);
        }
    }
    catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

//    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
//        if(empty(trim($_POST['username'])) && empty(trim($_POST['password']))){
//            echo ''
//        } else{

      //  }
    //}
