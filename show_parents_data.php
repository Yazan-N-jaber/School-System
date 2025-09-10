<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';

if (!$role) {
    echo "ليس لديك صلاحيات للوصول لهنا";
    exit;
}

// جلب جميع أولياء الأمور مع بيانات الأبناء
$res = mysqli_query($conn, "
    SELECT par.id AS parent_id, par.father_name, par.phone, par.another_phone
    FROM parents par
");
$parents = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>أولياء الأمور وأقساط الأبناء</title>
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
    width:95%;
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
.edit_btn{
    background:#44bd32;
    color:#fff;
    padding:5px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.child-panel {margin-bottom:10px;}
</style>
</head>
<body>

<h1>أولياء الأمور وأقساط الأبناء</h1>

<input type="text" id="searchInput" placeholder="ابحث باسم الأب، رقم الهاتف، أو اسم الابن..." 
       style="width:95%; padding:10px; margin:10px auto; display:block; border-radius:6px; border:none; font-size:16px;">

<?php foreach($parents as $parent): 
    $parent_id = $parent['parent_id'];

    // جلب الأبناء لهذا الأب
    $res_children = mysqli_query($conn, "
        SELECT s.*, 
        IFNULL((SELECT SUM(amount) FROM payments p WHERE p.student_id=s.id),0) AS paid
        FROM students s
        WHERE s.father_id=$parent_id
    ");
    $children = mysqli_fetch_all($res_children, MYSQLI_ASSOC);

    $total_fees = 0;
    $total_after_discount = 0;
    $total_remaining = 0;
    foreach($children as $c){
        $total_fees += $c['fees'] + $c['bus_fees'];
        $total_after_discount += ($c['fees'] + $c['bus_fees']) - $c['discount'];
        $total_remaining += ($c['fees'] + $c['bus_fees']) - $c['discount'] - $c['paid'];
    }
?>

<button class="accordion parent-row" 
        data-father="<?= htmlspecialchars($parent['father_name']) ?>" 
        data-phone="<?= htmlspecialchars($parent['phone']) ?>" 
        data-alt="<?= htmlspecialchars($parent['another_phone']) ?>">
    <?= htmlspecialchars($parent['father_name']) ?> - الهاتف: <?= htmlspecialchars($parent['phone']) ?> / <?= htmlspecialchars($parent['another_phone']) ?>
    - عدد الأبناء: <?= count($children) ?> 
    - القسط الكلي: <?= $total_fees ?> دينار
    - بعد الخصم: <?= $total_after_discount ?> دينار
    - المتبقي بعد الدفعات: <?= $total_remaining ?> دينار
    <a href="edit_parent.php?id=<?= $parent_id ?>" class="edit_btn">تعديل بيانات الأب</a>
</button>
<div class="panel">
    <?php foreach($children as $child): 
        $child_remaining = ($child['fees'] + $child['bus_fees']) - $child['discount'] - $child['paid'];
        $res_pay = mysqli_query($conn, "SELECT amount, payment_date, notes FROM payments WHERE student_id={$child['id']} ORDER BY payment_date");
        $payments = [];
        while($p = mysqli_fetch_assoc($res_pay)) $payments[] = $p;
    ?>
    <div class="child-panel">
        <strong>اسم الطالب:</strong> <?= htmlspecialchars($child['student_name']) ?> - الصف: <?= htmlspecialchars($child['class']) ?><br>
        <strong>القسط الكلي:</strong> <?= $child['fees'] + $child['bus_fees']?> دينار - الخصم: <?= $child['discount'] ?> دينار - المتبقي بعد الدفعات: <?= $child_remaining ?> دينار<br>
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
// Accordion
const acc = document.getElementsByClassName("accordion");
for(let i=0;i<acc.length;i++){
    acc[i].addEventListener("click", function(){
        this.classList.toggle("active");
        const panel = this.nextElementSibling;
        panel.style.display = (panel.style.display === "block") ? "none" : "block";
    });
}

// Search
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    const parentRows = document.querySelectorAll('.parent-row');
    
    parentRows.forEach(row => {
        const nextPanel = row.nextElementSibling; 
        const fatherName = row.getAttribute('data-father').toLowerCase();
        const fatherPhone = row.getAttribute('data-phone').toLowerCase();
        const fatherAlt = row.getAttribute('data-alt').toLowerCase();

        let childMatch = false;
        const childDivs = nextPanel.querySelectorAll('.child-panel');
        childDivs.forEach(child => {
            if(child.textContent.toLowerCase().includes(filter)){
                childMatch = true;
            }
        });

        if(fatherName.includes(filter) || fatherPhone.includes(filter) || fatherAlt.includes(filter) || childMatch){
            row.style.display = '';
            // لا تغيّر حالة اللوحة المفتوحة
        } else {
            row.style.display = 'none';
            nextPanel.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
