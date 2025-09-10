<?php
session_start();
require "DB_CONNECT.php";

$role = $_SESSION['role'] ?? '';
if (!$role) {
    die("<p style='color:red; text-align:center; margin-top:50px;'>ليس لديك صلاحيات للوصول</p>");
}

if (isset($_POST['backup'])) {

    $dbHost = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "school_db";

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $sqlScript = "";
    foreach ($tables as $table) {
        $query = $conn->query("SHOW CREATE TABLE $table");
        $row = $query->fetch_row();
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";

        $query = $conn->query("SELECT * FROM $table");
        $columnCount = $query->field_count;

        for ($i = 0; $i < $query->num_rows; $i++) {
            $rowData = $query->fetch_row();
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $columnCount; $j++) {
                $rowData[$j] = $rowData[$j] ? addslashes($rowData[$j]) : "NULL";
                $sqlScript .= isset($rowData[$j]) ? "'".$rowData[$j]."'" : "''";
                if ($j < ($columnCount - 1)) $sqlScript .= ",";
            }
            $sqlScript .= ");\n";
        }
        $sqlScript .= "\n";
    }

    $backupFileName = $dbName . '_backup_' . date("Y-m-d_H-i-s") . '.sql';

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=' . $backupFileName);
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $sqlScript;
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>نسخة احتياطية للقاعدة</title>
    <style>
        body {
            background: linear-gradient(135deg, #1b0a2e, #2e1a4f);
            font-family: Arial, sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #2a1b3d;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
            text-align: center;
        }
        h1 {
            margin-bottom: 30px;
            font-size: 28px;
            color: #f0e5ff;
        }
        button {
            background-color: #7d3cff;
            border: none;
            color: #fff;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: #9a5fff;
        }
        p {
            margin-top: 20px;
            font-size: 16px;
            color: #d1c4e9;
        }
        .back_button{
            position: fixed;
            top: 1%;
            background-color: red;
            font-size: 22px;
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تنزيل نسخة احتياطية من قاعدة البيانات</h1>
        <form method="post">
            <button type="submit" name="backup">تحميل النسخة الاحتياطية</button>
        </form>
        <p>يتم إنشاء ملف SQL جاهز للتحميل مباشرة على جهازك.</p>
    </div>
    <button onclick="window.history.back()" class="back_button">العودة</button>
</body>
</html>
