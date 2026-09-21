<?php
$password = 'Nepjoon123';  // รหัสผ่านที่คุณต้องการแปลง
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);  // แปลงเป็น hash
echo $hashedPassword;  // แสดง hash ที่ได้

$hashedPasswordd = '$2y$10$wJd9Z6gCv9aEQu5l.A7eO.PjqkdmAM1BEXd7HLJHAd1I.KqHtbXhW'; // รหัสแฮชในฐานข้อมูล
$userPassword = 'Nepjoon123'; // รหัสที่ผู้ใช้กรอก

// ตรวจสอบรหัสผ่าน
if (password_verify($userPassword, $hashedPasswordd)) {
    echo "รหัสผ่านถูกต้อง!";
} else {
    echo "รหัสผ่านไม่ถูกต้อง!";
}
?>