<?php
session_start();
require 'DB_CONNECT.php';
$role = $_SESSION['role'] ?? "";
$username = $_SESSION['username'] ?? "";
if (!$role){
    echo"ليس لديك صلاحيات للوصول";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>المصاريف اليومية</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: "Tahoma", sans-serif;
            background-color: #1a1525; /* بنفسجي غامق */
            color: #f0e9ff; /* نص فاتح */
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #c9a6ff;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #2a2139;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.6);
            display: grid;
            gap: 12px;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[name="amount"],
        input[name="spending_cause"],
        input[name="for_who"],
        input[name="spending_date"] {
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: #3a2d4f;
            color: #f0e9ff;
        }

        input[type="submit"] {
            background: #6d28d9;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background: #8b5cf6;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background: #2a2139;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.6);
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: #4c1d95;
            color: #f0e9ff;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #3a2d4f;
        }

        tr:nth-child(odd) {
            background: #2a2139;
        }

        tr:hover {
            background: #5b21b6;
        }
        .back_button{
            position: fixed;
            top: 1%;
            background-color: red;
            font-size: 33px;
            right: 10px;
        }
    </style>
</head>
<body>
    <h1>المصاريف اليومية</h1>

    <form method="post">
        <input name="amount" placeholder="المبلغ">
        <input name="spending_cause" placeholder="سبب الصرف">
        <input name="for_who" placeholder="صرفت ل">
        <input name="spending_date" type="date" placeholder="تاريخ الصرف">
        <input name="submit" type="submit" value="احفظ">
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $amount = $_POST['amount'];
        $spending_cause = $_POST['spending_cause'];
        $for_who = $_POST['for_who'];
        $spending_date = $_POST['spending_date'];

        $Insert = "INSERT INTO spending (amount, spending_cause, for_who, spending_date) 
                   VALUES ('$amount', '$spending_cause', '$for_who', '$spending_date')";
        mysqli_query($conn, $Insert);
    }

    $save = "SELECT amount, spending_cause, for_who, spending_date FROM spending ORDER BY spending_date DESC";
    $intable = mysqli_query($conn, $save);

    if (mysqli_num_rows($intable) > 0) {
        echo "<table>";
        echo "<tr>
                <th>المبلغ</th>
                <th>سبب الصرف</th>
                <th>صرفت ل</th>
                <th>تاريخ الصرف</th>
              </tr>";
        while ($row = mysqli_fetch_assoc($intable)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
            echo "<td>" . htmlspecialchars($row['spending_cause']) . "</td>";
            echo "<td>" . htmlspecialchars($row['for_who']) . "</td>";
            echo "<td>" . htmlspecialchars($row['spending_date']) . "</td>";
            echo "</tr>";
        }
        $total = "SELECT SUM(amount) as total FROM spending";
        $totalresult = mysqli_query($conn , $total);
        $totalrow = mysqli_fetch_assoc($totalresult);
        echo '<tr>';
        echo "<h1>" . "المجموع: " . $totalrow['total'] ?? 0 . "</h1>";
        echo'</tr>';

        echo "</table>";




        





    } else {
        echo "<p style='text-align:center; color:#aaa;'>لا توجد بيانات بعد.</p>";
    }
    ?>



    <!-- زر مسح البيانات -->
<center><form method="post" style="text-align:center; margin:20px;">
    <input type="submit" name="clear_table" value="إفراغ جميع البيانات" 
           style="background:#e11d48; color:#fff; padding:10px 20px; border:none; border-radius:8px; cursor:pointer;">
</form></center>

<?php
if (isset($_POST['clear_table'])) {
    // مسح جميع البيانات من جدول spending
    $truncate = "TRUNCATE TABLE spending"; // أو استخدم DELETE FROM spending
    if (mysqli_query($conn, $truncate)) {
        echo "<p style='text-align:center; color:#fff; background:#e11d48; padding:10px; border-radius:8px;'>تم مسح جميع البيانات بنجاح ✔</p>";
    } else {
        echo "<center><p style='text-align:center; color:#fff; background:#e11d48; padding:10px; border-radius:8px;'>خطأ: " . mysqli_error($conn) . "</p></center>";
    }
}
?>
<button onclick="window.history.back()" class="back_button">العودة</button>                


</body>
</html>
