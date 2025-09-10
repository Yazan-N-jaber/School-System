<?php
require "DB_CONNECT.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $new_school = mysqli_real_escape_string($conn, $_POST['new_school']);

    // جلب بيانات الطالب
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id = $student_id");
    if ($res && mysqli_num_rows($res) > 0) {
        $student = mysqli_fetch_assoc($res);

        // حساب الرسوم الكلية بعد الخصم
        $total_fees = ($student['fees'] + $student['bus_fees']) - $student['discount'];

        // إدخال بياناته في جدول المنتقلين مع كل التفاصيل
        $insert = $conn->prepare("
            INSERT INTO transferred_students
            (student_id, student_name, gender, class_name, section_id, father_id, old_school, total_fees, new_school)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param(
            "isssiisss",
            $student['id'],
            $student['student_name'],
            $student['gender'],
            $student['class'],            // الصف
            $student['section_id'],
            $student['father_id'],
            $student['previous_school'],  // المدرسة السابقة
            $total_fees,                  // الرسوم بعد الخصم
            $new_school
        );

        if ($insert->execute()) {
            // حذف الطالب من جدول students
            mysqli_query($conn, "DELETE FROM students WHERE id = $student_id");
            echo "✅ تم نقل الطالب مع كل البيانات بنجاح";
        } else {
            echo "❌ خطأ في الإدخال: " . $insert->error;
        }
    } else {
        echo "⚠️ الطالب غير موجود";
    }
}
?>
