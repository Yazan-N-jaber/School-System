<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';

if (!$role) {
    echo "ليس لديك صلاحيات الوصول";
    exit;
}

// رسائل النجاح والخطأ
$success = $error = "";

// بحث عن ولي الأمر
$searchResults = [];
if(isset($_POST['searchParent'])){
    $term = $_POST['searchParent'];
    $stmt = $conn->prepare("SELECT * FROM parents WHERE father_name LIKE ? OR phone LIKE ?");
    $likeTerm = "%$term%";
    $stmt->bind_param("ss", $likeTerm, $likeTerm);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()){
        $searchResults[] = $row;
    }
    $stmt->close();
}

// إضافة دفعة
if(isset($_POST['student_id']) && isset($_POST['amount'])){
    $student_id = $_POST['student_id'];
    $amount = floatval($_POST['amount']);
    $notes = $_POST['notes'] ?? '';

    if($amount > 0){
        $stmt = $conn->prepare("INSERT INTO payments (student_id, amount, payment_date, notes) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("ids", $student_id, $amount, $notes);
        if($stmt->execute()){
            $success = "تم تسجيل الدفعة بنجاح ✅";
        } else {
            $error = "حدث خطأ أثناء تسجيل الدفعة: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "المبلغ يجب أن يكون أكبر من صفر";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>صفحة الدفعات</title>
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #1e1e2f, #232946); color: #fff; padding: 30px; }
.container { background: #1f1f34; padding: 30px 40px; border-radius: 15px; width: 800px; margin: auto; box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
h2 { text-align: center; color: #4cd137; margin-bottom: 25px; font-size: 28px; }
form input[type=text], form input[type=number] { width: 100%; padding: 10px; margin: 5px 0 15px 0; border-radius: 8px; border: none; background: #3c3c55; color: #fff; }
form input[type=submit], .payBtn { padding: 12px 20px; border: none; border-radius: 10px; background: #4cd137; color: #fff; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s; }
form input[type=submit]:hover, .payBtn:hover { background: #44bd32; }
.parent-block { border:1px solid #444; padding:15px; border-radius:12px; margin-bottom:15px; background:#2a2a3d; }
.student-block { border:1px solid #555; padding:15px; border-radius:10px; margin:10px 0; background:#3c3c55; }
.total { font-weight:bold; color:#f5f6fa; margin-top:10px; }
.success { color:#4cd137; font-weight:bold; }
.error { color:#e84118; font-weight:bold; }
.back_button{ position: fixed; top: 10px; right: 10px; background: red; color: #fff; font-size: 20px; border:none; border-radius:6px; padding:5px 10px; cursor:pointer; }
.payment-date { font-size: 14px; color:#ddd; margin-top:5px; }
</style>
</head>
<body>
<div class="container">
<h2>صفحة الدفعات</h2>

<?php
if($success) echo "<p class='success'>$success</p>";
if($error) echo "<p class='error'>$error</p>";
?>

<!-- بحث ولي الأمر -->
<form method="post">
    <input type="text" name="searchParent" placeholder="أدخل اسم ولي الأمر أو رقم الهاتف" required>
    <input type="submit" value="بحث">
</form>

<?php if(!empty($searchResults)): ?>
    <?php foreach($searchResults as $parent): ?>
        <div class="parent-block">
            <strong>ولي الأمر:</strong> <?= $parent['father_name'] ?> | <?= $parent['phone'] ?>
            <?php
            $res = $conn->query("SELECT * FROM students WHERE father_id=".$parent['id']);
            while($stu = $res->fetch_assoc()):
                // حساب الرسوم
                $total = $stu['fees'] + ($stu['bus_service'] ? $stu['bus_fees'] : 0);
                $discount = $stu['discount'] ?? 0;
                $total_after_discount = $total - $discount;

                // المدفوعات
                $payRes = $conn->query("SELECT SUM(amount) as paid, MAX(payment_date) as last_date FROM payments WHERE student_id=".$stu['id']);
                $payData = $payRes->fetch_assoc();
                $paid = $payData['paid'] ?? 0;
                $lastDate = $payData['last_date'] ?? '';

                $remaining = $total_after_discount - $paid;
            ?>
                <div class="student-block">
                    <strong><?= $stu['student_name'] ?></strong><br>
                    الرسوم الكلية: <?= $total ?> |
                    الخصم: <?= $discount ?> |
                    بعد الخصم: <?= $total_after_discount ?> |
                    المدفوع: <?= $paid ?> |
                    المتبقي: <?= $remaining ?>

                    <?php if($lastDate): ?>
                        <div class="payment-date">آخر دفعة بتاريخ: <?= date('Y-m-d', strtotime($lastDate)) ?></div>
                    <?php endif; ?>

                    <form method="post" style="margin-top:10px;">
                        <input type="hidden" name="student_id" value="<?= $stu['id'] ?>">
                        <input type="number" name="amount" placeholder="المبلغ" max="<?= $remaining ?>" required>
                        <input type="text" name="notes" placeholder="ملاحظات">
                        <input type="submit" value="سجل الدفعة" class="payBtn">
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>
<button onclick="window.history.back()" class="back_button">العودة</button>
</body>
</html>
