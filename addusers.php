<?php
session_start();

// تحقق من صلاحية الأدمين
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "ليس لديك صلاحيات للوصول إلى هنا";
    exit;
}

// مسار ملف المستخدمين
$usersFile = "users.php";

// جلب محتوى ملف المستخدمين
require $usersFile;

// معالجة النموذج عند الإرسال
$message = "";
if (isset($_POST['ok'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // تحقق من أن البيانات موجودة
    if ($username && $password && $role) {

        // إضافة المستخدم الجديد داخل المصفوفة
        $users[] = [
            "username" => $username,
            "password" => $password,
            "role" => $role
        ];

        // إنشاء نص جديد للملف
        $newContent = "<?php\n\$users = [\n";
        foreach ($users as $u) {
            $newContent .= "    [\"username\" => \"{$u['username']}\", \"password\" => \"{$u['password']}\", \"role\" => \"{$u['role']}\"] ,\n";
        }
        $newContent = rtrim($newContent, ",\n") . "\n];\n?>";

        // حفظ التغييرات في ملف users.php
        file_put_contents($usersFile, $newContent);

        $message = "تم إضافة المستخدم بنجاح!";
    } else {
        $message = "يرجى ملء جميع الحقول!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>صفحة إضافة المستخدمين</title>
<style>
    body {
        background: linear-gradient(135deg, #2a1e3d, #3c2a55);
        font-family: Arial, sans-serif;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .container {
        background: #3c2a55;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.5);
        width: 350px;
        text-align: center;
    }
    input, select {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 8px;
        border: none;
        outline: none;
    }
    input[type="submit"] {
        background: #9b59b6;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }
    input[type="submit"]:hover {
        background: #8e44ad;
    }
    .message {
        margin-top: 10px;
        font-weight: bold;
    }
    .back_button{
    position: fixed;
    top: 1%;
    background-color: red;
    font-size: 22px;
    right: 10px;
    }
</style>
</head>
<body>

<div class="container">
    <h2>إضافة مستخدم جديد</h2>
    <form method="post">
        <input type="text" name="username" placeholder="اسم المستخدم" required>
        <input type="text" name="password" placeholder="كلمة المرور" required>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="accountet">Accountet</option>
        </select>
        <input type="submit" name="ok" value="إضافة المستخدم">
    </form>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>
<button onclick="window.history.back()" class="back_button">العودة</button>                

</body>
</html>
