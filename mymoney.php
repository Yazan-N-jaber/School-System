<?php
session_start();
require "DB_CONNECT.php";

$role = $_SESSION['role'] ?? '';
if (!$role) {
    echo "ليس لديك صلاحيات للوصول لهنا";
    exit;
}

$from_date = $_POST['from_date'] ?? '';
$to_date = $_POST['to_date'] ?? '';

$payments = [];
if($from_date && $to_date){
    $query = "SELECT payments.id, payments.amount, payments.payment_date, payments.notes, students.student_name, students.id as student_id
              FROM payments
              JOIN students ON payments.student_id = students.id
              WHERE payments.payment_date BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
    $result = mysqli_query($conn, $query);
    $payments = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>دفعات الطلاب حسب التاريخ</title>
<style>
body {
    background: linear-gradient(135deg, #1e1e2f, #232946);
    font-family: Arial, sans-serif;
    color: #fff;
    padding: 20px;
    text-align: center;
}
h2 { color: #a569e1; margin-bottom: 20px; }
input[type="date"], input[type="submit"] {
    padding: 8px 12px;
    margin: 5px;
    border-radius: 6px;
    border: none;
    background: #3c2c5f;
    color: #fff;
    font-size: 16px;
}
input[type="submit"] { background: #7d3cff; cursor: pointer; }
input[type="submit"]:hover { background: #9b59ff; }
table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background: #2c2a4a;
    border-radius: 10px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #444;
    text-align: center;
}
th { background: #3c2c5f; color: #a569e1; }
tr:hover { background: #3a2b6a; }
.back_button{
    display: block;
    margin: 20px auto;
    background: #ff4c4c;
    color: #fff;
    font-size: 18px;
    border:none; 
    border-radius:6px; 
    padding:8px 16px;
    cursor:pointer;
}
.back_button:hover { background: #ff6666; }
</style>
</head>
<body>

<h2>دفعات الطلاب حسب التاريخ</h2>

<form method="post">
    من: <input type="date" name="from_date" required value="<?php echo htmlspecialchars($from_date); ?>">
    إلى: <input type="date" name="to_date" required value="<?php echo htmlspecialchars($to_date); ?>">
    <input type="submit" value="عرض الدفعات">
</form>

<?php if(!empty($payments)): ?>
    <?php
    $totalAmount = 0;
    foreach($payments as $p){
    $totalAmount += $p['amount'];
    }
    
    ?>
    <p style="color:#4cd137; font-weight:bold; font-size:18px;">
    مجموع الدفعات في هذه الفترة: <?php echo $totalAmount; ?>
    </p>
    <table>
    <tr>
        <th>رقم الطالب</th>
        <th>اسم الطالب</th>
        <th>المبلغ</th>
        <th>تاريخ الدفع</th>
        <th>ملاحظات</th>
    </tr>
    <?php foreach($payments as $p): ?>
    <tr>
        <td><?php echo $p['student_id']; ?></td>
        <td><?php echo $p['student_name']; ?></td>
        <td><?php echo $p['amount']; ?></td>
        <td><?php echo $p['payment_date']; ?></td>
        <td><?php echo $p['notes']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php elseif($from_date && $to_date): ?>
<p style="color:#f5f6fa;">لا توجد دفعات في هذه الفترة</p>
<?php endif; ?>

<button onclick="window.history.back()" class="back_button">العودة</button>

</body>
</html>
