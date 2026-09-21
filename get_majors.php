<?php
include "db.php";

if (isset($_GET['faculty_id'])) {
    $faculty_id = $_GET['faculty_id'];

    $major_sql = "SELECT MajorID, Major_Name FROM Major WHERE FacultyID = ?";
    $stmt = $conn->prepare($major_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $majors = [];
    while ($row = $result->fetch_assoc()) {
        $majors[] = $row;
    }

    echo json_encode($majors);
}

$conn->close();
?>
