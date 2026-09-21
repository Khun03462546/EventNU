<?php
// เชื่อมต่อกับฐานข้อมูล
include('db_connection.php');
// ตรวจสอบข้อมูลที่ส่งมา
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id']; // ID ของ Event
    $title = $_POST['title']; // ชื่อ Event
    $time = $_POST['time']; // เวลา
    $date = $_POST['date']; // วันที่
    $location = $_POST['location']; // สถานที่
    $details = $_POST['details']; // รายละเอียด
    $ticket_release = $_POST['ticket_release']; // วันวางจำหน่ายตั๋ว

    // คำสั่ง SQL สำหรับอัพเดตข้อมูล Event
    $sql = "UPDATE events SET 
            title = ?, 
            time = ?, 
            date = ?, 
            location = ?, 
            details = ?, 
            ticket_release = ? 
            WHERE event_id = ?";

    // เตรียมคำสั่ง SQL
    if ($stmt = $conn->prepare($sql)) {
        // ผูกข้อมูล
        $stmt->bind_param("ssssssi", $title, $time, $date, $location, $details, $ticket_release, $event_id);
        
        // เรียกใช้งานคำสั่ง
        if ($stmt->execute()) {
            echo "Event updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        // ปิดคำสั่ง
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }

    // ปิดการเชื่อมต่อ
    $conn->close();
}
?>
