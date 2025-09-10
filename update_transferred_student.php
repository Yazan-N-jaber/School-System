<?php
require "DB_CONNECT.php";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);
    $total_fees = floatval($_POST['total_fees']);
    $old_school = mysqli_real_escape_string($conn, $_POST['old_school']);
    $new_school = mysqli_real_escape_string($conn, $_POST['new_school']);

    $query = "UPDATE transferred_students SET 
        student_name='$student_name',
        gender='$gender',
        class_name='$class_name',
        section_id='$section_id',
        total_fees='$total_fees',
        old_school='$old_school',
        new_school='$new_school'
        WHERE student_id=$id";
    if(mysqli_query($conn, $query)){
        echo "✅ تم تحديث بيانات الطالب بنجاح";
    } else {
        echo "❌ خطأ: " . mysqli_error($conn);
    }
}
?>
