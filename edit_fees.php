<?php
    session_start(); // استرجاع الجلسة
    $role = $_SESSION['role'] ?? ''; // إذا لم يكن موجود، يصبح فارغ
    require "DB_CONNECT.php";   
?>


<?php
    if ($role === "admin") :
?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>تعديل الاقساط</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                margin: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #1e1e2f, #232946);
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                }
                .form_to_edit{
                    right: 500px;
                    background: #2a2a3d;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 6px 15px rgba(0,0,0,0.5);
                    width: 320px;
                    text-align: center;
                    color: #fff; 50px;
                }
                .back_button{
                    position: fixed;
                    top: 1%;
                    background-color: red;
                    font-size: 30px;
                    right: 10px;
                }
                .save{
                    background-color: #44bd32;
                    font-size: 20px;


                }
            </style>
        </head>

        <body>
                <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        foreach ($_POST['fees'] as $classId => $fee) {
                            $fee = intval($fee);
                            $conn->query("UPDATE class_fees SET fee = $fee WHERE id = $classId");
                        }
                        echo "<p style='color:green'>تم تحديث الأقساط بنجاح</p>";
                    }

                    $result = $conn->query("SELECT * FROM class_fees");
                    ?>
                    <form method="post" class="form_to_edit">
                    <table border="1" cellpadding="10">
                        <tr>
                            <th>الصف</th>
                            <th>القسط</th>
                        </tr>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td>
                                    <input type="number" name="fees[<?= $row['id'] ?>]" value="<?= $row['fee'] ?>">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                    <br>
                    <input type="submit" value="حفظ التغييرات" class="save">
                    </form>
        
                    <button onclick="window.history.back()" class="back_button">العودة</button>                
        </body>
    </html>
<?php  
    else :
        echo"ليس لديك صلاحيات للوصول هنا";
?>
<?php endif; ?>
