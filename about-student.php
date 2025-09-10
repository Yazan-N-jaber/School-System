<?php
session_start();
require "DB_CONNECT.php";

$role = $_SESSION['role'] ?? '';
if (!$role) {
    echo "ليس لديك صلاحيات للوصول هنا";
    exit;
}

// عملية البحث
$searchTerm = $_POST['search'] ?? '';
$where = '';
if ($searchTerm) {
    $searchTerm = "%$searchTerm%";
    $where = "WHERE student_name LIKE '$searchTerm'";
}

// جلب بيانات الطلاب مع الدفعات
$query = "SELECT students.id, discount , students.student_name, students.class, students.fees, students.bus_fees, students.previous_school , students.bus_type , students.age , students.address , parents.phone ,sections.section_name ,
          COALESCE(SUM(payments.amount), 0) AS paid
          FROM students 
          LEFT JOIN payments ON students.id = payments.student_id
          LEFT JOIN parents ON students.father_id = parents.id
          LEFT JOIN sections ON students.section_id = sections.id
          $where
          GROUP BY students.id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>لوحة الطلبة</title>
<style>
body {
    background: linear-gradient(135deg, #1e1e2f, #232946);
    font-family: Arial, sans-serif;
    color: #fff;
    padding: 20px;
}
h1 {
    text-align: center;
    color: greenyellow;
    font-size: 50px;
    margin-bottom: 30px;
}
form input[type="search"] {
    width: 60%;
    padding: 10px;
    font-size: 20px;
    border-radius: 8px;
    border: none;
    background: #3c3c55;
    color: #fff;
}
form button {
    padding: 10px 20px;
    font-size: 20px;
    border: none;
    border-radius: 8px;
    background: #2c2c3e;
    color: #fff;
    cursor: pointer;
}
form button:hover { background: #444466; }

table {
    width: 90%;
    margin: auto;
    border-collapse: collapse;
    background: #1f1f34;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.5);
}
th, td {
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #444;
}
th {
    background: #2d2d44;
    color: greenyellow;
}
tr:hover { background: #2c2c3e; }

.btn_pay {
    background-color: #14141bff;
    color: white;
    font-size: 16px;
    padding: 5px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.btn_pay:hover { background-color: #444466; }

.transfer_form {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.transfer_form input {
    padding: 5px;
    border-radius: 5px;
    border: none;
    text-align: center;
}
.back_button {
    position: fixed;
    top: 10px;
    right: 10px;
    background: red;
    color: #fff;
    font-size: 20px;
    border: none;
    border-radius: 6px;
    padding: 5px 10px;
    cursor: pointer;
}
</style>
</head>
<body>

<h1>لوحة الطلبة</h1>

<form method="POST" style="text-align:center; margin-bottom:20px;">
    <input type="search" name="search" placeholder="ابحث عن طالب..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
    <button type="submit">بحث</button>
</form>

<table>
    <tr>
        <th>الرقم</th>
        <th>اسم الطالب</th>
        <th>رقم هاتف ولي الامر</th>
        <th>الصف</th>
        <th>الشعية</th>
        <th>العمر</th>
        <th>الرسوم الكلية قبل الخصومات</th>
        <th>الخصومات</th>
        <th>القسط بعد الخصم</th>
        <th>المتبقي بعد الدفعات</th>
        <th>نوع الاشتراك بالباص</th>
        <th>المدرسة السابقة</th>
        <th>الدفعات</th>
        <th>نقل الطالب</th>
        <th>الصفحة الطالب</th>

        <?php if($role === "admin") echo "<th>تعديل</th>"; ?>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)): 
        $remaining = ($row['fees'] + $row['bus_fees']) - $row['paid'] - $row['discount'];
    ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['student_name'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td><?= $row['class'] ?></td>
            <td><?= $row['section_name'] ?></td>
            <td><?= $row['age'] ?></td>
            <td><?= $row['fees'] + $row['bus_fees'] ?></td>
            <td><?= $row['discount'] ?></td>
            <td><?= $row['fees'] - $row['discount'] + $row['bus_fees'] ?></td>
            <td><?= $remaining ?></td>
            <td>
            <?php 
            if ($row['bus_type'] === 'full') {
                echo "كلي";
            } elseif ($row['bus_type'] === 'partial') {
                echo "جزئي";
            } else {
                echo "غير مشترك";
            }
            ?>
            </td>
            <td><?= $row['previous_school'] ?></td>
            <td>
                <button onclick="window.location.href='student_payments.php?id=<?= $row['id'] ?>'" class="btn_pay">الدفعات</button>
            </td>

            <!-- زر نقل الطالب -->
            <td>
                <form method="POST" action="transfer_student.php" class="transfer_form">
                    <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                    <input type="text" name="new_school" placeholder="المدرسة الجديدة" required>
                    <button type="submit" class="btn_pay">نقل</button>
                </form>
            </td>

            <?php if($role){ ?>
                <td>
                    <button class='btn_pay' onclick="window.location.href='change_student_data.php?id=<?= $row['id'] ?>'">تعديل</button>
                </td>
            <?php } ?>
        </tr>
    <?php endwhile; ?>
</table>

<button onclick="window.history.back()" class="back_button">العودة</button>

</body>
</html>
