<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

// รับค่าจาก POST
$action = $_POST['action']; // 'add' หรือ 'remove'
$event_id = $_POST['event_id'];
$email = $_POST['email'];

// ถ้าเป็นการเพิ่ม favorite
if ($action == 'add') {
    $sql = "INSERT INTO favorite (Audience_email, EventId) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $event_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถเพิ่ม Favorite ได้']);
    }
}
// ถ้าเป็นการลบ favorite
elseif ($action == 'remove') {
    $sql = "DELETE FROM favorite WHERE Audience_email = ? AND EventId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $event_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถยกเลิก Favorite ได้']);
    }
}

$conn->close();
?>