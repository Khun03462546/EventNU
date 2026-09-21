<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['email'])) {
    die("Unauthorized access");
}

$first_name = $_POST['firstName'] ?? null;
$last_name = $_POST['lastName'] ?? null;
$personal_id = $_POST['staffId'] ?? null;
$birth_Date = $_POST['birthDate'] ?? null;
$gender = $_POST['gender'] ?? null;
$position = $_POST['position'] ?? null;
$phone = $_POST['phoneNumber'] ?? null;

$update_fields = [];
$params = [];
$types = '';

if ($first_name !== null) {
    $update_fields[] = 'first_name = ?';
    $params[] = $first_name;
    $types .= 's';
}
if ($last_name !== null) {
    $update_fields[] = 'last_name = ?';
    $params[] = $last_name;
    $types .= 's';
}
if ($personal_id !== null) {
    $update_fields[] = 'personal_id = ?';
    $params[] = $personal_id;
    $types .= 's';
}
if ($birth_Date !== null) {
    $update_fields[] = 'birth_date = ?';
    $params[] = $birth_Date;
    $types .= 's';
}
if ($gender !== null) {
    $update_fields[] = 'genderID = ?';
    $params[] = $gender;
    $types .= 'i';
}
if ($position !== null) {
    $update_fields[] = 'position = ?';
    $params[] = $position;
    $types .= 's';
}
if ($phone !== null) {
    $update_fields[] = 'phone = ?';
    $params[] = $phone;
    $types .= 's';
}

if (empty($update_fields)) {
    die("No fields to update.");
}

$params[] = $_SESSION['email'];
$types .= 's';

$sql = "UPDATE studentaffairs SET " . implode(', ', $update_fields) . " WHERE studentaffairs_email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL Error: {$conn->error}");
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo "Data saved successfully.";
} else {
    echo "Error: {$stmt->error}";
}

$stmt->close();
$conn->close();
