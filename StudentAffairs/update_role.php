<?php
include('../db.php');

$email = $_POST['email'];
$role = $_POST['role'];

if(isset($email) && isset($role)) {
    // เตรียม SQL ให้อัปเดต Audience_Role และเก็บค่าปัจจุบันไว้ใน Previous_Role
    $sql = "UPDATE Audience 
            SET Previous_Role = Audience_Role, Audience_Role = ? 
            WHERE Audience_email = ?";
    
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $role, $email);
        if($stmt->execute()) {
            echo "success";
        } else {
            echo "เกิดข้อผิดพลาดในการอัปเดต role";
        }
        $stmt->close();
    } else {
        echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
    }
} else {
    echo "ข้อมูลไม่ครบถ้วน";
}

$conn->close();
?>
