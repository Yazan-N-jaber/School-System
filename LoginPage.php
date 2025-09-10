<?php
session_start();
require "users.php"; // استدعاء المصفوفة

$replay = "";

if (isset($_POST["submit"])) {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    $found = false;
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $username;
            $found = true;
            $replay = "تم تسجيل الدخول بنجاح!";
            header("Location: dashboard.php");
            exit;
        }
    }
    if (!$found) {
        $replay = "خطأ في اسم المستخدم أو كلمة المرور";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل الدخول</title>
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #1e1e2f, #232946);
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.login-box {
  background: #2a2a3d;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.5);
  width: 320px;
  text-align: center;
  color: #fff;
}

.login-box h2 {
  margin-bottom: 20px;
  color: #9b59b6;
}

.login-box input {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  border: none;
  border-radius: 8px;
  outline: none;
}

.login-box input[type="text"], 
.login-box input[type="password"] {
  background: #3c3c55;
  color: #fff;
}

.login-box input[type="submit"] {
  background: #9b59b6;
  color: #fff;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

.login-box input[type="submit"]:hover {
  background: #8e44ad;
}

.login-box a {
  display: block;
  margin-top: 15px;
  color: #aaa;
  text-decoration: none;
  font-size: 14px;
}

.login-box a:hover {
  color: #fff;
}
</style>
</head>
<body>

<div class="login-box">
  <h2>تسجيل الدخول</h2>
  <form method="post">
    <input type="text" placeholder="اسم المستخدم" name="username" required>
    <input type="password" placeholder="كلمة المرور" name="password" required>
    
    <?php 
    if ($replay) {
        $color = ($replay === "تم تسجيل الدخول بنجاح!") ? "green" : "red";
        echo "<p style='color: $color;'>$replay</p>";
    }
    ?>

    <input type="submit" value="دخول" name="submit">
  </form>
  <a href="#">نسيت كلمة المرور؟</a>
</div>

</body>
</html>
