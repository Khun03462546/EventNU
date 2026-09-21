<?php
include '../db.php'; // เชื่อมต่อฐานข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($event_id && $status) {
        $sql = "UPDATE register SET register_status = ? WHERE register_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die(json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]));
        }

        $stmt->bind_param("si", $status, $event_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Execute Error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบถ้วน"]);
    }
}
$conn->close();
?>
