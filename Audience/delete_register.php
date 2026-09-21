<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['email'])) {
    echo "Session expired!";
    exit();
}

if (!isset($_POST['register_id']) || !is_numeric($_POST['register_id'])) {
    echo "Invalid register ID!";
    exit();
}

$register_id = intval($_POST['register_id']);
$email = $_SESSION['email'];

// ตรวจสอบว่า register_id นี้เป็นของผู้ใช้ที่ล็อกอินอยู่หรือไม่
$sql_get_booking = "SELECT booking_id FROM register WHERE register_id = ? AND Audience_email = ?";
$stmt = $conn->prepare($sql_get_booking);
$stmt->bind_param("is", $register_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No record found!";
    exit();
}

$row = $result->fetch_assoc();
$booking_id = $row['booking_id'];

$stmt->close();

// ลบข้อมูลใน register
$sql_delete_register = "DELETE FROM register WHERE register_id = ? AND Audience_email = ?";
$stmt = $conn->prepare($sql_delete_register);
$stmt->bind_param("is", $register_id, $email);
$stmt->execute();
$stmt->close();

// ลบข้อมูลใน booking
if ($booking_id) {
    $sql_delete_booking = "DELETE FROM booking WHERE booking_id = ?";
    $stmt = $conn->prepare($sql_delete_booking);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();
}

echo "success";
$conn->close();
?>
