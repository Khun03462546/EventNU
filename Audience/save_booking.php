<?php
include '../db.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'] ?? null;
    $ticket_count = $_POST['ticket_count'] ?? null;
    $ticket_totalprice = $_POST['ticket_totalprice'] ?? null;
    $email = $_POST['email'] ?? null;
    $booking_receipt = NULL;
    $paytime = "0000-00-00 00:00:00";
    if (!$event_id || !$ticket_count || !$ticket_totalprice || !$email) {
        error_log("❌ ข้อมูลไม่ครบ");
        echo json_encode(["success" => false, "message" => "❌ ข้อมูลไม่ครบ"]);
        exit;
    }

    error_log("📌 Event ID: $event_id, Tickets: $ticket_count, Total: $ticket_totalprice, Email: $email");
    // สร้าง SQL สำหรับการเพิ่มข้อมูลใน booking
    $sql_booking = "INSERT INTO booking (Audience_email, EventID, ticket_count, ticket_totalprice, booking_receipt, paytime) VALUES (?, ?, ?, ?, ?, ?)";
    error_log("📌 SQL (Booking): $sql_booking"); // ตรวจสอบ SQL Query
    $stmt_booking = $conn->prepare($sql_booking);

    if (!$stmt_booking) {
        error_log("❌ SQL Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "❌ SQL Prepare Failed: " . $conn->error]);
        exit;
    }

    $stmt_booking->bind_param("siidss", $email, $event_id, $ticket_count, $ticket_totalprice, $booking_receipt, $paytime);

    if ($stmt_booking->execute()) {
        $booking_id = $stmt_booking->insert_id; // ดึง booking_id ที่เพิ่มล่าสุด

        // สร้าง SQL สำหรับการเพิ่มข้อมูลใน register
        $sql_register = "INSERT INTO register (Audience_email, EventID, Register_date, register_status, booking_id) VALUES (?, ?, ?, ?, ?)";
        error_log("📌 SQL (Register): $sql_register"); // ตรวจสอบ SQL Query
        $stmt_register = $conn->prepare($sql_register);

        if (!$stmt_register) {
            error_log("❌ SQL Prepare Failed (register): " . $conn->error);
            echo json_encode(["success" => false, "message" => "❌ SQL Prepare Failed (register): " . $conn->error]);
            exit;
        }

        // กำหนดค่าเริ่มต้น register_status เป็น "pending"
        $register_status = "pending";
        $register_date = date("Y-m-d H:i:s");

        $stmt_register->bind_param("sissi", $email, $event_id, $register_date, $register_status, $booking_id);

        if ($stmt_register->execute()) {
            echo json_encode(["success" => true]);
        } else {
            error_log("❌ SQL Execute Failed (register): " . $stmt_register->error);
            echo json_encode(["success" => false, "message" => "❌ ไม่สามารถบันทึกข้อมูล register ได้: " . $stmt_register->error]);
        }

        $stmt_register->close();
    } else {
        error_log("❌ SQL Execute Failed (booking): " . $stmt_booking->error);
        echo json_encode(["success" => false, "message" => "❌ ไม่สามารถบันทึกข้อมูล booking ได้: " . $stmt_booking->error]);
    }

    $stmt_booking->close();
    $conn->close();
}
?>
