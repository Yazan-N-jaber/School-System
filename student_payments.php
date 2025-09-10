<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';

if (!$role) {
    echo "ليس لديك صلاحيات للوصول لهنا";
    exit;
}

$id = $_GET['id'] ?? 0;
$getstudent = "SELECT student_name FROM students WHERE id = $id";
$resultgetstudent = mysqli_query($conn , $getstudent);
$student = mysqli_fetch_assoc($resultgetstudent);
?>

<!DOCTYPE html>
<html>
<head>
    <title>دفعات الطالب</title>
    <meta charset="UTF-8">
    <style>
        body {background: linear-gradient(135deg, #1e1e2f, #232946); font-family: Arial, sans-serif; color: #fff;}
        h2 {text-align: center; color: greenyellow; margin-bottom: 20px;}
        table {width: 80%; margin: auto; border-collapse: collapse; background: #1f1f34; border-radius: 10px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.5);}
        th, td {padding: 12px; border-bottom: 1px solid #444; text-align: center;}
        th {background: #2d2d44; color: greenyellow;}
        tr:hover {background: #2c2c3e;}
        .back_button{position: fixed; top: 10px; right: 10px; background: red; color: #fff; font-size: 20px; border:none; border-radius:6px; padding:5px 10px; cursor:pointer;}
        .export_button{position: fixed; top: 10px; left: 10px; background: green; color: #fff; font-size: 20px; border:none; border-radius:6px; padding:5px 10px; cursor:pointer;}
        .btn_action {padding:5px 10px; border:none; border-radius:6px; cursor:pointer; margin:2px;}
        .btn_edit {background-color:#44bd32; color:white;}
        .btn_delete {background-color:#e84118; color:white;}
    </style>
</head>
<body>
<h2>دفعات الطالب: <?= $student['student_name'] ?? 'غير موجود'; ?></h2>

<table id="paymentsTable">
    <tr>
        <th>اسم الطالب</th>
        <th>المبلغ</th>
        <th>تاريخ الدفع</th>
        <th>ملاحظات</th>
        <th>تعديل</th>
        <th>حذف</th>
    </tr>
    <?php
    $getpay = "SELECT payments.id, payments.amount, payments.payment_date, payments.notes, students.student_name
               FROM payments
               JOIN students ON payments.student_id = students.id
               WHERE payments.student_id = $id";
    $resultgetpay = mysqli_query($conn , $getpay);

    while ($row = mysqli_fetch_assoc($resultgetpay)) {
        echo "<tr id='row_{$row['id']}'>";
        echo "<td>" . $row["student_name"] . "</td>";
        echo "<td class='amount'>" . $row["amount"] . "</td>";
        echo "<td class='date'>" . $row["payment_date"] . "</td>";
        echo "<td class='notes'>" . $row["notes"] . "</td>";
        echo "<td><button class='btn_action btn_edit' onclick='editPayment({$row['id']})'>تعديل</button></td>";
        echo "<td><button class='btn_action btn_delete' onclick='deletePayment({$row['id']})'>حذف</button></td>";
        echo "</tr>";
    }
    ?>
</table>

<?php
$getstudent = "SELECT student_name, fees, bus_fees FROM students WHERE id = $id";
$resultgetstudent = mysqli_query($conn , $getstudent);
$student = mysqli_fetch_assoc($resultgetstudent);
$total = $student['fees'] + $student['bus_fees'];
echo "<p style='text-align:center; margin-top:20px; font-size:18px; color:greenyellow;'>القسط الكلي: $total دينار</p>"
?>

<button onclick="window.history.back()" class="back_button">العودة</button>

<script>
// حذف الدفعة
function deletePayment(id){
    if(confirm("هل أنت متأكد من حذف هذه الدفعة؟")){
        fetch('ajax_delete_payment.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id=' + id
        })
        .then(res => res.text())
        .then(data => {
            if(data == 'success'){
                document.getElementById('row_' + id).remove();
            } else {
                alert('حدث خطأ أثناء الحذف');
            }
        });
    }
}

// تعديل الدفعة
function editPayment(id){
    let amount = prompt("أدخل المبلغ الجديد:");
    let notes = prompt("أدخل الملاحظات الجديدة:");
    if(amount !== null && notes !== null){
        fetch('ajax_edit_payment.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&amount=' + encodeURIComponent(amount) + '&notes=' + encodeURIComponent(notes)
        })
        .then(res => res.json())
        .then(data => {
            if(data.status == 'success'){
                let row = document.getElementById('row_' + id);
                row.querySelector('.amount').textContent = data.amount;
                row.querySelector('.notes').textContent = data.notes;
            } else {
                alert('حدث خطأ أثناء التعديل');
            }
        });
    }
}
</script>
<button class="export_button" onclick="window.print()">طباعة / حفظ PDF</button>
</body>
</html>
