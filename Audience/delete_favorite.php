<?php
// เชื่อมต่อกับฐานข้อมูล
include('../db.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าได้รับค่า event_id หรือไม่
if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // เตรียมคำสั่ง SQL เพื่อลบข้อมูลที่ EventID ตรงกัน
    $sql = "DELETE FROM favorite WHERE EventID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id); // ใช้ i สำหรับ Integer

    // ตรวจสอบว่าการลบสำเร็จหรือไม่
    if ($stmt->execute()) {
        echo "success"; // ส่งผลลัพธ์กลับไปว่า ลบสำเร็จ
    } else {
        echo "error"; // ส่งผลลัพธ์กลับไปว่า ลบไม่สำเร็จ
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No event ID provided";
}
?>
