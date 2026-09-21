<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['email'])) {
    echo "Session expired!";
    exit();
}

if (!isset($_POST['register_id']) || !isset($_FILES['qr_code'])) {
    echo "Missing data!";
    exit();
}

$register_id = intval($_POST['register_id']);
$email = $_SESSION['email'];
date_default_timezone_set("Asia/Bangkok");
$paytime = date("Y-m-d H:i:s");

// กำหนดโฟลเดอร์ปลายทาง
$target_dir = "../assets/imgs/receipt/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// สร้างชื่อไฟล์ใหม่เพื่อลดความซ้ำซ้อน
$file_name = "receipt_" . time() . "_" . basename($_FILES["qr_code"]["name"]);
$target_file = $target_dir . $file_name;
$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// ตรวจสอบประเภทไฟล์ (อนุญาตเฉพาะ JPG, PNG, PDF)
$allowed_types = ["jpg", "jpeg", "png", "pdf"];
if (!in_array($file_type, $allowed_types)) {
    echo "Invalid file type!";
    exit();
}

// อัปโหลดไฟล์
if (move_uploaded_file($_FILES["qr_code"]["tmp_name"], $target_file)) {
    // อัปเดตฐานข้อมูล
    $sql = "UPDATE booking b
        JOIN register r ON b.booking_id = r.booking_id
        SET b.booking_receipt = ?, 
            b.paytime = ?,  -- เพิ่ม paytime
            r.register_status = 'verifying'
        WHERE r.register_id = ? AND r.Audience_email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $file_name, $paytime, $register_id, $email);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Database update failed!";
    }
    $stmt->close();
} else {
    echo "File upload failed!";
}
$conn->close();
