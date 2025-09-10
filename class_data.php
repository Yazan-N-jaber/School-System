<?php
session_start();
require "DB_CONNECT.php";

// جلب كل الصفوف
$classes = [];
$classRes = $conn->query("SELECT DISTINCT class FROM students ORDER BY class");
while($row = $classRes->fetch_assoc()){
    $classes[] = $row['class'];
}

// جلب الشعب لكل صف من جدول sections
$sections = [];
$secRes = $conn->query("
    SELECT DISTINCT s.class, s.section_id, sec.section_name 
    FROM students s
    LEFT JOIN sections sec ON s.section_id = sec.id
    ORDER BY s.class, s.section_id
");
while($row = $secRes->fetch_assoc()){
    $sections[$row['class']][] = $row['section_name'];
}

$selected_class = $_POST['class'] ?? '';
$selected_section = $_POST['section'] ?? '';
$students = [];

if($selected_class && $selected_section){
    $stmt = $conn->prepare("
        SELECT s.student_name, p.phone 
        FROM students s
        LEFT JOIN parents p ON s.father_id = p.id
        WHERE s.class=? AND s.section_id=(
            SELECT id FROM sections WHERE section_name=? LIMIT 1
        )
        ORDER BY s.student_name
    ");
    $stmt->bind_param("ss", $selected_class, $selected_section);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>بيانات الصف والشعبة</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background:#1e1e2f; color:#fff; }
select, button { padding: 8px 12px; margin:5px; border-radius:5px; border:none; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #444; padding:8px; text-align:center; }
th { background:#2c2a4a; }
button.print { background:#4cd137; color:#fff; cursor:pointer; }
.loading { display: none; color: #4cd137; margin: 10px; }
.back_button {
    position: fixed;
    top: 10px;
    left: 10px;
    background: red;
    color: #fff;
    font-size: 20px;
    border: none;
    border-radius: 6px;
    padding: 5px 10px;
    cursor: pointer;
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script>
function bufferToBase64(buffer) {
    let binary = '';
    const bytes = new Uint8Array(buffer);
    const len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary);
}

function generatePDF() {
    document.getElementById('loading').style.display = 'block';
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // تحميل الخط من ملف TTF
    fetch('fonts/Amiri-Regular.ttf')
        .then(res => res.arrayBuffer())
        .then(buffer => {
            doc.addFileToVFS("Amiri-Regular.ttf", bufferToBase64(buffer));
            doc.addFont("Amiri-Regular.ttf", "Amiri", "normal");
            doc.setFont("Amiri");

            // عنوان التقرير
            doc.setFontSize(18);
            doc.text("قائمة الطلاب - الصف <?= $selected_class ?> - الشعبة <?= $selected_section ?>", 105, 15, { align: 'center' });

            // جمع بيانات الجدول
            const table = document.getElementById("students_table");
            const rows = table.rows;
            const data = [];
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                data.push([
                    row.cells[0].innerText,
                    row.cells[1].innerText
                ]);
            }

            // إعداد جدول PDF
            const tableOptions = {
                head: [['اسم الطالب', 'رقم الهاتف']],
                body: data,
                startY: 25,
                styles: { font: 'Amiri', fontStyle: 'normal', halign: 'right', direction: 'rtl' },
                headStyles: { fillColor: [44, 42, 74], halign: 'right' },
                columnStyles: {
                    0: { cellWidth: 'wrap', halign: 'right' },
                    1: { cellWidth: 'wrap', halign: 'right' }
                },
                margin: { right: 10, left: 10 },
                theme: 'grid'
            };
            doc.autoTable(tableOptions);

            // إضافة التاريخ
            const date = new Date().toLocaleDateString('ar-EG');
            doc.setFontSize(10);
            doc.text('تم الإنشاء في: ' + date, 105, doc.lastAutoTable.finalY + 10, { align: 'center' });

            // حفظ الملف
            setTimeout(function() {
                doc.save('قائمة_الطلاب_<?= $selected_class ?>_<?= $selected_section ?>.pdf');
                document.getElementById('loading').style.display = 'none';
            }, 500);
        });
}
</script>
</head>
<body>

<h2>اختيار الصف والشعبة</h2>
<form method="post">
    <label>الصف:</label>
    <select name="class" onchange="this.form.submit()">
        <option value="">اختر الصف</option>
        <?php foreach($classes as $c): ?>
            <option value="<?= $c ?>" <?= ($c==$selected_class)?'selected':'' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>

    <?php if($selected_class && isset($sections[$selected_class])): ?>
    <label>الشعبة:</label>
    <select name="section" onchange="this.form.submit()">
        <option value="">اختر الشعبة</option>
        <?php foreach($sections[$selected_class] as $sec): ?>
            <option value="<?= $sec ?>" <?= ($sec==$selected_section)?'selected':'' ?>><?= $sec ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
</form>

<?php if($selected_class && $selected_section): ?>
    <?php if(count($students) > 0): ?>
        <button class="print" onclick="generatePDF()">تصدير إلى PDF</button>
        <span id="loading" class="loading">جاري إنشاء PDF...</span>
        <table id="students_table">
            <tr>
                <th>اسم الطالب</th>
                <th>رقم الهاتف</th>
            </tr>
            <?php foreach($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['student_name']) ?></td>
                <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>لا يوجد طلاب بهذا الصف والشعبة.</p>
    <?php endif; ?>
<?php endif; ?>
<button onclick="window.history.back()" class="back_button">العودة</button>

</body>
</html>
