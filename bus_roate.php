<?php
require "DB_CONNECT.php";

// حفظ البيانات عند الضغط على زر الحفظ
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    foreach($_POST['bus_number'] as $id => $bus_number){
        $bus_route = $_POST['bus_route'][$id] ?? '';
        $id = intval($id);
        $bus_number = mysqli_real_escape_string($conn, $bus_number);
        $bus_route = mysqli_real_escape_string($conn, $bus_route);

        mysqli_query($conn, "UPDATE students SET bus_number='$bus_number', bus_route='$bus_route' WHERE id=$id");
    }
    $message = "تم حفظ التعديلات بنجاح ✅";
}

// جلب الطلاب المشتركين بالباص مع اسم الشعبة
$res = mysqli_query($conn, "
    SELECT s.id, s.student_name, s.address, s.bus_number, s.class, s.bus_route, sec.section_name 
    FROM students s
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.bus_service = 1
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>بيانات الباص</title>
<style>
body { font-family: Tahoma, sans-serif; background:#1a1525; color:#f0e9ff; padding:20px; }
table { width:90%; margin:auto; border-collapse:collapse; background:#2a2139; border-radius:8px; }
th, td { padding:12px; text-align:center; border-bottom:1px solid #3a2d4f; }
th { background:#4c1d95; }
tr:hover { background:#5b21b6; }
input { width:80%; padding:6px; border-radius:6px; border:none; background:#3a2d4f; color:#fff; text-align:center; }
button { padding:8px 15px; border:none; border-radius:6px; cursor:pointer; }
#export_pdf { background:#ff4757; color:#fff; position:fixed; top:20px; left:20px; }
#save_btn { background:#2ed573; color:#000; margin-top:10px; display:block; width: 100%; }
.message { text-align:center; color:greenyellow; margin-bottom:10px; }
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
#searchInput { width: 30%; padding: 8px; margin: 10px auto; display: block; border-radius:6px; border:none; background:#3a2d4f; color:#fff; text-align:center; }
</style>
</head>
<body>

<?php if(isset($message)) echo "<p class='message'>$message</p>"; ?>

<button id="export_pdf" onclick="window.open('export_bus_pdf.php','_blank')">تصدير PDF</button>

<h2 style="text-align:center;">بيانات الطلاب المشتركين بالباص</h2>

<input type="text" id="searchInput" placeholder="ابحث بالاسم أو الصف أو الشعبة..." onkeyup="searchTable()">

<form method="POST">
<table id="busTable">
<tr>
    <th>الاسم</th>
    <th>الصف</th>
    <th>الشعبة</th>
    <th>العنوان</th>
    <th>رقم الباص</th>
    <th>الجولة</th>
</tr>

<?php while($row = mysqli_fetch_assoc($res)): ?>
<tr>
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    <td><?= htmlspecialchars($row['class']) ?></td>
    <td><?= htmlspecialchars($row['section_name']) ?></td>
    <td><?= htmlspecialchars($row['address']) ?></td>
    <td><input type="text" name="bus_number[<?= $row['id'] ?>]" value="<?= htmlspecialchars($row['bus_number']) ?>"></td>
    <td><input type="text" name="bus_route[<?= $row['id'] ?>]" value="<?= htmlspecialchars($row['bus_route']) ?>"></td>
</tr>
<?php endwhile; ?>
</table>

<input type="submit" id="save_btn" value="حفظ التعديلات">
</form>

<button onclick="window.history.back()" class="back_button">العودة</button>

<script>
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('busTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        for (let j = 0; j < cells.length; j++) {
            if(cells[j].textContent.toLowerCase().includes(input)){
                found = true;
                break;
            }
        }
        row.style.display = found ? '' : 'none';
    }
}
</script>

</body>
</html>
