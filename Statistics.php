<?php
session_start();
require 'DB_CONNECT.php';
$role = $_SESSION['role'] ?? '';
if (!$role) {
    echo "ليس لديك صلاحيات للوصول";
    exit;
}

$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إحصائيات الطلاب</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: "Tahoma", sans-serif; background-color: #1a1525; color: #f0e9ff; padding: 20px; }
h1, h2 { text-align: center; color: #c9a6ff; }
table { width: 80%; margin: 20px auto; border-collapse: collapse; background-color: #2a2139; border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: center; border-bottom: 1px solid #3a2d4f; }
th { background-color: #4c1d95; }
tr:hover { background-color: #5b21b6; }
.container { display: flex; justify-content: space-around; flex-wrap: wrap; }
.chart-container { width: 90%; margin: 40px auto; background-color: #2a2139; padding: 20px; border-radius: 12px; }
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

<h1>إحصائيات الطلاب</h1>

<?php
// إجمالي عدد الطلاب
$totalQuery = "SELECT COUNT(*) AS total FROM students";
$total = mysqli_fetch_assoc(mysqli_query($conn, $totalQuery))['total'];

// الطلاب حسب الصف
$classQuery = "
SELECT cf.class_name, COUNT(s.id) AS count
FROM students s
LEFT JOIN sections sec ON s.section_id = sec.id
LEFT JOIN class_fees cf ON sec.class_id = cf.id
GROUP BY cf.class_name
";
$classResult = mysqli_query($conn, $classQuery);

// الطلاب حسب الشعبة
$sectionQuery = "
SELECT cf.class_name, sec.section_name, COUNT(s.id) AS count
FROM students s
LEFT JOIN sections sec ON s.section_id = sec.id
LEFT JOIN class_fees cf ON sec.class_id = cf.id
GROUP BY cf.class_name, sec.section_name
";
$sectionResult = mysqli_query($conn, $sectionQuery);

// الطلاب حسب الجنس
$genderQuery = "SELECT gender, COUNT(*) as count FROM students GROUP BY gender";
$genderResult = mysqli_query($conn, $genderQuery);
$maleCount = 0; $femaleCount = 0;
while($row = mysqli_fetch_assoc($genderResult)){
    $g = strtolower(trim($row['gender']));
    if($g == 'ذكر' || $g == 'male') $maleCount += $row['count'];
    elseif($g == 'أنثى' || $g == 'female') $femaleCount += $row['count'];
}

// عدد الطلاب المنتقلين
$transferredCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM transferred_students"))['total'];

// عدد الطلاب الجدد لهذا العام
$newStudentsCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students WHERE YEAR(admission_date) = $currentYear"))['total'];
?>

<h2>إجمالي عدد الطلاب: <?php echo $total; ?></h2>
<h2>عدد الطلاب المنتقلين: <?php echo $transferredCount; ?></h2>
<h2>عدد الطلاب الجدد لهذا العام: <?php echo $newStudentsCount; ?></h2>

<h2>عدد الطلاب حسب الصف</h2>
<table>
<tr><th>الصف</th><th>عدد الطلاب</th></tr>
<?php
$classNames = []; $classCounts = [];
while($row = mysqli_fetch_assoc($classResult)){
    echo "<tr><td>".htmlspecialchars($row['class_name'])."</td><td>".$row['count']."</td></tr>";
    $classNames[] = $row['class_name'];
    $classCounts[] = $row['count'];
}
?>
</table>

<h2>عدد الطلاب حسب الشعبة</h2>
<table>
<tr><th>الصف</th><th>الشعبة</th><th>عدد الطلاب</th></tr>
<?php
$sectionLabels = []; $sectionCounts = [];
while($row = mysqli_fetch_assoc($sectionResult)){
    echo "<tr><td>".htmlspecialchars($row['class_name'])."</td><td>".htmlspecialchars($row['section_name'])."</td><td>".$row['count']."</td></tr>";
    $sectionLabels[] = $row['class_name'] . " - " . $row['section_name'];
    $sectionCounts[] = $row['count'];
}
?>
</table>

<h2>عدد الطلاب حسب الجنس</h2>
<div class="container">
    <table><tr><th>الذكور</th><th>عددهم</th></tr><tr><td>الذكور</td><td><?php echo $maleCount; ?></td></tr></table>
    <table><tr><th>الإناث</th><th>عددهم</th></tr><tr><td>الإناث</td><td><?php echo $femaleCount; ?></td></tr></table>
</div>

<!-- الرسوم البيانية -->
<div class="chart-container"><canvas id="classChart"></canvas></div>
<div class="chart-container"><canvas id="sectionChart"></canvas></div>
<div class="chart-container"><canvas id="genderChart"></canvas></div>

<script>
const classChart = new Chart(document.getElementById('classChart'), {
    type: 'bar',
    data: { labels: <?php echo json_encode($classNames); ?>, datasets: [{ label: 'عدد الطلاب في الصفوف', data: <?php echo json_encode($classCounts); ?>, backgroundColor: '#6d28d9' }] },
    options: { responsive: true, plugins:{ legend:{display:false} }, scales:{ x:{ticks:{color:'#fff'}}, y:{ticks:{color:'#fff'}, beginAtZero:true} } }
});
const sectionChart = new Chart(document.getElementById('sectionChart'), {
    type: 'bar',
    data: { labels: <?php echo json_encode($sectionLabels); ?>, datasets: [{ label: 'عدد الطلاب في الشعب', data: <?php echo json_encode($sectionCounts); ?>, backgroundColor: '#9333ea' }] },
    options: { responsive: true, plugins:{ legend:{display:false} }, scales:{ x:{ticks:{color:'#fff'}}, y:{ticks:{color:'#fff'}, beginAtZero:true} } }
});
const genderChart = new Chart(document.getElementById('genderChart'), {
    type: 'pie',
    data: { labels: ['ذكور','إناث'], datasets: [{ label: 'عدد الطلاب حسب الجنس', data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>], backgroundColor: ['#3b82f6','#ec4899'] }] },
    options: { responsive: true }
});
</script>
<button onclick="window.history.back()" class="back_button">العودة</button>

</body>
</html>
