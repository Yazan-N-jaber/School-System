<?php
session_start();
require "DB_CONNECT.php";
$role = $_SESSION['role'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!$role){
    echo "ليس لديك صلاحيات للوصول لهنا";
    exit;
}

// جلب بيانات الطالب
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = $id"));
if (!$student) {
    echo "الطالب غير موجود";
    exit;
}

// جلب رسوم الصفوف
$classFees = [];
$res = mysqli_query($conn, "SELECT id, class_name, fee FROM class_fees");
while($row = mysqli_fetch_assoc($res)){
    $classFees[$row['class_name']] = ['id'=>$row['id'], 'fee'=>$row['fee']];
}

// حفظ البيانات عند الضغط على الزر
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = $_POST['student_name'];
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $discount = floatval($_POST['discount']);
    $bus_service = isset($_POST['bus_service']) ? 1 : 0;
    $bus_type = $_POST['bus_type'] ?? '';
    $bus_fees = $bus_service ? floatval($_POST['bus_fees']) : 0;
    $class = $_POST['class'];
    $fees = $classFees[$class]['fee'] ?? 0;
    $class_id = $classFees[$class]['id'] ?? 0;
    $section_id = intval($_POST['section_id'] ?? 0);
    $previous_school = $_POST['previous_school'] ?? '';
    $national_id = $_POST['national_id'] ?? '';
    $religion = $_POST['religion'] ?? '';
    if ($bus_service == 1) {
    $exitway = 'باص';  // إذا مشترك بالباص غصبًا عنه الخروج "باص"
    } else {
    $exitway = $_POST['exitway'] ?? ''; // إذا مش بالباص، بتاخذ القيمة من الفورم
    }
    

    $update = "UPDATE students SET 
        student_name='$student_name',
        age=$age,
        gender='$gender',
        address='$address',
        discount=$discount,
        bus_service=$bus_service,
        bus_type='$bus_type',
        bus_fees=$bus_fees,
        class='$class',
        fees=$fees,
        section_id=$section_id,
        previous_school='$previous_school',
        national_id = '$national_id',
        religion = '$religion',
        exitway = '$exitway'
        WHERE id=$id";

    mysqli_query($conn, $update);
    $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id = $id"));
    $message = "تم حفظ التعديلات بنجاح ✅";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>تعديل بيانات الطالب</title>
<style>
body {background: linear-gradient(135deg, #1e1e2f, #232946); font-family: Arial, sans-serif; color: #fff; padding:20px;}
.container {max-width:700px; margin:auto;}
table {width:100%; background:#1f1f34; border-radius:10px; padding:20px;}
td {padding:10px;}
input, select {width:100%; padding:8px; border-radius:6px; border:none; background:#2c2c44; color:#fff;}
input[type="submit"] {background:#4cd137; padding:10px; border:none; border-radius:8px; color:#fff; cursor:pointer;}
input[type="submit"]:hover {background:#44bd32;}
.back_button {position:fixed; top:10px; right:10px; background:red; color:#fff; padding:5px 10px; font-size:20px; border:none; border-radius:6px; cursor:pointer;}
</style>
</head>
<body>

<h2>تعديل بيانات الطالب: <?= htmlspecialchars($student['student_name']) ?></h2>
<?php if(isset($message)) echo "<p style='text-align:center; color:greenyellow;'>$message</p>"; ?>

<div class="container">
<form method="POST">
<table>
<tr><td>الاسم</td><td><input type="text" name="student_name" value="<?= htmlspecialchars($student['student_name']) ?>"></td></tr>

<tr>
    <td>الصف</td>
    <td>
        <select name="class" id="class_select">
            <option value="">-- اختر الصف --</option>
            <?php foreach($classFees as $cls=>$data): ?>
                <option value="<?= $cls ?>" <?= $student['class']==$cls?'selected':'' ?>><?= $cls ?></option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>

<tr>
    <td>الشعبة</td>
    <td>
        <select name="section_id" id="section_select">
            <option value="">-- اختر الشعبة --</option>
            <?php
            if(isset($student['section_id']) && $student['section_id']){
                $sec = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE id=".$student['section_id']));
                echo "<option value='".$student['section_id']."' selected>".htmlspecialchars($sec['section_name'])."</option>";
            }
            ?>
        </select>
    </td>
</tr>

<tr><td>العمر</td><td><input type="number" name="age" value="<?= $student['age'] ?>"></td></tr>
<tr><td>الجنس</td>
<td>
<select name="gender">
<option value="male" <?= $student['gender']=='male'?'selected':'' ?>>ذكر</option>
<option value="female" <?= $student['gender']=='female'?'selected':'' ?>>أنثى</option>
</select>
</td></tr>

<tr><td>العنوان</td><td><input type="text" name="address" value="<?= htmlspecialchars($student['address']) ?>"></td></tr>
<tr><td>الرسوم الدراسية</td><td><input type="number" id="fees" value="<?= $student['fees'] ?>" readonly></td></tr>
<tr><td>الخصم</td><td><input type="number" name="discount" value="<?= $student['discount'] ?>"></td></tr>

<tr>
<td>اشتراك الباص</td>
<td><input type="checkbox" name="bus_service" id="bus_service" <?= $student['bus_service']==1?'checked':'' ?>></td>
</tr>
<tr id="bus_type_row" style="display: <?= $student['bus_service']==1?'table-row':'none' ?>;">
<td>نوع الاشتراك بالباص</td>
<td>
<select name="bus_type">
<option value="">-- اختر النوع --</option>
<option value="full" <?= $student['bus_type']=='full'?'selected':'' ?>>كلي</option>
<option value="partial" <?= $student['bus_type']=='partial'?'selected':'' ?>>جزئي</option>
</select>
</td>
</tr>
<tr id="bus_fees_row" style="display: <?= $student['bus_service']==1?'table-row':'none' ?>;">
<td>رسوم الباص</td><td><input type="number" name="bus_fees" value="<?= $student['bus_fees'] ?>"></td>
</tr>
<tr><td>الرقم الوطني</td><td><input type="number" name="national_id" value="<?= htmlspecialchars($student['national_id']) ?>"></td></tr>
<tr><td>الديانة</td><td><input type="text" name="religion" value="<?= htmlspecialchars($student['religion']) ?>"></td></tr>


<?php
        if ($student['bus_service'] == 1){
            $student['exitway'] = 'باص';
        }

?>

<!-- طريقة الخروج -->
<tr<?= htmlspecialchars ($student['exitway']) ?>;>
<td>طريقة الخروج </td>
<td>
<select name="exitway">
<option value="">-- اختر النوع --</option>
<option value="باص" <?= $student['exitway']=='باص'?'selected':'' ?>>باص</option>
<option value="مشي" <?= $student['exitway']=='مشي'?'selected':'' ?>>مشي</option>
<option value="انتظار" <?= $student['exitway']=='انتظار'?'selected':'' ?>>انتظار</option>
</select>
</td>
</tr>




<tr><td>المدرسة السابقة</td><td><input type="text" name="previous_school" value="<?= htmlspecialchars($student['previous_school']) ?>"></td></tr>
</table>

<input type="submit" value="حفظ التعديلات">
</form>
</div>

<button onclick="window.history.back()" class="back_button">العودة</button>

<script>
// تحديث الرسوم عند تغيير الصف
const classFees = <?= json_encode(array_map(function($d){return $d['fee'];}, $classFees)); ?>;
const classSelect = document.getElementById('class_select');
const feesInput = document.getElementById('fees');
const busCheckbox = document.getElementById('bus_service');
const busTypeRow = document.getElementById('bus_type_row');
const busFeesRow = document.getElementById('bus_fees_row');
const sectionSelect = document.getElementById('section_select');

classSelect.addEventListener('change', ()=>{
    const cls = classSelect.value;
    feesInput.value = classFees[cls] ?? 0;

    // جلب الشعب عبر AJAX
    if(cls==='') {
        sectionSelect.innerHTML = '<option value="">-- اختر الشعبة --</option>';
        return;
    }
    fetch('get_sections.php?class_name=' + encodeURIComponent(cls))
        .then(res=>res.json())
        .then(data=>{
            sectionSelect.innerHTML = '<option value="">-- اختر الشعبة --</option>';
            data.forEach(sec=>{
                const opt = document.createElement('option');
                opt.value = sec.id;
                opt.textContent = sec.section_name;
                sectionSelect.appendChild(opt);
            });
        });
});

// إظهار/إخفاء الباص
busCheckbox.addEventListener('change', ()=>{
    const show = busCheckbox.checked;
    busTypeRow.style.display = show?'table-row':'none';
    busFeesRow.style.display = show?'table-row':'none';
});
</script>

</body>
</html>
