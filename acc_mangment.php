<?php
$users_file = 'users.php';
$users = [];

// قراءة البيانات الأصلية
if(file_exists($users_file)){
    include $users_file; // يجب أن يحتوي على $users
}

// حفظ التعديلات عند الإرسال
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    foreach($_POST['username'] as $i=>$username){
        $users[$i]['username'] = $username;
        $users[$i]['password'] = $_POST['password'][$i];
        $users[$i]['role'] = $_POST['role'][$i];
    }

    // كتابة الملف مرة أخرى
    $content = "<?php\n\$users = [\n";
    foreach($users as $u){
        $content .= "    [\"username\" => \"".addslashes($u['username'])."\", \"password\" => \"".addslashes($u['password'])."\", \"role\" => \"".addslashes($u['role'])."\"],\n";
    }
    $content .= "];\n?>";

    file_put_contents($users_file, $content);
    $message = "تم حفظ التعديلات بنجاح ✅";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل المستخدمين</title>
<style>
body { font-family: Tahoma, sans-serif; background:#1a1525; color:#f0e9ff; padding:20px;}
table { width:80%; margin:auto; border-collapse:collapse; }
th, td { padding:10px; border:1px solid #3a2d4f; text-align:center; }
th { background:#4c1d95; }
input, select { width:90%; padding:5px; border-radius:4px; border:none; background:#2c2c44; color:#fff; }
button { padding:5px 10px; border:none; border-radius:5px; background:#4cd137; color:#fff; cursor:pointer; }
.back_button{ position: fixed; top: 1%; background-color: red; font-size: 22px; right: 10px; }
</style>
</head>
<body>

<h2 style="text-align:center;">تعديل بيانات المستخدمين</h2>
<?php if(isset($message)) echo "<p style='text-align:center; color:greenyellow;'>$message</p>"; ?>

<form method="POST">
<table>
<tr><th>اسم المستخدم</th><th>كلمة المرور</th><th>الدور</th></tr>
<?php foreach($users as $i=>$user): ?>
<tr>
<td><input type="text" name="username[]" value="<?= htmlspecialchars($user['username']) ?>"></td>
<td><input type="text" name="password[]" value="<?= htmlspecialchars($user['password']) ?>"></td>
<td>
<select name="role[]">
<option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>admin</option>
<option value="accountet" <?= $user['role']=='accountet'?'selected':'' ?>>accountet</option>
</select>
</td>
</tr>
<?php endforeach; ?>
</table>
<div style="text-align:center; margin-top:20px;">
<button type="submit">حفظ التعديلات</button>
</div>
</form>
<button onclick="window.history.back()" class="back_button">العودة</button>

</body>
</html>
