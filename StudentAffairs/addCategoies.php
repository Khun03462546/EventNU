<?php
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "eventnu"; // เปลี่ยนชื่อฐานข้อมูล

$conn = new mysqli($servername, $username, $password, $dbname);

// เช็คการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่ามีข้อมูลจากฟอร์มส่งมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name']; // รับค่าจากฟอร์ม

    // สร้างคำสั่ง SQL เพื่อเพิ่มข้อมูล
    $sql = "INSERT INTO categories (categories) VALUES ('$category_name')";

    if ($conn->query($sql) === TRUE) {
        echo "เพิ่มหมวดหมู่ใหม่สำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มหมวดหมู่</title>
</head>
<body>

<h2>เพิ่มหมวดหมู่ใหม่</h2>
<form method="post" action="">
    <label for="category_name">ชื่อหมวดหมู่:</label>
    <input type="text" id="category_name" name="category_name" required>
    <input type="submit" value="เพิ่ม">
</form>

</body>
</html>
