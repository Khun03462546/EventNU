<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_SESSION['email'];
    $favorite_id = $_POST['favorite_id'];
    $action = $_POST['action'];

    if (empty($email) || empty($favorite_id) || empty($action)) {
        echo json_encode(['success' => false, 'error' => 'ข้อมูลไม่ครบถ้วน!']);
        exit();
    }

    if ($action === "add") {
        // เพิ่มการแจ้งเตือน
        $sql = "INSERT INTO notification (Audience_email, favorite_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $favorite_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ไม่สามารถเพิ่มการแจ้งเตือนได้!']);
        }
    } elseif ($action === "remove") {
        // ลบการแจ้งเตือน
        $sql = "DELETE FROM notification WHERE Audience_email = ? AND favorite_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $favorite_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'ไม่สามารถลบการแจ้งเตือนได้!']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'การกระทำไม่ถูกต้อง!']);
    }
    $conn->close();
}
?>
