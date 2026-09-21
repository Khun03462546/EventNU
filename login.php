<?php
include "db.php"; // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ค้นหาผู้ใช้จากตาราง Audience
    $sql = "SELECT Audience_Password, Audience_Role FROM Audience WHERE Audience_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $row['Audience_Password'])) {
            // Login สำเร็จ
            session_start();
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $row['Audience_Role'];

            // ไปยังหน้าตาม role
            if ($row['Audience_Role'] === 'organizer') {
                header("Location: ./EventOrganizer/home.php");
            } else {
                header("Location: ./Audience/home.php");
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No user found with this email!";
    }

    // ค้นหาผู้ใช้จากฐานข้อมูล
    $sql = "SELECT committee_Password FROM committee WHERE committee_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $row['committee_Password'])) {
            // การ login สำเร็จ
            session_start();
            $_SESSION['email'] = $email;
            header("Location: ./Committee/home.php"); // เปลี่ยนไปที่หน้าหลังจาก login
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No user found with this email!";
    }

    // ค้นหาผู้ใช้จากฐานข้อมูล
    $sql = "SELECT studentaffairs_Password FROM studentaffairs WHERE Studentaffairs_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $row['studentaffairs_Password'])) {
            // การ login สำเร็จ
            session_start();
            $_SESSION['email'] = $email;
            header("Location: ./StudentAffairs/home.php"); // เปลี่ยนไปที่หน้าหลังจาก login
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No user found with this email!";
    }

    // ค้นหาผู้ใช้จากฐานข้อมูล
    $sql = "SELECT President_password FROM president WHERE President_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $row['President_password'])) {
            // การ login สำเร็จ
            session_start();
            $_SESSION['email'] = $email;
            header("Location: ./President/home.php"); // เปลี่ยนไปที่หน้าหลังจาก login
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No user found with this email!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/meyawo.css">
    <title>Login</title>
</head>

<body class="login-body">

    <div class="eventlogin-container">
        <img src="assets/imgs/logoEventNU.png" alt="NU Event Logo" class="eventlogin-logo">
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; // แสดงข้อความผิดพลาด 
        ?>
        <form method="POST" action="">
            <input type="email" name="email" class="eventlogin-input" placeholder="Email" required>
            <input type="password" name="password" class="eventlogin-input" placeholder="Password" required>
            <button type="submit" class="eventlogin-button">Log In</button>
        </form>
        <p class="eventlogin-text">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </div>

</body>

</html>