<?php
session_start();
session_destroy(); // ทำลาย session
header("Location: login.php");  // เปลี่ยนเส้นทางไปที่หน้า login
exit();
?>
