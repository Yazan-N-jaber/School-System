<?php
session_start();
$role = $_SESSION['role'] ?? '';
require "DB_CONNECT.php";

if (!$role) {
    echo "ليس لديك صلاحيات للوصول لهذه الصفحة";
    exit;
}

// حذف شعبة
if(isset($_GET['delete_section'])) {
    $section_id = intval($_GET['delete_section']);

    // تحقق إذا يوجد طلاب بالشعبة
    $check = $conn->prepare("SELECT COUNT(*) FROM students WHERE section_id=?");
    $check->bind_param("i", $section_id);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if($count > 0){
        $message = "لا يمكن حذف الشعبة لأنها تحتوي على طلاب.";
    } else {
        $del = $conn->prepare("DELETE FROM sections WHERE id=?");
        $del->bind_param("i", $section_id);
        $del->execute();
        $del->close();
        $message = "تم حذف الشعبة بنجاح ✅";
    }
}

// تحديث شعب موجودة + إضافة شعب جديدة
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تعديل شعب
    if(isset($_POST['sections'])) {
        foreach($_POST['sections'] as $sec_id => $sec) {
            $max = intval($sec['max_students'] ?? 0);
            $status = ($sec['status'] ?? '') === 'open' ? 'open' : 'closed';
            $stmt = $conn->prepare("UPDATE sections SET max_students=?, status=? WHERE id=?");
            $stmt->bind_param("isi", $max, $status, $sec_id);
            $stmt->execute();
        }
    }

    // إضافة شعب جديدة
    if(isset($_POST['new_sections'])) {
        foreach($_POST['new_sections'] as $class_id => $sec) {
            $name = $sec['section_name'] ?? '';
            $max = intval($sec['max_students'] ?? 0);
            if(empty($name) || $max <= 0) continue;
            $stmt = $conn->prepare("INSERT INTO sections (class_id, section_name, max_students, status) VALUES (?, ?, ?, 'open')");
            $stmt->bind_param("isi", $class_id, $name, $max);
            $stmt->execute();
        }
    }

    $message = "<p style='color:green'>تم حفظ التغييرات بنجاح ✅</p>";
}

// جلب كل الصفوف
$classes = $conn->query("SELECT * FROM class_fees ORDER BY id");

// جلب كل الشعب وعدد الطلاب الحالي
$sections = [];
$secRes = $conn->query("
    SELECT sec.*, cf.class_name, COUNT(st.id) AS current_students
    FROM sections sec
    LEFT JOIN students st ON st.section_id = sec.id
    LEFT JOIN class_fees cf ON cf.id = sec.class_id
    GROUP BY sec.id
    ORDER BY cf.id, sec.section_name
");
while($row = $secRes->fetch_assoc()){
    $sections[$row['class_id']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة الشعب</title>
<style>
body { font-family: Arial, sans-serif; background: #1e1e2f; color: #fff; padding: 20px; }
h2 { color: #4cd137; }
table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
th, td { border: 1px solid #444; padding: 8px; text-align: center; }
th { background: #2c2a4a; }
input, select { padding: 5px; border-radius: 5px; border: none; }
button, input[type="submit"] { padding: 8px 15px; border:none; border-radius:6px; cursor:pointer; }
.save { background:#44bd32; color:#fff; }
.delete-btn { background:#e84118; color:#fff; }
.delete-btn:hover { background:#c23616; }
.back_button { background:red; color:#fff; font-size:18px; padding:5px 10px; margin-bottom:20px; display:inline-block; text-decoration:none;}
.message { font-weight:bold; margin-bottom:10px; color:#f5f6fa; }
</style>
<script>
function confirmDelete(sectionId){
    if(confirm("هل أنت متأكد أنك تريد حذف هذه الشعبة؟")){
        window.location.href = "?delete_section=" + sectionId;
    }
}
</script>
</head>
<body>

<a href="javascript:history.back()" class="back_button">العودة</a>
<h2>إدارة الشعب لكل الصفوف</h2>
<?php if(isset($message)) echo "<div class='message'>$message</div>"; ?>

<form method="post">
<?php while($class = $classes->fetch_assoc()): ?>
    <h3><?= htmlspecialchars($class['class_name']) ?></h3>

    <?php if(isset($sections[$class['id']])): ?>
        <table>
            <tr>
                <th>الصف</th>
                <th>اسم الشعبة</th>
                <th>عدد الطلاب الحالي</th>
                <th>الحد الأقصى للطلاب</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
            <?php foreach($sections[$class['id']] as $sec): ?>
                <tr>
                    <td><?= htmlspecialchars($class['class_name']) ?></td>
                    <td><?= htmlspecialchars($sec['section_name']) ?></td>
                    <td><?= $sec['current_students'] ?></td>
                    <td><input type="number" name="sections[<?= $sec['id'] ?>][max_students]" value="<?= $sec['max_students'] ?>"></td>
                    <td>
                        <select name="sections[<?= $sec['id'] ?>][status]">
                            <option value="open" <?= $sec['status']=='open'?'selected':'' ?>>مفتوحة</option>
                            <option value="closed" <?= $sec['status']=='closed'?'selected':'' ?>>مقفلة</option>
                        </select>
                    </td>
                    <td><button type="button" class="delete-btn" onclick="confirmDelete(<?= $sec['id'] ?>)">حذف الشعبة</button></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>لا توجد شعب لهذا الصف بعد.</p>
    <?php endif; ?>

    <h4>إضافة شعبة جديدة لهذا الصف</h4>
    <input type="text" name="new_sections[<?= $class['id'] ?>][section_name]" placeholder="اسم الشعبة">
    <input type="number" name="new_sections[<?= $class['id'] ?>][max_students]" placeholder="الحد الأقصى للطلاب">
    <hr>
<?php endwhile; ?>

<input type="submit" value="حفظ التغييرات" class="save">
</form>

</body>
</html>
