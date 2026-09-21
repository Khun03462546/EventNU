<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบว่า user เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่ได้ล็อกอินให้ไปหน้า login
    exit();
}

// รับข้อมูลที่ส่งมาจากฟอร์ม
$event_id = $_POST['event_id'];
$feedback_comment = $_POST['feedback_comment'];
$feedback_option = json_decode($_POST['feedback_option'], true); // แปลงข้อมูลจาก JSON ที่ส่งมาจาก JavaScript
$total_score = $_POST['total_score'];
$email = $_SESSION['email'];  // ใช้ email ของผู้ใช้งานจาก session

// ตรวจสอบให้แน่ใจว่าไม่มีข้อมูลว่าง
if (empty($event_id) || empty($feedback_comment) || empty($feedback_option) || !isset($total_score)) {
    echo "ข้อมูลไม่ครบถ้วน!";
    exit();
}

// เตรียมคำสั่ง SQL สำหรับการบันทึกข้อมูล
$sql = "INSERT INTO feedback (EventID, audience_email, feedback_comment, feedback_option, feedback_point) 
        VALUES (?, ?, ?, ?, ?)";

// เตรียมคำสั่ง SQL
$stmt = $conn->prepare($sql);

// แปลง array ของ feedback_option เป็น string ก่อนบันทึกในฐานข้อมูล
$feedback_option_str = json_encode($feedback_option);

// เชื่อมโยงค่าใน SQL
$stmt->bind_param("isssi", $event_id, $email, $feedback_comment, $feedback_option_str, $total_score);

// ตรวจสอบและดำเนินการ
if ($stmt->execute()) {
    // ถ้าบันทึกสำเร็จ ให้กลับไปที่หน้า feedback.php
    header("Location: feedback.php?event_id=$event_id&success=true");
    exit();
} else {
    echo "เกิดข้อผิดพลาดในการบันทึกคำติชม.";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$stmt->close();
$conn->close();
?>
