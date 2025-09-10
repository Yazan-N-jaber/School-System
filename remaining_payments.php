<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';

if (!$role) {
    echo "ليس لديك صلاحيات للوصول لهنا";
    exit;
}

// جلب جميع أولياء الأمور الذين لديهم أبناء عليهم أقساط متبقية
$res = mysqli_query($conn, "
    SELECT par.id AS parent_id, par.father_name, par.phone, par.another_phone
    FROM parents par
    JOIN students s ON s.father_id = par.id
    WHERE (s.fees + s.bus_fees) > (SELECT IFNULL(SUM(p.amount),0) FROM payments p WHERE p.student_id = s.id)
    GROUP BY par.id
");
$parents = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الأقساط المتبقية حسب ولي الأمر</title>
<style>
body {
    background: linear-gradient(135deg,#1e1e2f,#232946);
    color:#fff;
    font-family: Arial, sans-serif;
    padding:20px;
    font-size:18px;
}
h1 {text-align:center; color:greenyellow; margin-bottom:30px;}
.accordion {
    background:#fff;
    cursor:pointer;
    padding:20px;
    width:100%;
    margin:auto;
    border:none;
    text-align:right;
    outline:none;
    font-size:18px;
    border-radius:8px;
    margin-bottom:5px;
    font-weight:bold;
}
.accordion:after {content:'▼'; float:left;}
.accordion.active:after {content:'▲';}
.panel {
    padding:10px 20px;
    display:none;
    background:#1f1f34;
    width:80%;
    margin:auto;
    overflow:hidden;
    border-radius:8px;
    margin-bottom:15px;
}
table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
th, td {border:1px solid #444; padding:8px; text-align:center;}
th {background:#4c1d95; color:#fff;}
td {background:#2c2c44;}
.back_button{
    position:fixed;
    top:10px;
    right:10px;
    background:red;
    color:#fff;
    font-size:20px;
    border:none;
    border-radius:6px;
    padding:5px 10px;
    cursor:pointer;
}
.export_btn{
    position:fixed;
    top:10px;
    left:10px;
    background:green;
    color:#fff;
    font-size:20px;
    border:none;
    border-radius:6px;
    padding:5px 10px;
    cursor:pointer;    
}
#searchInput { width: 30%; padding: 8px; margin: 10px auto; display: block; border-radius:6px; border:none; background:#3a2d4f; color:#fff; text-align:center; }

.student_info {margin-bottom:15px; text-align:right; background:#2a2139; padding:10px; border-radius:6px;}
</style>
</head>
<body>

<h1>الأقساط المتبقية حسب ولي الأمر</h1>



<input type="text" id="searchInput" placeholder="ابحث بالاسم أو الصف أو الشعبة..." onkeyup="searchTable()">

<script>
    function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const acc = document.getElementsByClassName("accordion");

    for (let i = 0; i < acc.length; i++) {
        const panel = acc[i].nextElementSibling;
        const text = acc[i].textContent + " " + panel.textContent;
        if (text.toLowerCase().includes(input)) {
            acc[i].style.display = '';
            panel.style.display = acc[i].classList.contains('active') ? 'block' : 'none';
        } else {
            acc[i].style.display = 'none';
            panel.style.display = 'none';
        }
    }
}

</script>






<?php foreach($parents as $parent): 
    // جلب الأبناء لكل ولي أمر
    $parent_id = $parent['parent_id'];
    $res_children = mysqli_query($conn, "
        SELECT s.*, 
        IFNULL((SELECT SUM(amount) FROM payments p WHERE p.student_id=s.id),0) AS paid
        FROM students s
        WHERE s.father_id=$parent_id AND (s.fees + s.bus_fees) > (SELECT IFNULL(SUM(amount),0) FROM payments p WHERE p.student_id=s.id)
    ");
    $children = mysqli_fetch_all($res_children, MYSQLI_ASSOC);

    // حساب المتبقي الإجمالي للولي الأمر
    $total_remaining = 0;
    foreach($children as $c){
        $total_remaining += ($c['fees'] + $c['bus_fees']) - $c['paid'] - $c['discount'];
    }

    $total_fees = 0;
    foreach($children as $c){
        $total_fees += ($c['fees'] + $c['bus_fees']) - $c['discount'];
    }
?>

<button class="accordion" id="father_table">
    <?= htmlspecialchars($parent['father_name']) ?> - الهاتف: <?= htmlspecialchars($parent['phone'] ?? '-') ?> / <?= htmlspecialchars($parent['another_phone'] ?? '-') ?> - المتبقي: <?= $total_remaining ?> دينار / من اصل <?php echo $total_fees?>
</button>
<div class="panel">
    <?php foreach($children as $child): 
        $child_remaining = ($child['fees'] + $child['bus_fees']) - $child['discount'] - $child['paid']  ;
        // جلب دفعات الطالب
        $res_pay = mysqli_query($conn, "SELECT amount, payment_date, notes FROM payments WHERE student_id={$child['id']} ORDER BY payment_date");
        $payments = [];
        while($p = mysqli_fetch_assoc($res_pay)) $payments[] = $p;
    ?>
    <div class="student_info">
        <strong>اسم الطالب:</strong> <?= htmlspecialchars($child['student_name']) ?> - الصف: <?= htmlspecialchars($child['class']) ?><br>
        <strong>الكلي:</strong> <?= $child['fees'] + $child['bus_fees']?> دينار<br>
        <strong>الخصم:</strong> <?= $child['discount'] ?> دينار<br>
        <strong>المتبقي بعد الخصم:</strong> <?= $child['fees'] + $child['bus_fees'] - $child['discount'] ?> دينار<br>

        <strong>المتبقي بعد الدفعات:</strong> <?= $child_remaining ?> دينار<br>
        <strong>طريقة الخروج:</strong> <?= htmlspecialchars($child['exitway']) ?><br>
    
        <?php if($child['bus_service']==1): ?>
        <strong>اشتراك الباص:</strong> نعم - النوع: <?= $child['bus_type']=='full' ? 'كلي' : ($child['bus_type']=='partial' ? 'جزئي' : '-') ?> - القيمة: <?= $child['bus_fees'] ?> دينار<br>
        <?php else: ?>
        <strong>اشتراك الباص:</strong> لا<br>
        <?php endif; ?>

        <table>
            <tr>
                <th>المبلغ المدفوع</th>
                <th>تاريخ الدفع</th>
                <th>ملاحظات</th>
            </tr>
            <?php foreach($payments as $p): ?>
            <tr>
                <td><?= $p['amount'] ?></td>
                <td><?= $p['payment_date'] ?></td>
                <td><?= htmlspecialchars($p['notes']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<button onclick="window.history.back()" class="back_button">العودة</button>

<script>
// Accordion functionality
const acc = document.getElementsByClassName("accordion");
for(let i=0;i<acc.length;i++){
    acc[i].addEventListener("click", function(){
        this.classList.toggle("active");
        const panel = this.nextElementSibling;
        panel.style.display = (panel.style.display === "block") ? "none" : "block";
    });
}
</script>
<button class="export_btn" onclick="window.print()">طباعة / حفظ PDF</button>
</body>
</html>
