<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';
if(!$role) exit;

$id = $_POST['id'] ?? 0;
$id = (int)$id;
if($id){
    mysqli_query($conn, "DELETE FROM payments WHERE id = $id");
    echo 'success';
}
