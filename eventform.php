<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $event_name = $conn->real_escape_string($_POST["event_name"]);
    $event_date = $conn->real_escape_string($_POST["event_date"]);
    $event_time = $conn->real_escape_string($_POST["event_time"]);
    $event_layout = $conn->real_escape_string($_POST["event_layout"]);
    $event_location = $conn->real_escape_string($_POST["event_location"]);
    $event_detail = $conn->real_escape_string($_POST["event_detail"]);
    $category_id = intval($_POST["category_id"]);
    $event_price = floatval($_POST["event_price"]);
    $type_register = $conn->real_escape_string($_POST["type_register"]);
    $check_free_event = $conn->real_escape_string($_POST["check_free_event"]);
    
    // Handle file upload
    $event_picture = "";
    if (isset($_FILES["event_picture"]) && $_FILES["event_picture"]["error"] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["event_picture"]["name"]);
        
        // Check if the file was uploaded successfully
        if (move_uploaded_file($_FILES["event_picture"]["tmp_name"], $target_file)) {
            $event_picture = $target_file;
        } else {
            echo "Error uploading the file.<br>";
        }
    }

    // Prepare SQL query
    $sql = "INSERT INTO event (Event_Name, Event_Date, Event_Time, Event_Layout, Event_Location, Event_Detail, Event_Picture, CategoryID, Event_Price, TypeRegister, CheckFreeEvent) 
            VALUES ('$event_name', '$event_date', '$event_time', '$event_layout', '$event_location', '$event_detail', '$event_picture', '$category_id', '$event_price', '$type_register', '$check_free_event')";
    
    // Debug: output the query to check for errors
    echo "SQL Query: " . $sql . "<br>";
    
    // Execute the query and handle errors
    if ($conn->query($sql) === TRUE) {
        echo "New event added successfully<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
    
    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มอีเว้นท์</title>
</head>
<body>
    <h2>เพิ่มอีเว้นท์</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        ชื่ออีเว้นท์: <input type="text" name="event_name" required><br>
        วันที่: <input type="date" name="event_date" required><br>
        เวลา: <input type="time" name="event_time" required><br>
        รูปแบบ: <textarea name="event_layout"></textarea><br>
        สถานที่: <input type="text" name="event_location" required><br>
        รายละเอียด: <textarea name="event_detail"></textarea><br>
        รูปภาพ: <input type="file" name="event_picture" accept="image/*"><br>
        หมวดหมู่: <input type="number" name="category_id"><br>
        ราคา: <input type="number" name="event_price" step="0.01" required><br>
        ลงทะเบียน: 
        <select name="type_register" required>
            <option value="yes">Yes</option>
            <option value="no">No</option>
        </select><br>
        ประเภทอีเว้นท์: 
        <select name="check_free_event" required>
            <option value="free">Free</option>
            <option value="paid">Paid</option>
        </select><br>
        <input type="submit" value="บันทึก">
    </form>
</body>
</html>
