<?php
session_start();
include "../db.php";  // รวมไฟล์เชื่อมต่อฐานข้อมูล

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['email'])) {
    die("User is not logged in.");
}

// รับค่า request_event_id และ vote_status ที่ส่งจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $event_id = $_POST['request_event_id'];
    $vote_status = $_POST['vote_status'];

    // ตรวจสอบค่าของ email จาก session
    $email = $_SESSION['email'];

    // ตรวจสอบว่า event_id ที่ส่งมาจากฟอร์มมีในฐานข้อมูล request_event หรือไม่
    $sql_check_event = "SELECT * FROM request_event WHERE request_event_id = ?";
    $stmt_check_event = $conn->prepare($sql_check_event);
    $stmt_check_event->bind_param("i", $event_id);
    $stmt_check_event->execute();
    $result_check_event = $stmt_check_event->get_result();

    if ($result_check_event->num_rows == 0) {
        echo "Event ID not found in request_event table.";
        exit();
    }

    // เช็คว่า committee_email นี้ได้โหวตไปยัง request_event_id นี้หรือยัง
    $sql_check_vote = "SELECT * FROM vote WHERE request_event_id = ? AND committee_email = ?";
    $stmt_check_vote = $conn->prepare($sql_check_vote);
    $stmt_check_vote->bind_param("is", $event_id, $email); // เช็คว่า email นี้มีการโหวตใน request_event_id นี้หรือไม่
    $stmt_check_vote->execute();
    $result_check_vote = $stmt_check_vote->get_result();

    if ($result_check_vote->num_rows > 0) {
        echo "You have already voted for this event.";
        exit(); // ถ้าโหวตแล้ว ให้หยุดการทำงาน
    }

    // ถ้าผู้ใช้เลือก "อนุมัติ" หรือ "ไม่อนุมัติ"
    if ($vote_status == "อนุมัติ") {
        // ถ้าผู้ใช้เลือก "อนุมัติ" ทำการอัพเดท request_event_point โดยใช้ COALESCE เพื่อแทนค่า NULL ด้วย 0
        $sql_update_point = "UPDATE request_event SET request_event_point = COALESCE(request_event_point, 0) + 1 WHERE request_event_id = ?";
        $stmt_update_point = $conn->prepare($sql_update_point);
        $stmt_update_point->bind_param("i", $event_id);

        if (!$stmt_update_point->execute()) {
            echo "Error executing update point: " . $stmt_update_point->error;
        } else {
            echo "Point updated successfully!";
        }
    }

    // บันทึกการโหวตในตาราง vote
    $sql_vote = "INSERT INTO vote (committee_email, request_event_id, vote_status) VALUES (?, ?, ?)";
    $stmt_vote = $conn->prepare($sql_vote);

    if ($stmt_vote === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt_vote->bind_param("sis", $email, $event_id, $vote_status);  // ใช้ "i" สำหรับ integer และ "s" สำหรับ string

    if (!$stmt_vote->execute()) {
        die("Error executing statement: " . $stmt_vote->error);
    }

    // แสดงข้อความหรือทำการ redirect
    echo "Vote successfully recorded!";
    header("Location: assessment.php");  // เปลี่ยนเส้นทางไปยังหน้าตรวจสอบการประเมิน
    exit();
}
?>
