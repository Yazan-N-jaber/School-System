<?php
require "DB_CONNECT.php";
$class_name = $_GET['class_name'] ?? '';
if(!$class_name) exit(json_encode([]));

// جلب id الصف من class_fees
$res = mysqli_query($conn, "SELECT id FROM class_fees WHERE class_name='".mysqli_real_escape_string($conn, $class_name)."' LIMIT 1");
$class = mysqli_fetch_assoc($res);
if(!$class) exit(json_encode([]));
$class_id = $class['id'];

// جلب الشعب المرتبطة بالصف
$sections = [];
$res = mysqli_query($conn, "SELECT id, section_name FROM sections WHERE class_id=$class_id");
while($row = mysqli_fetch_assoc($res)){
    $sections[] = $row;
}

echo json_encode($sections);
?>
