<?php
session_start();
require "DB_CONNECT.php";

$role = $_SESSION['role'] ?? '';

if ($role && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // إضافة ولي الأمر
    $father = $_POST['Father'];
    $phone = $_POST['Phone-number'];
    $another_phone = $_POST['another-phone-number'] ?? '';

    // التحقق من التكرار
    $stmt = $conn->prepare("SELECT id FROM parents WHERE father_name=? AND phone=? LIMIT 1");
    $stmt->bind_param("ss", $father, $phone);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($father_id);
        $stmt->fetch();
    } else {
        $stmtInsert = $conn->prepare("INSERT INTO parents (father_name, phone, another_phone) VALUES (?, ?, ?)");
        $stmtInsert->bind_param("sss", $father, $phone, $another_phone);
        $stmtInsert->execute();
        $father_id = $stmtInsert->insert_id;
        $stmtInsert->close();
    }
    $stmt->close();

    // إضافة الطلاب
    if(isset($_POST['students'])) {
        $stmtStudent = $conn->prepare("
            INSERT INTO students 
            (father_id, student_name, age, gender, address, class, section_id, fees, discount, bus_service, bus_fees, notes, bus_type , previous_school)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach($_POST['students'] as $s) {
            $bus_service = isset($s['Bus-service']) ? 1 : 0;
            $bus_type = !empty($s['Bus-type']) ? $s['Bus-type'] : NULL;
            $fullStudentName = $father . " - " . $s['Student-name']; 
            $section_id = $s['Section'];

            $stmtStudent->bind_param(
                "isssssiiddisss",
                $father_id,
                $fullStudentName,
                $s['Student-age'],
                $s['Student-gender'],
                $s['Address'],
                $s['Class'],
                $section_id,
                $s['Fees'],
                $s['Discount'],
                $bus_service,
                $s['busFees'],
                $s['Notes'],
                $bus_type,
                $s['Previous-school']
            );
            $stmtStudent->execute();
        }
        $stmtStudent->close();
        $message = "<p style='color:#4cd137; font-weight:bold;'>تم إضافة ولي الأمر وجميع الأبناء بنجاح ✅</p>";
    }
}
?>

<?php if($role): ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تسجيل ولي الأمر والأبناء</title>
<style>
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #1e1e2f, #232946); color: #fff; display: flex; justify-content: center; align-items: flex-start; padding: 30px; min-height: 100vh; }
.container { background: #1f1f34ff; padding: 30px 40px; border-radius: 15px; width: 800px; box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
h2 { text-align: center; color: #4cd137; margin-bottom: 25px; font-size: 28px; }
form label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold; }
input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: none; outline: none; background: #3c3c55; color: #fff; font-size: 15px; transition: 0.3s; }
input:focus, select:focus, textarea:focus { background: #474768; }
input[type="checkbox"] { width: auto; margin-left: 5px; cursor: pointer; }
textarea { resize: vertical; min-height: 60px; }
.student-block { border: 1px solid #444; padding: 20px; border-radius: 12px; margin-bottom: 20px; position: relative; background: #1f1f34ff; }
.remove-student { position: absolute; top: 5px; right: 700px; background: #e84118; color: #fff; border: none; border-radius: 6px; cursor: pointer; padding: 5px 10px; font-weight: bold; }
button, input[type="submit"] { padding: 12px 20px; border: none; border-radius: 10px; background: #4cd137; color: #fff; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 15px; transition: 0.3s; }
button:hover, input[type="submit"]:hover { background: #44bd32; }
.total { margin-top: 15px; font-weight: bold; font-size: 18px; color: #f5f6fa; }
.back_button{ position: fixed; top: 1%; background-color: red; font-size: 22px; right: 10px; }
</style>
</head>
<body>
<div class="container">
<h2>تسجيل ولي الأمر والأبناء</h2>
<?php if(isset($message)) echo $message; ?>
<form method="post" id="mainForm">
    <label>اسم ولي الامر</label>
    <input type="text" name="Father" required>
    <label>رقم هاتف ولي الامر</label>
    <input type="number" name="Phone-number" required>
    <label>رقم هاتف آخر</label>
    <input type="number" name="another-phone-number">

    <hr style="border:1px solid #444; margin:25px 0;">
    <div id="studentsContainer"></div>
    <button type="button" id="addStudentBtn">إضافة طالب</button>
    <div class="total">الرسوم الكلية: <span id="grandTotal">0</span></div>
    <input type="submit" value="حفظ جميع البيانات">
</form>
</div>

<script>
// جلب الرسوم لكل صف
let classFees = {};
<?php
$result = $conn->query("SELECT * FROM class_fees");
while($row = $result->fetch_assoc()){
    echo "classFees['".$row['class_name']."'] = ".$row['fee'].";\n";
}
?>

// جلب الشعب لكل صف
let sections = {};
<?php
$secRes = $conn->query("SELECT sec.*, cf.class_name, COUNT(st.id) AS current_students
                        FROM sections sec
                        LEFT JOIN students st ON st.section_id = sec.id
                        LEFT JOIN class_fees cf ON cf.id = sec.class_id
                        GROUP BY sec.id");
while($sec = $secRes->fetch_assoc()){
    echo "if(!sections['".$sec['class_name']."']) sections['".$sec['class_name']."'] = [];\n";
    echo "sections['".$sec['class_name']."'].push({id:".$sec['id'].", name:'".$sec['section_name']."', current:".$sec['current_students'].", max:".$sec['max_students'].", status:'".$sec['status']."'});\n";
}
?>

let studentsContainer = document.getElementById('studentsContainer');
let grandTotalSpan = document.getElementById('grandTotal');

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.student-block').forEach(block => {
        let fees = parseFloat(block.querySelector('.fees').value) || 0;
        let bus = block.querySelector('.busCheck').checked ? (parseFloat(block.querySelector('.busFees').value)||0) : 0;
        let discount = parseFloat(block.querySelector('.discount').value) || 0;
        total += (fees + bus - discount);
    });
    grandTotalSpan.textContent = total;
}

function addStudentBlock() {
    let index = studentsContainer.children.length;
    let div = document.createElement('div');
    div.className = 'student-block';

    div.innerHTML = `
        <button type="button" class="remove-student">X</button>
        <label>اسم الطالب</label>
        <input type="text" name="students[${index}][Student-name]" required>
        <label>المدرسة السابقة</label>
        <input type="text" name="students[${index}][Previous-school]">
        <label>العمر</label>
        <input type="number" name="students[${index}][Student-age]" required>
        <label>الجنس</label>
        <input type="radio" name="students[${index}][Student-gender]" value="male" required> ذكر
        <input type="radio" name="students[${index}][Student-gender]" value="female" required> أنثى
        <label>العنوان</label>
        <input type="text" name="students[${index}][Address]" required>
        <label>الصف الدراسي</label>
        <select name="students[${index}][Class]" class="classSelect" required>
            <option value="">-- اختر الصف --</option>
            <?php
            $result = $conn->query("SELECT * FROM class_fees");
            while($row = $result->fetch_assoc()){
                echo "<option value='".$row['class_name']."'>".$row['class_name']."</option>";
            }
            ?>
        </select>
        <label>الشعبة</label>
        <select name="students[${index}][Section]" class="sectionSelect" required>
            <option value="">-- اختر الشعبة --</option>
        </select>
        <label>الرسوم الدراسية</label>
        <input type="number" name="students[${index}][Fees]" class="fees" readonly>

        <input type="checkbox" class="busCheck" name="students[${index}][Bus-service]"> خدمة الباص
        <div class="bus-options" style="display:none;">
            <label>نوع الاشتراك</label>
            <select name="students[${index}][Bus-type]" class="busType">
                <option value="">-- اختر نوع الاشتراك --</option>
                <option value="full">كلي</option>
                <option value="partial">جزئي</option>
            </select>
            <label>رسوم الباص</label>
            <input type="number" name="students[${index}][busFees]" class="busFees">
        </div>

        <label>الخصم</label>
        <input type="number" name="students[${index}][Discount]" class="discount" value="0">

        <label>ملاحظات</label>
        <textarea name="students[${index}][Notes]"></textarea>
    `;

    studentsContainer.appendChild(div);

    let feesInput = div.querySelector('.fees');
    let busCheck = div.querySelector('.busCheck');
    let busOptions = div.querySelector('.bus-options');
    let busType = div.querySelector('.busType');
    let busFeesInput = div.querySelector('.busFees');
    let classSelect = div.querySelector('.classSelect');
    let discountInput = div.querySelector('.discount');
    let sectionSelect = div.querySelector('.sectionSelect');

    classSelect.addEventListener('change', () => {
        feesInput.value = classFees[classSelect.value] || 0;
        sectionSelect.innerHTML = '<option value="">-- اختر الشعبة --</option>';
        if(sections[classSelect.value]){
            sections[classSelect.value].forEach(sec => {
                if(sec.status === 'open' && sec.current < sec.max){
                    let opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = `${sec.name} (${sec.current}/${sec.max})`;
                    sectionSelect.appendChild(opt);
                }
            });
        }
        calculateGrandTotal();
    });

    busCheck.addEventListener('change', () => {
        busOptions.style.display = busCheck.checked ? 'block' : 'none';
        calculateGrandTotal();
    });

    busType.addEventListener('change', () => {
        if (busType.value === 'full') {
            busFeesInput.value = 100;
            busFeesInput.readOnly = false;
            busFeesInput.disabled = false;
        } else if (busType.value === 'partial') {
            busFeesInput.value = 0;
            busFeesInput.readOnly = false;
            busFeesInput.disabled = false;
        } else {
            busFeesInput.value = 0;
            busFeesInput.readOnly = true;
            busFeesInput.disabled = true;
        }
        calculateGrandTotal();
    });

    busFeesInput.addEventListener('input', calculateGrandTotal);
    discountInput.addEventListener('input', calculateGrandTotal);

    div.querySelector('.remove-student').addEventListener('click', () => {
        div.remove();
        calculateGrandTotal();
    });
}

document.getElementById('addStudentBtn').addEventListener('click', addStudentBlock);
</script>

<button onclick="window.history.back()" class="back_button">العودة</button>
</body>
</html>
<?php else: ?>
<p>ليس لديك صلاحيات للوصول لهذه الصفحة.</p>
<?php endif; ?>
