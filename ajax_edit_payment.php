<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';
if(!$role) exit;

$id = (int)($_POST['id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$notes = $_POST['notes'] ?? '';

if($id){
    mysqli_query($conn, "UPDATE payments SET amount=$amount, notes='".mysqli_real_escape_string($conn,$notes)."' WHERE id=$id");
    echo json_encode(['status'=>'success','amount'=>$amount,'notes'=>$notes]);
}
