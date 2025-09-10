<?php
session_start();
require "DB_CONNECT.php";

$role = $_SESSION['role'] ?? '';
$message = '';

if($role && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $father_id = $_POST['father_id'] ?? null;

    if($father_id && isset($_POST['students'])){
        $stmtStudent = $conn->prepare("
            INSERT INTO students 
            (father_id, student_name, age, gender, address, class, section_id, fees, discount, bus_service, bus_fees, bus_type, previous_school)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        foreach($_POST['students'] as $s){
            $student_name = $s['Student-name'] ?? '';
            $age = $s['Student-age'] ?? 0;
            $gender = $s['Student-gender'] ?? '';
            $address = $s['Address'] ?? '';
            $class = $s['Class'] ?? '';
            $section_id = $s['Section'] ?? 0;
            $fees = $s['Fees'] ?? 0;
            $discount = $s['Discount'] ?? 0;
            $bus_service = isset($s['Bus-service']) ? 1 : 0;
            $bus_fees = $s['busFees'] ?? 0;
            $bus_type = $s['Bus-type'] ?? '';
            $previous_school = $s['Previous-school'] ?? '';

            $stmtStudent->bind_param(
                "isssssiiddsss",
                $father_id,
                $student_name,
                $age,
                $gender,
                $address,
                $class,
                $section_id,
                $fees,
                $discount,
                $bus_service,
                $bus_fees,
                $bus_type,
                $previous_school
            );
            $stmtStudent->execute();
        }
        $stmtStudent->close();
        $message = "<p style='color:#4cd137; font-weight:bold;'>تم إضافة الطلاب بنجاح ✅</p>";
    } else {
        $message = "<p style='color:#e84118; font-weight:bold;'>اختر ولي الأمر وأضف الطلاب أولاً!</p>";
    }
}
?>

<?php if($role): ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إضافة طلاب لولي أمر</title>
<style>
body { font-family: Arial,sans-serif; background: linear-gradient(135deg,#1e1e2f,#232946); color:#fff; padding:30px; }
.container { background:#1f1f34; padding:30px 40px; border-radius:15px; max-width:900px; margin:auto; box-shadow:0 6px 20px rgba(0,0,0,0.5); }
h2 { text-align:center; color:#4cd137; margin-bottom:25px; }
input, select { width:100%; padding:10px; border-radius:8px; border:none; background:#3c3c55; color:#fff; margin-top:5px; }
.student-block { border:1px solid #444; padding:15px; border-radius:12px; margin-bottom:15px; position:relative; background:#1f1f34; }
.remove-student { position:absolute; top:5px; right:850px; background:#e84118; color:#fff; border:none; border-radius:6px; cursor:pointer; padding:5px 10px; font-weight:bold; }
button, input[type="submit"] { padding:12px 20px; border:none; border-radius:10px; background:#4cd137; color:#fff; font-weight:bold; cursor:pointer; margin-top:10px; }
button:hover, input[type="submit"]:hover { background:#44bd32; }
.total { font-weight:bold; margin-top:10px; }
.back_button{
    display: block;
    margin: 20px auto;
    background: #ff4c4c;
    color: #fff;
    font-size: 18px;
    border:none; 
    border-radius:6px; 
    padding:8px 16px;
    cursor:pointer;}
</style>
</head>
<body>
<div class="container">
<h2>إضافة طلاب لولي أمر</h2>
<?php if($message) echo $message; ?>

<form method="post" id="mainForm">
    <label>اختر ولي الأمر</label>
    <input type="text" id="parentSearch" placeholder="ابحث عن ولي الأمر...">
    <select name="father_id" id="fatherSelect" required>
        <option value="">-- اختر ولي الأمر --</option>
        <?php
        $parents = $conn->query("SELECT id,father_name,phone FROM parents");
        while($p = $parents->fetch_assoc()){
            echo "<option value='".$p['id']."'>".$p['father_name']." | ".$p['phone']."</option>";
        }
        ?>
    </select>

    <hr style="margin:20px 0; border:1px solid #444;">

    <div id="studentsContainer"></div>
    <button type="button" id="addStudentBtn">إضافة طالب</button>

    <div class="total">الرسوم الإجمالية: <span id="grandTotal">0</span></div>
    <input type="submit" value="حفظ الطلاب">
</form>
</div>

<script>
// بحث ولي الأمر
const parentSearch = document.getElementById('parentSearch');
const fatherSelect = document.getElementById('fatherSelect');
parentSearch.addEventListener('keyup', function(){
    const term = this.value.toLowerCase();
    for(let i=0;i<fatherSelect.options.length;i++){
        const text = fatherSelect.options[i].text.toLowerCase();
        fatherSelect.options[i].style.display = text.includes(term)? '':'none';
    }
});

// الرسوم لكل صف
let classFees = {};
<?php
$res = $conn->query("SELECT * FROM class_fees");
while($r=$res->fetch_assoc()){
    echo "classFees['".$r['class_name']."']=".$r['fee'].";\n";
}
?>

// الشعب لكل صف
let sections = {};
<?php
$secRes = $conn->query("SELECT sec.*, cf.class_name FROM sections sec LEFT JOIN class_fees cf ON cf.id = sec.class_id");
while($s=$secRes->fetch_assoc()){
    echo "if(!sections['".$s['class_name']."']) sections['".$s['class_name']."']=[];\n";
    echo "sections['".$s['class_name']."'].push({id:".$s['id'].",name:'".$s['section_name']."'});\n";
}
?>

let studentsContainer = document.getElementById('studentsContainer');
let grandTotalSpan = document.getElementById('grandTotal');

function calculateGrandTotal(){
    let total = 0;
    document.querySelectorAll('.student-block').forEach(block=>{
        let fees = parseFloat(block.querySelector('.fees').value)||0;
        let bus = block.querySelector('.busCheck').checked ? (parseFloat(block.querySelector('.busFees').value)||0) :0;
        let discount = parseFloat(block.querySelector('.discount').value)||0;
        total += (fees+bus-discount);
    });
    grandTotalSpan.textContent = total;
}

function addStudentBlock(){
    let index = studentsContainer.children.length;
    let div = document.createElement('div');
    div.className = 'student-block';
    div.innerHTML = `
        <button type="button" class="remove-student">X</button><br><br>
        <label>اسم الطالب</label>
        <input type="text" name="students[${index}][Student-name]" required><br><br>
        <label>المدرسة السابقة</label>
        <input type="text" name="students[${index}][Previous-school]"><br><br>
        <label>العمر</label>
        <input type="number" name="students[${index}][Student-age]" required><br><br>
        <label>الجنس</label>
        <input type="radio" name="students[${index}][Student-gender]" value="ذكر" required> ذكر
        <input type="radio" name="students[${index}][Student-gender]" value="أنثى" required> أنثى<br><br>
        <label>العنوان</label>
        <input type="text" name="students[${index}][Address]" required><br><br>
        <label>الصف الدراسي</label>
        <select name="students[${index}][Class]" class="classSelect" required><br><br>
            <option value="">-- اختر الصف --</option>
            <?php
            $res2 = $conn->query("SELECT * FROM class_fees");
            while($r2=$res2->fetch_assoc()){
                echo "<option value='".$r2['class_name']."'>".$r2['class_name']."</option>";
            }
            ?>
        </select>
        <br><br><label>الشعبة</label>
        <select name="students[${index}][Section]" class="sectionSelect" required>
            <option value="">-- اختر الشعبة --</option>
        </select>
        <br><br><label>الرسوم الدراسية</label>
        <input type="number" name="students[${index}][Fees]" class="fees" readonly value="0"><br><br>
        <input type="checkbox" class="busCheck" name="students[${index}][Bus-service]"> خدمة الباص<br><br>
        <div class="bus-options" style="display:none;">
            <label>نوع الاشتراك</label>
            <select name="students[${index}][Bus-type]" class="busType">
                <option value="">-- اختر نوع الاشتراك --</option>
                <option value="full">كلي</option>
                <option value="partial">جزئي</option>
            </select>
            <br><br><label>رسوم الباص</label>
            <input type="number" name="students[${index}][busFees]" class="busFees" value="0"><br><br>
        </div>
        <label>الخصم</label>
        <input type="number" name="students[${index}][Discount]" class="discount" value="0"><br><br>
    `;

    studentsContainer.appendChild(div);

    const feesInput = div.querySelector('.fees');
    const busCheck = div.querySelector('.busCheck');
    const busOptions = div.querySelector('.bus-options');
    const busType = div.querySelector('.busType');
    const busFeesInput = div.querySelector('.busFees');
    const classSelect = div.querySelector('.classSelect');
    const sectionSelect = div.querySelector('.sectionSelect');
    const discountInput = div.querySelector('.discount');

    classSelect.addEventListener('change', ()=>{
        feesInput.value = classFees[classSelect.value]||0;
        sectionSelect.innerHTML = '<option value="">-- اختر الشعبة --</option>';
        if(sections[classSelect.value]){
            sections[classSelect.value].forEach(s=>{
                let opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                sectionSelect.appendChild(opt);
            });
        }
        calculateGrandTotal();
    });

    busCheck.addEventListener('change', ()=>{
        busOptions.style.display = busCheck.checked ? 'block':'none';
        calculateGrandTotal();
    });

    busType.addEventListener('change', ()=>{
        if(busType.value==='full') busFeesInput.value=100;
        else if(busType.value==='partial') busFeesInput.value=0;
        calculateGrandTotal();
    });

    [busFeesInput, discountInput].forEach(el=>el.addEventListener('input', calculateGrandTotal));

    div.querySelector('.remove-student').addEventListener('click', ()=>{
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
<p>ليس لديك صلاحيات الوصول لهذه الصفحة.</p>
<?php endif; ?>
