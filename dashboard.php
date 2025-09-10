<?php
session_start(); 
$role = $_SESSION['role'] ?? ''; 
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة التحكم</title>
<style>
/* ------------------- الخلفية والنص ------------------- */
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #1e1e2f, #232946);
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 50px;
}

/* ------------------- العنوان والترحيب ------------------- */
h1 {
    font-size: 36px;
    color: #c9a6ff;
    margin-bottom: 5px;
}
p {
    font-size: 20px;
    color: #dcd6f7;
    margin-bottom: 40px;
}

/* ------------------- أزرار لوحة التحكم ------------------- */
.buttons-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    width: 90%;
    max-width: 1200px;
    margin-bottom: 50px;
}

.button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-size: 18px;
    border-radius: 12px;
    text-decoration: none;
    color: #fff;
    font-weight: bold;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.button:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.5);
}

/* ------------------- ألوان الأزرار ------------------- */
.add-student { background-color: #4cd137; }
.add-pay { background-color: #00a8ff; }
.about-student { background-color: #9c88ff; }
.my-money { background-color: #e1b12c; }
.edit_fees { background-color: palevioletred; }
.uleft { background-color: #934c95ff; }
.middleline { background-color: #9b59b6; }
.mmiddleline { background-color: #933535ff; }
.student_transferd_show{background: #a86326ff;}
.Statistics_bus{background: #2a69a8ff;}
.remaining_payments{background: #36aa22ff;}

.manage-student { background-color: #4cd137; }
.statistics { background-color: #00a8ff; }
.daily-reports { background-color: #9c88ff; }
.add-account { background-color: #e1b12c; }
.bus {background-color: #743893ff;}

/* ------------------- أزرار النسخ الاحتياطي والخروج ------------------- */
.top-buttons {
    position: fixed;
    top: 10px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 20px;
}

.logout-button, .backup {
    padding: 10px 25px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

.logout-button { background-color: #e84118; color: #fff; }
.logout-button:hover { opacity: 0.85; }

.backup { background-color: #00fc11ff; color: #fff; }
.backup:hover { opacity: 0.85; }

</style>
</head>
<body>

<!-- ------------------- أزرار النسخ الاحتياطي والخروج ------------------- -->
<div class="top-buttons">
    <form method="post">
        <button class="backup" name="backup">نسخ احتياطي</button>
    </form>
    <form method="post">
        <button class="logout-button" name="logout">تسجيل الخروج</button>
    </form>
</div>
<?php
if (isset($_POST['logout'])) {
    session_destroy();
    session_unset();
    header("Location: loginPage.php");
    exit();
}
if (isset($_POST['backup'])) {
    header("Location: backup.php");
    exit();
}
?>

<!-- ------------------- محتوى مركزي ------------------- -->
<div class="container">
    <h1>لوحة التحكم</h1>
    <center><p>مرحبا <?php echo htmlspecialchars($username); ?></p></center>
</div>

<!-- ------------------- أزرار المحاسب ------------------- -->
<div class="buttons-container">
<?php if($role === "accountet"): ?>
    <a href="AddStudent.php" class="button add-student">تسجيل طالب</a>
    <a href="payment.php" class="button add-pay">تسجيل دفعة</a>
    <a href="about-student.php" class="button about-student">لوحة الطلبة</a>
    <a href="show_parents_data.php" class="button remaining_payments">ادارة اولاياء الامور</a>
    <a href="mymoney.php" class="button my-money">الصندوق</a>
    <a href="AddStudentToSameFather.php" class="button edit_fees">اضافة طالب لولي امره</a>
    <a href="admin_section.php" class="button uleft">الشعب والرخصة</a>
    <a href="class_data.php" class="button middleline">بيانات صف</a>
    <a href="spending.php" class="button mmiddleline">المصاريف</a>
    <a href="student_transferd_show.php" class="button student_transferd_show">الطلبة المنتقلين</a>
    <a href="bus_roate.php" class="button bus">خطوط الباص</a>
    <a href="Statistics_bus.php" class="button Statistics_bus">احصائيات الباصات</a>
    <a href="remaining_payments.php" class="button remaining_payments">المبالغ المترتبة</a>

    <?php endif; ?>
<!-- ------------------- أزرار الادمن ------------------- -->
<?php if($role === "admin"): ?>
    <a href="about-student.php" class="button manage-student">إدارة الطلبة</a>
    <a href="show_parents_data.php" class="button remaining_payments">ادارة اولاياء الامور</a>
    <a href="Statistics.php" class="button statistics">الاحصائيات</a>
    <a href="addusers.php" class="button add-account">اضافة حساب</a>
    <a href="acc_mangment.php" class="button daily-reports">ادارة الحسابات</a>
    <a href="mymoney.php" class="button my-money">الصندوق</a>
    <a href="edit_fees.php" class="button edit_fees">تعديل الاقساط</a>
    <a href="admin_section.php" class="button uleft">الشعب والرخصة</a>
    <a href="student_transferd_show.php" class="button student_transferd_show">الطلبة المنتقلين</a>
    <a href="student_transferd_show.php" class="button student_transferd_show">الطلبة المنتقلين</a>
    <a href="bus_roate.php" class="button bus">خطوط الباص</a>
    <a href="Statistics_bus.php" class="button Statistics_bus">احصائيات الباصات</a>
    <a href="remaining_payments.php" class="button remaining_payments">المبالغ المترتبة</a>


    <?php endif; ?>
</div>

</body>
</html>
