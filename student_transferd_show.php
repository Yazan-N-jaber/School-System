<?php
require "DB_CONNECT.php";

// فلترة حسب التاريخ
$where = "";
if(isset($_GET['from_date']) && isset($_GET['to_date']) && $_GET['from_date'] && $_GET['to_date']){
    $from = mysqli_real_escape_string($conn, $_GET['from_date']);
    $to   = mysqli_real_escape_string($conn, $_GET['to_date']);
    $where = "WHERE transfer_date BETWEEN '$from' AND '$to'";
}

// جلب جميع الطلاب المنتقلين مع فلترة التاريخ
$res = mysqli_query($conn, "SELECT * FROM transferred_students $where ORDER BY transfer_date DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الطلاب المنتقلين</title>
<style>
body { font-family: Tahoma, sans-serif; background-color: #1a1525; color: #f0e9ff; padding: 20px; }
h1 { text-align: center; color: #c9a6ff; margin-bottom: 20px; }
table { width: 90%; margin: auto; border-collapse: collapse; background-color: #2a2139; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
th, td { padding: 12px; text-align: center; border-bottom: 1px solid #3a2d4f; }
th { background-color: #4c1d95; }
tr:nth-child(even) { background-color: #3a2d4f; }
tr:nth-child(odd) { background-color: #2a2139; }
tr:hover { background-color: #5b21b6; }
button.edit-btn { background-color: #2c2c3e; color: #fff; padding: 5px 10px; border: none; border-radius: 6px; cursor: pointer; }
button.edit-btn:hover { background-color: #444466; }
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
.filter-form {
    width: 90%;
    margin: auto;
    text-align: center;
    margin-bottom: 20px;
    background: #2a2139;
    padding: 10px;
    border-radius: 8px;
}
input[type="date"] { padding: 6px; border-radius: 6px; border: none; margin: 0 5px; }
input[type="submit"] { padding: 6px 15px; background: green; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
</style>
</head>
<body>

<h1>الطلاب المنتقلين</h1>

<!-- فلترة التاريخ -->
<form method="get" class="filter-form">
    من: <input type="date" name="from_date" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
    إلى: <input type="date" name="to_date" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
    <input type="submit" value="عرض">
</form>

<table>
<tr>
    <th>الاسم</th>
    <th>الجنس</th>
    <th>الصف</th>
    <th>الشعبة</th>
    <th>الرسوم الكلية</th>
    <th>المدرسة السابقة</th>
    <th>المدرسة الجديدة</th>
    <th>تاريخ النقل</th>
    <th>تعديل</th>
</tr>

<?php while($row = mysqli_fetch_assoc($res)): ?>
<tr id="row-<?= $row['student_id'] ?>">
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    <td><?= htmlspecialchars($row['gender']) ?></td>
    <td><?= htmlspecialchars($row['class_name']) ?></td>
    <td><?= htmlspecialchars($row['section_id']) ?></td>
    <td><?= htmlspecialchars($row['total_fees']) ?></td>
    <td><?= htmlspecialchars($row['old_school']) ?></td>
    <td><?= htmlspecialchars($row['new_school']) ?></td>
    <td><?= htmlspecialchars($row['transfer_date']) ?></td>
    <td><button class="edit-btn" onclick="editStudent(<?= $row['student_id'] ?>)">تعديل</button></td>
</tr>
<?php endwhile; ?>

</table>

<script>
function editStudent(id){
    let name = prompt("اسم الطالب:", document.querySelector(`#row-${id} td:nth-child(1)`).innerText);
    if(name === null) return;
    let gender = prompt("الجنس:", document.querySelector(`#row-${id} td:nth-child(2)`).innerText);
    if(gender === null) return;
    let class_name = prompt("الصف:", document.querySelector(`#row-${id} td:nth-child(3)`).innerText);
    if(class_name === null) return;
    let section_id = prompt("الشعبة:", document.querySelector(`#row-${id} td:nth-child(4)`).innerText);
    if(section_id === null) return;
    let total_fees = prompt("الرسوم الكلية:", document.querySelector(`#row-${id} td:nth-child(5)`).innerText);
    if(total_fees === null) return;
    let old_school = prompt("المدرسة السابقة:", document.querySelector(`#row-${id} td:nth-child(6)`).innerText);
    if(old_school === null) return;
    let new_school = prompt("المدرسة الجديدة:", document.querySelector(`#row-${id} td:nth-child(7)`).innerText);
    if(new_school === null) return;

    fetch('update_transferred_student.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id=${id}&student_name=${encodeURIComponent(name)}&gender=${encodeURIComponent(gender)}&class_name=${encodeURIComponent(class_name)}&section_id=${encodeURIComponent(section_id)}&total_fees=${encodeURIComponent(total_fees)}&old_school=${encodeURIComponent(old_school)}&new_school=${encodeURIComponent(new_school)}`
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        location.reload();
    });
}
</script>

<button onclick="window.history.back()" class="back_button">العودة</button>

</body>
</html>
