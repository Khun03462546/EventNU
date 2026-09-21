<?php
include('../db.php');

$email = $_POST['email'];

if(isset($email)) {
    // สลับค่า Role และ Previous_Role
    $sql = "UPDATE Audience 
            SET Audience_Role = Previous_Role 
            WHERE Audience_email = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        if($stmt->execute()) {
            echo "success";
        } else {
            echo "เกิดข้อผิดพลาดในการคืนค่า role";
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
