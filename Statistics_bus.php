<?php
require "DB_CONNECT.php";

// 1- إحصائية حسب exitway
$exitwayStats = [];
$res = mysqli_query($conn, "SELECT exitway, COUNT(*) as total FROM students GROUP BY exitway");
while($row = mysqli_fetch_assoc($res)){
    $exitwayStats[$row['exitway']] = $row['total'];
}

// 2- إحصائية حسب رقم الباص
$busStats = [];
$res = mysqli_query($conn, "SELECT bus_number, COUNT(*) as total FROM students WHERE bus_service=1 GROUP BY bus_number");
while($row = mysqli_fetch_assoc($res)){
    $busStats[] = $row;
}

// 3- إحصائية حسب الجولة
$routeStats = [];
$res = mysqli_query($conn, "SELECT bus_route, COUNT(*) as total FROM students WHERE bus_service=1 GROUP BY bus_route");
while($row = mysqli_fetch_assoc($res)){
    $routeStats[] = $row;
}

// 4- إحصائية حسب الباص + الجولة
$busRouteStats = [];
$res = mysqli_query($conn, "SELECT bus_number, bus_route, COUNT(*) as total 
                            FROM students 
                            WHERE bus_service=1 
                            GROUP BY bus_number, bus_route
                            ORDER BY bus_number, bus_route");
while($row = mysqli_fetch_assoc($res)){
    $busRouteStats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إحصائيات الباصات</title>
<style>
body { font-family: Tahoma, sans-serif; background:#111827; color:#f9fafb; padding:20px; }
h2 { text-align:center; margin-top:30px; color:#fbbf24; }
table { width:80%; margin:20px auto; border-collapse:collapse; background:#1f2937; border-radius:8px; overflow:hidden; }
th, td { border:1px solid #374151; padding:10px; text-align:center; }
th { background:#4b5563; color:#fff; }
tr:nth-child(even) { background:#111827; }
tr:hover { background:#374151; }
.back_button { position:fixed; top:10px; right:10px; background:red; color:#fff; padding:5px 10px; border:none; border-radius:6px; cursor:pointer; font-size: 25px }
</style>
</head>
<body>

<button onclick="window.history.back()" class="back_button">العودة</button>

<h1 style="text-align:center; color:#34d399;">إحصائيات الطلاب المشتركين بالباص</h1>

<!-- 1- حسب طريقة الخروج -->
<h2>عدد الطلاب حسب طريقة الخروج</h2>
<table>
<tr><th>طريقة الخروج</th><th>العدد</th></tr>
<tr><td>باص</td><td><?= $exitwayStats['باص'] ?? 0 ?></td></tr>
<tr><td>مشي</td><td><?= $exitwayStats['مشي'] ?? 0 ?></td></tr>
<tr><td>انتظار</td><td><?= $exitwayStats['انتظار'] ?? 0 ?></td></tr>
</table>

<!-- 2- حسب رقم الباص -->
<h2>عدد الطلاب في كل باص</h2>
<table>
<tr><th>رقم الباص</th><th>عدد الطلاب</th></tr>
<?php foreach($busStats as $b): ?>
<tr>
    <td><?= htmlspecialchars($b['bus_number']) ?></td>
    <td><?= $b['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- 3- حسب الجولة -->
<h2>عدد الطلاب في كل جولة</h2>
<table>
<tr><th>الجولة</th><th>عدد الطلاب</th></tr>
<?php foreach($routeStats as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['bus_route']) ?></td>
    <td><?= $r['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- 4- حسب الباص + الجولة -->
<h2>عدد الطلاب في كل باص حسب الجولة</h2>
<table>
<tr><th>رقم الباص</th><th>الجولة</th><th>عدد الطلاب</th></tr>
<?php foreach($busRouteStats as $br): ?>
<tr>
    <td><?= htmlspecialchars($br['bus_number']) ?></td>
    <td><?= htmlspecialchars($br['bus_route']) ?></td>
    <td><?= $br['total'] ?></td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
