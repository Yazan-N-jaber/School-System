<?php     
    $host = "localhost";
    $user = "root";
    $password = "";
    $db = "school_db";
    $conn = new mysqli($host , $user, $password, $db);
    
    if ($conn -> connect_error){

        die("فشل الاتصال" . $conn -> connect_error);

    }


?>