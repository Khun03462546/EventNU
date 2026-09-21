<?php
session_start();
include "../db.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
$event_name = $_POST['eventName'];
$event_date = $_POST['dateevent'];
$event_time = $_POST['timeevent'];
$event_location = $_POST['locationevent'];
$event_price = $_POST['prictticketevent'];
$event_map = $_POST['mapevent'];
$event_budget = $_POST['budgetTotal'];
$event_saledate = !empty($_POST['datesale']) ? $_POST['datesale'] : NULL;
$event_saletime = !empty($_POST['timesale']) ? $_POST['timesale'] : NULL;
$event_type = ($_POST['registerType'] == "yes") ? "yes" : "no";
$event_detail = $_POST['eventDetails'];
$category_id = $_POST['category'];

// ตรวจสอบการอัปโหลดรูปภาพ
if (isset($_FILES["eventImage"]) && $_FILES["eventImage"]["error"] == UPLOAD_ERR_OK) {
    $file_tmp = $_FILES["eventImage"]["tmp_name"];
    $file_name = $_FILES["eventImage"]["name"];
    $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png'];

    if (!in_array($imageFileType, $allowed_types)) {
        die("อัปโหลดได้เฉพาะไฟล์ JPG, JPEG, PNG เท่านั้น");
    }

    $target_dir = "../assets/imgs/event/";
    $target_file = $target_dir . basename($file_name);

    if (file_exists($target_file)) {
        $target_file = $target_dir . uniqid() . '.' . $imageFileType;
    }

    if (!move_uploaded_file($file_tmp, $target_file)) {
        die("อัปโหลดไฟล์ล้มเหลว");
    }

    $image_path = $target_file;
} else {
    die("ไม่พบไฟล์ที่อัปโหลด หรือเกิดข้อผิดพลาดในการอัปโหลด");
}

// SQL Insert
$sql = "INSERT INTO request_event (
    Audience_email, request_event_status, request_event_name, category_id,
    request_event_detail, request_event_picture, request_event_date,
    request_event_saletime, request_event_saledate, request_event_time,
    request_event_location, request_event_map, request_event_price,
    request_event_type, request_event_budget
) VALUES (?, 'committee', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing: " . $conn->error);
}

$stmt->bind_param(
    "ssisssssssssdss",
    $email, $event_name, $category_id,
    $event_detail, $image_path, $event_date,
    $event_saletime, $event_saledate, $event_time,
    $event_location, $event_map, $event_price,
    $event_type, $event_budget
);

if ($stmt->execute()) {
    echo "<script>alert('บันทึกอีเว้นท์สำเร็จ'); window.location.href='followrequest.php';</script>";
} else {
    echo "บันทึกไม่สำเร็จ: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
