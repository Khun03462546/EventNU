<?php
// รับข้อมูลจากคำขอ
$data = json_decode(file_get_contents('php://input'), true);
$eventTitle = $data['title'];
include('../db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);


// ลบข้อมูลจากฐานข้อมูล
// ตรวจสอบว่ามี EventID อยู่ในตาราง booking หรือไม่
$checkSql = "SELECT COUNT(*) as count FROM booking WHERE EventID = (SELECT EventID FROM event WHERE Event_Name = ?)";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $eventTitle);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$row = $checkResult->fetch_assoc();

if ($row['count'] > 0) {
    // ถ้ามี EventID อยู่ในตาราง booking จะลบไม่ได้
    echo json_encode(['success' => false, 'message' => 'Cannot delete event because it is referenced in booking table']);
} else {
    // ลบข้อมูลจากฐานข้อมูล
    $sql = "DELETE FROM event WHERE Event_Name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $eventTitle); // ใช้ bind_param เพื่อป้องกัน SQL Injection
    $result = $stmt->execute();

    // ส่งผลลัพธ์กลับไปยัง client
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $stmt->close();
}

$checkStmt->close();
$conn->close();
?>
