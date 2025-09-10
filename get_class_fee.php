<?php
require "DB_CONNECT.php";

$class_name = $_GET['class_name'] ?? '';
$fee = 0;

if($class_name){
    $stmt = $conn->prepare("SELECT fee FROM class_fees WHERE class_name=? LIMIT 1");
    $stmt->bind_param("s", $class_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        $fee = $row['fee'];
    }
}

echo json_encode(['fee' => $fee]);
?>
