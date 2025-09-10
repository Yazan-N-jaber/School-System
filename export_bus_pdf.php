<?php
session_start();
require "DB_CONNECT.php";

// جلب كل الصفوف الموجودة للطلاب المشتركين بالباص
$classRes = mysqli_query($conn, "SELECT DISTINCT class FROM students WHERE bus_service=1");
$classes = [];
while($row = mysqli_fetch_assoc($classRes)){
    $classes[] = $row['class'];
}

// الصف والشعبة المحددين
$selectedClass = $_GET['class'] ?? '';
$selectedSection = $_GET['section'] ?? '';

// جلب الشعب المتاحة للصف المحدد
$sections = [];
if($selectedClass){
    $secRes = mysqli_query($conn, "SELECT * FROM sections s 
        JOIN class_fees c ON s.class_id = c.id 
        WHERE c.class_name='".mysqli_real_escape_string($conn, $selectedClass)."'");
    while($row = mysqli_fetch_assoc($secRes)){
        $sections[] = $row;
    }
}

// جلب الطلاب المشتركين بالباص مع فلتر الصف والشعبة
$query = "SELECT student_name, address, class, section_id, bus_number, bus_route 
          FROM students 
          WHERE bus_service=1";

if($selectedClass){
    $query .= " AND class='".mysqli_real_escape_string($conn, $selectedClass)."'";
}
if($selectedSection){
    $query .= " AND section_id=".intval($selectedSection);
}

$res = mysqli_query($conn, $query);

// جلب اسم الشعبة لكل student
$students = [];
while($row = mysqli_fetch_assoc($res)){
    $section_name = '';
    if($row['section_id']){
        $sec = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE id=".$row['section_id']));
        $section_name = $sec['section_name'] ?? '';
    }
    $row['section_name'] = $section_name;
    $students[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تقرير الباص</title>
<style>
@font-face { font-family: 'Amiri'; src: url('Amiri-Regular.ttf') format('truetype'); }
body { font-family: 'Amiri', serif; background:#fff; color:#000; }
h1 { text-align:center; margin-bottom:20px; }
table { width:100%; border-collapse: collapse; margin-bottom:20px; }
th, td { border:1px solid #000; padding:8px; text-align:center; }
th { background:#f0f0f0; }
button, select { padding:10px 20px; margin:10px; cursor:pointer; }
input[type="text"] { width:100%; }
form { text-align:center; margin-bottom:20px; }
</style>
</head>
<body>

<h1>تقرير الطلاب المشتركين في خدمة الباص</h1>


<form method="GET">
    <label>اختر الصف: </label>
    <select name="class" id="class_select" onchange="this.form.submit()">
        <option value="">جميع الصفوف</option>
        <?php foreach($classes as $cls): ?>
            <option value="<?= htmlspecialchars($cls) ?>" <?= $selectedClass==$cls?'selected':'' ?>><?= htmlspecialchars($cls) ?></option>
        <?php endforeach; ?>
    </select>



<script>
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.querySelectorAll('td');
        let found = false;

        cells.forEach(cell => {
            let text = cell.textContent.toLowerCase();
            // إذا فيه input جوة الخلية خذ قيمته
            const inputField = cell.querySelector('input');
            if (inputField) {
                text += inputField.value.toLowerCase();
            }

            if (text.includes(input)) {
                found = true;
            }
        });

        row.style.display = found ? '' : 'none';
    }
}
</script>

<input type="text" id="searchInput" placeholder="اختر الشعبة" onkeyup="searchTable()">



    
    
    <noscript><input type="submit" value="فلتر"></noscript>
</form>

<button onclick="window.print()">طباعة / حفظ PDF</button>

<table>
<tr>
    <th>الاسم</th>
    <th>العنوان</th>
    <th>الصف</th>
    <th>الشعبة</th>
    <th>رقم الباص</th>
    <th>الجولة</th>
</tr>

<?php foreach($students as $s): ?>
<tr>
    <td><?= htmlspecialchars($s['student_name']) ?></td>
    <td><?= htmlspecialchars($s['address']) ?></td>
    <td><?= htmlspecialchars($s['class']) ?></td>
    <td><?= htmlspecialchars($s['section_name']) ?></td>
    <td><input type="text" value="<?= htmlspecialchars($s['bus_number']) ?>" readonly></td>
    <td><input type="text" value="<?= htmlspecialchars($s['bus_route']) ?>" readonly></td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
