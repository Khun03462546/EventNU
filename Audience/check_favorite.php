<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

// รับค่าจาก POST
$event_id = $_POST['event_id'];
$email = $_POST['email'];

// ตรวจสอบว่าเป็นผู้ใช้ที่ลงชื่อเข้าใช้งาน
if (!isset($email)) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้งาน']);
    exit();
}

// ตรวจสอบว่าเหตุการณ์นั้นถูกทำเครื่องหมายเป็น Favorite หรือไม่
$sql = "SELECT * FROM favorite WHERE Audience_email = ? AND EventId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email, $event_id);
$stmt->execute();
$result = $stmt->get_result();

// ส่งสถานะกลับไปยัง JavaScript
if ($result->num_rows > 0) {
    echo json_encode(['is_favorite' => true]);
} else {
    echo json_encode(['is_favorite' => false]);
}

$conn->close();
?>
