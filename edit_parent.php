<?php
session_start();
require "DB_CONNECT.php";

$role = $_SESSION['role'] ?? '';
if (!$role) {
    echo "ليس لديك صلاحيات للوصول لهنا";
    exit;
}

$parent_id = $_GET['id'] ?? 0;
$parent_id = intval($parent_id);

// جلب بيانات الأب الحالي
$res = mysqli_query($conn, "SELECT * FROM parents WHERE id=$parent_id");
$parent = mysqli_fetch_assoc($res);

if (!$parent) {
    echo "ولي الأمر غير موجود!";
    exit;
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $another_phone = mysqli_real_escape_string($conn, $_POST['another_phone'] ?? '');

    $update = mysqli_query($conn, "
        UPDATE parents SET 
            father_name='$father_name', 
            phone='$phone', 
            another_phone='$another_phone' 
        WHERE id=$parent_id
    ");

    if ($update) {
        echo "<p style='color:green; text-align:center;'>تم تحديث بيانات الأب بنجاح!</p>";
        // إعادة جلب البيانات بعد التحديث
        $res = mysqli_query($conn, "SELECT * FROM parents WHERE id=$parent_id");
        $parent = mysqli_fetch_assoc($res);
    } else {
        echo "<p style='color:red; text-align:center;'>حدث خطأ أثناء التحديث!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل بيانات الأب</title>
<style>
body {background:#1e1e2f; color:#fff; font-family: Arial,sans-serif; padding:20px;}
form {width:400px; margin:auto; background:#2c2c44; padding:20px; border-radius:8px;}
label {display:block; margin-top:10px; font-weight:bold;}
input[type="text"] {width:100%; padding:8px; margin-top:5px; border-radius:4px; border:none;}
input[type="submit"] {margin-top:15px; padding:10px 20px; border:none; border-radius:6px; background:green; color:#fff; cursor:pointer; font-size:16px;}
.back_btn{margin-top:10px; padding:10px 20px; background:red; color:#fff; border:none; border-radius:6px; cursor:pointer; text-decoration:none; display:inline-block;}
</style>
</head>
<body>

<h1 style="text-align:center;">تعديل بيانات الأب</h1>

<form method="post">
    <label>اسم الأب</label>
    <input type="text" name="father_name" value="<?= htmlspecialchars($parent['father_name']) ?>" required>

    <label>الهاتف الأصلي</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($parent['phone']) ?>">

    <label>الهاتف الاحتياطي</label>
    <input type="text" name="another_phone" value="<?= htmlspecialchars($parent['another_phone']) ?>">

    <input type="submit" value="تحديث البيانات">
</form>

<div style="text-align:center;">
    <a href="javascript:history.back()" class="back_btn">العودة</a>
</div>

</body>
</html>
