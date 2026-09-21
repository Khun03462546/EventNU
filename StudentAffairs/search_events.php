<?php
include "../db.php";
header('Content-Type: application/json');

// รับคำค้นหาจาก request
$searchQuery = json_decode(file_get_contents('php://input'))->searchQuery;

// สร้าง SQL Query สำหรับค้นหาชื่ออีเวนต์ที่ตรงกับคำค้นหาหรือใกล้เคียง
$sql = "SELECT * FROM Event WHERE Event_Name LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $searchQuery . "%"; // การค้นหาคำใกล้เคียง
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// เก็บข้อมูลอีเวนต์ที่ค้นพบ
$events = [];
while ($row = $result->fetch_assoc()) {
    $event_date = date("d F Y", strtotime($row['Event_Date']));
    $event_time = date("H:i", strtotime($row['Event_Time']));
    $price_display = ($row['Event_Price'] == 0) ? "Free" : $row['Event_Price'];
    $button_class = ($price_display == "Free") ? "navbar-event-button" : "navbar-event-button sold-out";
    $button_text = ($price_display == "Free") ? "ดูรายละเอียด" : "บัตรหมด";
    
    $events[] = [
        'event_id' => $row['EventID'],  // เพิ่ม event_id เพื่อใช้ในลิงก์
        'event_name' => $row['Event_Name'],
        'event_date' => $event_date,
        'event_location' => $row['Event_Location'],
        'event_picture' => $row['Event_Picture'],
        'event_ids' => $row['EventID'],  // เพิ่ม event_id เพื่อใช้ในลิงก์
        'event_names' => $row['Event_Name'],
        'event_dates' => $event_date,
        'event_locations' => $row['Event_Location'],
        'event_pictures' => $row['Event_Picture'],
        'button_class' => $button_class,
        'button_text' => $button_text,
    ];
}

// ส่งผลลัพธ์กลับไปยัง JavaScript
echo json_encode(['events' => $events]);

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
