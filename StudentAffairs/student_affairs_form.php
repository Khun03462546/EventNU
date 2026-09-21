<?php
// เริ่มต้น session และเชื่อมต่อฐานข้อมูล
session_start();
include "../db.php";

// ดึงข้อมูล Gender
$gender_sql = "SELECT GenderID, GenderType FROM Gender";
$gender_result = $conn->query($gender_sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $studentaffairs_email = $_POST['StudentAffairs_email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $personal_id = $_POST['personal_id'];
    $gender_id = $_POST['gender_id'];
    $position = $_POST['position'];
    $phone = $_POST['phone'];
    $studentaffairs_password = password_hash($_POST['studentaffairs_password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน

    // สร้างคำสั่ง SQL สำหรับการบันทึกข้อมูล
    $sql = "INSERT INTO studentaffairs (StudentAffairs_email, first_name, last_name, personal_id, GenderID, position, phone, studentaffairs_password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // เตรียมคำสั่ง SQL
    if ($stmt = $conn->prepare($sql)) {
        // ผูกค่ากับคำสั่ง SQL
        $stmt->bind_param("ssssssss", $studentaffairs_email, $first_name, $last_name, $personal_id, $gender_id, $position, $phone, $studentaffairs_password);

        // เริ่มการบันทึกข้อมูล
        if ($stmt->execute()) {
            echo "ข้อมูลถูกบันทึกเรียบร้อย!";
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ไม่สามารถเตรียมคำสั่ง SQL ได้: " . $conn->error;
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มข้อมูล Student Affairs</title>
</head>

<body>
    <h2>ฟอร์มกรอกข้อมูลสำหรับ Student Affairs</h2>

    <form action="student_affairs_form.php" method="POST">
        <label for="StudentAffairs_email">อีเมล์:</label><br>
        <input type="email" id="StudentAffairs_email" name="StudentAffairs_email" required><br><br>

        <label for="first_name">ชื่อ:</label><br>
        <input type="text" id="first_name" name="first_name" required><br><br>

        <label for="last_name">นามสกุล:</label><br>
        <input type="text" id="last_name" name="last_name" required><br><br>

        <label for="personal_id">รหัสประจำตัว:</label><br>
        <input type="text" id="personal_id" name="personal_id" required><br><br>

        <select name="gender_id" class="signup-input" required>
            <option value="">Select Gender</option>
            <?php while ($row = $gender_result->fetch_assoc()) { ?>
                <option value="<?php echo $row['GenderID']; ?>"><?php echo $row['GenderType']; ?></option>
            <?php } ?>
        </select><br><br>

        <label for="position">ตำแหน่ง:</label><br>
        <input type="text" id="position" name="position" required><br><br>

        <label for="phone">เบอร์โทรศัพท์:</label><br>
        <input type="text" id="phone" name="phone" required><br><br>

        <label for="studentaffairs_password">รหัสผ่าน:</label><br>
        <input type="password" id="studentaffairs_password" name="studentaffairs_password" required><br><br>

        <input type="submit" value="บันทึกข้อมูล">
    </form>
</body>

</html>