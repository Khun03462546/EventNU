<?php
session_start();
include "../db.php";

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่มี session, เปลี่ยนเส้นทางไปหน้า login
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$email = $_SESSION['email'];
$sql = "SELECT 
            a.*, 
            f.Faculty_Name, 
            m.Major_Name,
            g.GenderType
        FROM Audience a
        LEFT JOIN Faculty f ON a.FacultyID = f.FacultyID
        LEFT JOIN Major m ON a.MajorID = m.MajorID
        LEFT JOIN Gender g ON a.GenderID = g.GenderID
        WHERE a.Audience_email = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error (User Query): " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'] ?? "N/A";
    $last_name = $row['Audience_LastName'] ?? "N/A";
    $faculty = $row['Faculty_Name'] ?? "N/A";
    $major = $row['Major_Name'] ?? "N/A";
    $role = $row['Audience_Role'] ?? "N/A";
    $id = $row['StudentID'] ?? "N/A";
    $phone = $row['Audience_Phone'] ?? "N/A";
    $gender = $row['GenderType'] ?? "N/A";
    $image = $row['Audience_Image'] ?? "../assets/imgs/jusmine.png"; // ใช้ภาพโปรไฟล์เริ่มต้นถ้าไม่มีภาพ
} else {
    die("User not found!");
}

// ดึงข้อมูลอีเวนต์ทั้งหมดจากตาราง Event
$sql_event = "SELECT * FROM Event";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->execute();
$event_result = $stmt_event->get_result();

// ตรวจสอบว่ามีอีเวนต์หรือไม่
$events = [];
if ($event_result->num_rows > 0) {
    while ($event_row = $event_result->fetch_assoc()) {
        $event_id = $event_row['EventID'];
        $event_name = $event_row['Event_Name'];
        $event_date = $event_row['Event_Date'];  // รับค่า event_date จากผลลัพธ์
        $event_time = $event_row['Event_Time'];
        if ($event_date) {
            $event_date_formatted = date("d F Y", strtotime($event_date));  // วันที่ในรูปแบบ "01 January 2025"
        } else {
            $event_date_formatted = "No date available";  // ถ้าไม่มีวันที่ในข้อมูล
        }

        if ($event_time) {
            $event_time_formatted = date("H:i", strtotime($event_time));  // เวลาในรูปแบบ "18:00"
        } else {
            $event_time_formatted = "No time available";  // ถ้าไม่มีเวลาในข้อมูล
        }
        $event_location = $event_row['Event_Location'];
        $event_detail = $event_row['Event_Detail'];
        $event_picture = $event_row['Event_Picture'];
        $event_price = $event_row['Event_Price'];
        $price_display = ($event_price == 0) ? "Free" : $event_price;
        $event_id = $event_row['EventID'];
        // สร้างอาร์เรย์อีเวนต์
        $events[] = [
            'event_id' => $event_row['EventID'],
            'event_name' => $event_row['Event_Name'],
            'event_date' => $event_row['Event_Date'],
            'event_time' => $event_row['Event_Time'],
            'event_date_formatted' => $event_date_formatted,
            'event_time_formatted' => $event_time_formatted,
            'event_location' => $event_row['Event_Location'],
            'event_detail' => $event_row['Event_Detail'],
            'event_picture' => $event_row['Event_Picture'],
            'event_price' => $event_row['Event_Price'],
            'price_display' => $price_display,
        ];

        // ตรวจสอบราคาอีเวนต์

    }
} else {
    echo "No events found.";
}
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- font icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="../assets/vendors/themify-icons/css/themify-icons.css">
    <!-- meyao -->
    <link rel="stylesheet" href="../assets/css/meyawo.css">
    <!-- font-awesome icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Material icons-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>EventNU</title>

</head>
<body class="navbar-body">
    <nav class="navbar-container">
        <div class="navbar-content">
            <div class="navbar-logo">
                <a href="home.php">EVENT NU</a>
            </div>
            <div class="navbar-popup-navbar" id="popupNavbar">
                <div class="navbar-search-container-popup">
                    <input type="text" class="form-control" placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน">
                </div>
                <button class="navbar-close-popup" id="closePopupNavbar"><i class="bi bi-x"></i></button>
            </div>

            <div class="navbar-popup-overlay" id="popupOverlay"></div>

            <div class="navbar-popup" id="searchPopup">

                <div class="navbar-popup-header-recommend">
                    <h3>แนะนำ</h3>
                </div>
                <div class="navbar-popup-content">
                    <div class="navbar-event-list">
                        <?php
                        // เช็คว่าเรามีอีเวนต์หรือไม่
                        if (count($events) > 0) {
                            // วนลูปแสดงอีเวนต์
                            foreach ($events as $event) {
                                // เก็บข้อมูลจากอีเวนต์
                                $event_date = $event['event_date_formatted'];
                                $event_name = $event['event_name'];
                                $event_location = $event['event_location'];
                                $event_picture = $event['event_picture'];
                                $event_price = $event['price_display'];
                                $event_id = $event['event_id'];

                                // สร้างปุ่มที่แสดงราคา
                                $button_class = "navbar-event-button";  // ไม่ต้องสนใจราคาบัตร
                                $button_text = "ดูรายละเอียด";  // แสดงข้อความ "ดูรายละเอียด" ตลอด

                        ?>
                                <div class="navbar-event-card">
                                    <img src="<?php echo $event_picture; ?>" alt="<?php echo $event_name; ?>">
                                    <div class="navbar-event-info">
                                        <p class="navbar-event-date"><i class="bi bi-calendar"></i> <?php echo $event_date; ?></p>
                                        <h4><?php echo $event_name; ?></h4>
                                        <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> <?php echo $event_location; ?></p>
                                        <a href="details.php?event_id=<?php echo $event['event_id']; ?>">
                                            <button class="<?php echo $button_class; ?>"><?php echo $button_text; ?></button>
                                        </a>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<p>No events available.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="navbar-search-container">
                <input type="text" class="form-control navbar-search-bar"
                    placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน" id="searchInput" readonly>

            </div>


            <ul class="navbar-nav navbar-menu-items">
                <li class="navbar-nav-item">
                    <a class="nav-link" href="home.php" data-target="home">หน้าแรก</a>
                </li>
                <li class="navbar-nav-item">
                    <a class="nav-link" href="followrequest.php" data-target="home">จัดอีเว้นท์</a>
                </li>
            </ul>
            <div class="nav-right">
                <button class="notification-button" id="notificationButton">
                    <i class="fi fi-rr-bell"></i>
                </button>
                <div class="notification-panel" id="notificationPanel">
                    <h5>การแจ้งเตือน</h5>
                    <hr>
                    <div class="notification-empty">
                        <i class="bi bi-envelope" style="font-size: 50px; color: #666;"></i>
                        <p>ไม่มีการแจ้งเตือน</p>
                        <small>ขณะนี้ยังไม่มีการแจ้งเตือนถึงคุณ</small>
                    </div>
                </div>
                <button class="user-button" id="userButton">
                    <img src="<?php echo $image; ?>" alt="Profile" class="profile-img">
                    <span><?php echo $first_name; ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="<?php echo $image; ?>" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo $first_name; ?><br><small><?php echo $email; ?></small></p>
                        </div>
                    </div>
                    <hr>
                    <a href="ticketsoon.php">บัตรของฉัน</a>
                    <a href="buy.php">คำสั่งซื้อของฉัน</a>
                    <a href="favorite.php">งานที่ติดตาม</a>
                    <a href="private.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="private-setting-container">
        <div class="private-setting-container-data">
            <div class="private-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="private-profile-img"></div>
                <h2><?php echo $first_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>

            <div class="private-setting-menu">
                <a href="ticketsoon.php" class="private-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    บัตรของฉัน</a>
                <a href="buy.php" class="private-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อของฉัน</a>
                <a href="favorite.php" class="private-setting-menu-item"><i class="bi bi-star"></i> อีเว้นท์ที่ติดตาม</a>
                <a href="private.php" class="private-setting-menu-item active"><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="private-setting-menu-item private-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="private-setting-container-extra-info">
            <h4>ข้อมูลส่วนตัว</h4>
            <div class="private-profile-header">
                <div class="private-profile-img-container">
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="private-profile-img-large"
                        id="profileImage">
                </div>
                <div class="private-profile-info">
                    <a href="#" class="private-edit-button-info" id="changeProfileButton">
                        <i class="bi bi-pencil-square"></i> เปลี่ยนภาพโปรไฟล์
                    </a>
                    <input type="file" id="profileInput" accept="image/*" style="display: none;">
                    <p class="private-email-text"><?php echo $email; ?></p>
                </div>
            </div>

            <div class="private-personal-info" id="personalInfo">
                <div class="private-info-header">
                    <h5><i class="bi bi-person"></i> ข้อมูลส่วนตัว</h5>
                    <a href="#" class="private-edit-button-personal-info" id="editButton"><i
                            class="bi bi-pencil-square"></i>
                        แก้ไข</a>
                </div>
                <p>ชื่อ-นามสกุล: <?php echo $first_name; ?> <?php echo $last_name; ?></p>
                <p>รหัสนิสิต: <?php echo $id; ?></p>
                <p>วันเกิด: 28/02/2004</p>
                <p>เพศ: <?php echo $gender; ?></p>
                <p>เบอร์โทรศัพท์มือถือ: <?php echo $phone; ?></p>
            </div>


            <div class="private-edit-form" id="editForm" style="display: none;">
                <h5>แก้ไขข้อมูลส่วนตัว</h5>
                <label>ชื่อจริง</label>
                <input type="text" id="firstName" value="หนูตาแบ๊ว">

                <label>นามสกุล</label>
                <input type="text" id="lastName" value="นะคะ">

                <label>รหัสนิสิต</label>
                <input type="text" id="studentId" maxlength="8" oninput="validateStudentId(this)" value="65314669">
                <small class="error-message" id="studentIdError"></small>


                <label>วันเกิด</label>
                <input type="date" id="birthDate" value="2004-02-28" onkeydown="return false"
                    onclick="this.showPicker()">

                <label>เพศ</label>
                <select id="gender">
                    <option value="หญิง" selected>หญิง</option>
                    <option value="ชาย">ชาย</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>

                <label>เบอร์โทรศัพท์มือถือ</label>
                <input type="text" id="phoneNumber" value="095-169-1073" maxlength="12"
                    oninput="formatPhoneNumber(this)" placeholder="000-000-0000">
                <small class="error-message" id="phoneError"></small>


                <button id="cancelButton">ย้อนกลับ</button>
                <button id="saveButton" disabled>บันทึก</button>
            </div>


            <div class="private-personal-info" id="personalInfoPass">
                <div class="private-info-header">
                    <h5><i class="bi bi-lock"></i> รหัสผ่าน</h5>
                    <a href="#" class="private-edit-button-personal-info" id="changePasswordButton">
                        <i class="bi bi-pencil-square"></i>เปลี่ยนรหัสผ่าน
                    </a>
                </div>
            </div>

            <div class="private-edit-form" id="passwordForm" style="display: none;">
                <h5>เปลี่ยนรหัสผ่าน</h5>
                <label for="confirmPassword">ยืนยันรหัสผ่านเก่า:</label>
                <input type="password" id="OldPassword" onkeyup="validatePassword()" required>
                <p id="message"></p>

                <label for="newPassword">รหัสผ่านใหม่:</label>
                <input type="password" id="newPassword" required>

                <label for="confirmPassword">ยืนยันรหัสผ่าน:</label>
                <input type="password" id="confirmPassword" required>

                <p id="message"></p>
                <button id="cancelPasswordButton">ย้อนกลับ</button>
                <button id="savePassword">บันทึก</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <script>
        //ฟอร์ม
        document.addEventListener("DOMContentLoaded", function () {
            const editButton = document.getElementById("editButton");
            const personalInfo = document.getElementById("personalInfo");
            const editForm = document.getElementById("editForm");
            const cancelButton = document.getElementById("cancelButton");
            const saveButton = document.getElementById("saveButton");

            // เมื่อกด "แก้ไข" ให้ซ่อนข้อมูลเดิมและแสดงฟอร์ม
            editButton.addEventListener("click", function (e) {
                e.preventDefault();
                personalInfo.style.display = "none";
                editForm.style.display = "block";
            });

            // เมื่อกด "ย้อนกลับ" ให้ซ่อนฟอร์มและแสดงข้อมูลเดิม
            cancelButton.addEventListener("click", function () {
                editForm.style.display = "none";
                personalInfo.style.display = "block";
            });

            // เมื่อกด "บันทึก"
            saveButton.addEventListener("click", function () {
                // รับค่าที่แก้ไข
                const firstName = document.getElementById("firstName").value;
                const lastName = document.getElementById("lastName").value;
                const studentId = document.getElementById("studentId").value;
                const birthDateValue = document.getElementById("birthDate").value;
                const gender = document.getElementById("gender").value;
                const phoneNumber = document.getElementById("phoneNumber").value;

                // แปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น DD/MM/YYYY
                let formattedBirthDate = "";
                if (birthDateValue) {
                    const dateParts = birthDateValue.split("-");
                    formattedBirthDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                }

                // อัปเดตข้อมูลที่แสดง
                personalInfo.innerHTML = `
            <div class="private-info-header">
                <h5>ข้อมูลส่วนตัว</h5>
                <a href="#" class="edit-button-personal-info" id="editButton"><i class="bi bi-pencil-square"></i> แก้ไข</a>
            </div>
            <p>ชื่อ-นามสกุล: ${firstName} ${lastName}</p>
            <p>รหัสนิสิต: ${studentId}</p>
            <p>วันเกิด: ${formattedBirthDate}</p>
            <p>เพศ: ${gender}</p>
            <p>เบอร์โทรศัพท์มือถือ: ${phoneNumber}</p>
        `;

                // ซ่อนฟอร์มและแสดงข้อมูลที่อัปเดต
                editForm.style.display = "none";
                personalInfo.style.display = "block";

                // ต้องเพิ่ม event listener ใหม่ให้ปุ่มแก้ไขที่ถูกสร้างขึ้นใหม่
                document.getElementById("editButton").addEventListener("click", function (e) {
                    e.preventDefault();
                    personalInfo.style.display = "none";
                    editForm.style.display = "block";
                });
            });
        });

        //เช็คข้อมูลว่ากรอกถูกมั้ย
        function validateStudentId(input) {
            input.value = input.value.replace(/\D/g, '').slice(0, 8); // รับเฉพาะตัวเลข และจำกัด 8 ตัว
            const errorMessage = document.getElementById("studentIdError");

            if (input.value.length < 8) {
                errorMessage.textContent = "กรุณากรอกให้ครบ 8 ตัวเลข";
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }

            validateForm();
        }
        function formatPhoneNumber(input) {
            let phone = input.value.replace(/\D/g, '').slice(0, 10); // รับเฉพาะตัวเลข และจำกัด 10 ตัว
            input.value = phone.length > 6 ? `${phone.slice(0, 3)}-${phone.slice(3, 6)}-${phone.slice(6)}` : phone.length > 3 ? `${phone.slice(0, 3)}-${phone.slice(3)}` : phone;

            const errorMessage = document.getElementById("phoneError");

            if (phone.length < 10) {
                errorMessage.textContent = "กรุณากรอกเบอร์โทรศัพท์มือถือให้ถูกต้อง";
                errorMessage.style.display = "block";
            } else {
                errorMessage.style.display = "none";
            }

            validateForm();
        }
        function validateForm() {
            const firstName = document.getElementById("firstName").value.trim();
            const lastName = document.getElementById("lastName").value.trim();
            const studentId = document.getElementById("studentId").value.trim();
            const birthDate = document.getElementById("birthDate").value.trim();
            const phoneNumber = document.getElementById("phoneNumber").value.replace(/\D/g, '').trim();

            const isValid = firstName && lastName && studentId.length === 8 && birthDate && phoneNumber.length === 10;
            document.getElementById("saveButton").disabled = !isValid;
        }

        //ฟอร์มแก้ไขข้อมูล
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("firstName").addEventListener("input", validateForm);
            document.getElementById("lastName").addEventListener("input", validateForm);
            document.getElementById("studentId").addEventListener("input", validateStudentId);
            document.getElementById("birthDate").addEventListener("change", validateForm);
            document.getElementById("phoneNumber").addEventListener("input", formatPhoneNumber);
        });

        //เปลี่ยนรูปโปรไฟล์
        document.addEventListener("DOMContentLoaded", function () {
            const changeProfileButton = document.getElementById("changeProfileButton");
            const profileInput = document.getElementById("profileInput");
            const profileImage = document.getElementById("profileImage");

            // เมื่อกดปุ่ม "เปลี่ยนภาพโปรไฟล์" ให้เปิด file picker
            changeProfileButton.addEventListener("click", function (e) {
                e.preventDefault();
                profileInput.click();
            });

            // เมื่อเลือกรูปภาพ ให้แสดงผลที่ profile-img-large
            profileInput.addEventListener("change", function () {
                const file = profileInput.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profileImage.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const editButton = document.getElementById("editButton");
            const personalInfo = document.getElementById("personalInfo");
            const personalInfoPass = document.getElementById("personalInfoPass");
            const editForm = document.getElementById("editForm");
            const cancelButton = document.getElementById("cancelButton");
            const saveButton = document.getElementById("saveButton");
            const changePasswordButton = document.getElementById("changePasswordButton");
            const passwordForm = document.getElementById("passwordForm");
            const cancelPasswordButton = document.getElementById("cancelPasswordButton");

            // แสดงฟอร์มแก้ไขข้อมูลส่วนตัว


            // กดปุ่ม "เปลี่ยนรหัสผ่าน" เพื่อแสดงฟอร์ม(เพิ่ม)
            changePasswordButton.addEventListener("click", function (e) {
                e.preventDefault();
                personalInfoPass.style.display = "none"
                personalInfo.style.display = "block";
                passwordForm.style.display = "block";
            });

            // กดปุ่ม "ยกเลิก" เพื่อซ่อนฟอร์มเปลี่ยนรหัสผ่าน(เพิ่ม)
            cancelPasswordButton.addEventListener("click", function () {
                passwordForm.style.display = "none";
                personalInfo.style.display = "block";
                personalInfoPass.style.display = "block"

            });
            //saveButtonPassword*ต้องเชื่อมdata_base*(เพิ่ม)
            savePassword.addEventListener("click", function (e) {
                if (!validatePassword() || !samePassword()) {
                    e.preventDefault(); // ป้องกันการบันทึกหากรหัสผ่านไม่ผ่านเงื่อนไข
                    alert("รหัสผ่านต้องมีทั้งตัวอักษรและตัวเลข!");
                } else {
                    alert("รหัสผ่านถูกต้อง! บันทึกเรียบร้อย");
                    passwordForm.style.display = "none";
                    personalInfo.style.display = "block";
                    personalInfoPass.style.display = "block"
                }

            });
        });

        // ตรวจสอบรหัสผ่านต้องมีทั้งตัวอักษรและตัวเลข(เพิ่ม)
        function validatePassword() {
            var password = document.getElementById("OldPassword").value;
            var message = document.getElementById("message");

            var hasLetter = /[a-zA-Z]/.test(password);
            var hasNumber = /\d/.test(password);

            if (!hasLetter || !hasNumber) {
                message.innerHTML = "รหัสผ่านต้องมีทั้งตัวอักษรและตัวเลข!";
                message.style.color = "red";
                return false;
            } else {
                message.innerHTML = "";
                message.style.color = "green";
                return true;
            }
        }
        //ตรวจสอบรหัสผ่านใหม่ทั้งสองอันว่าตรงกันไหม(เพิ่ม)
        function samePassword() {
            var NewPass1 = document.getElementById("newPassword").value;
            var NewPass2 = document.getElementById("confirmPassword").value;
            if (NewPass1 == NewPass2) {
                message.innerHTML = "";
                return true;
            } else {
                message.innerHTML = "รหัสผ่านไม่ตรงกัน"
                return false;
            }
        }
        // ส่งข้อความแจ้งเตือน*ยังไม่ได้ใส่(เพิ่ม)
        function checkPassword() {
            var errorMessage = validatePassword(); // เรียก function ตรวจสอบรหัสผ่าน
            var samePassword = samePassword();

            if (errorMessage) {
                alert(errorMessage); // ถ้ามี error ให้แสดง alert
            } else if (samePassword) {
                alert(samePassword);
            } else {
                alert(เปลี่ยนรหัสผ่านเรียบร้อย);
            }
        }
        //Endฟอร์มเปลี่ยนรหัสผ่าน
    </script>

<script>
        document.getElementById("popupNavbar").addEventListener("keyup", function(e) {
            let searchQuery = e.target.value;
            fetch("search_events.php", {
                    method: "POST",
                    body: JSON.stringify({
                        searchQuery: searchQuery
                    }),
                    headers: {
                        "Content-Type": "application/json"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    displayEvents(data.events);
                })
                .catch(error => console.error("Error fetching events:", error));
        });

        function displayEvents(events) {
            let eventList = document.querySelector(".navbar-event-list");
            eventList.innerHTML = ""; // Clear previous results

            if (events.length > 0) {
                events.forEach(event => {
                    let eventCard = `
                    <div class="navbar-event-card">
                        <img src="${event.event_picture}" alt="${event.event_name}">
                        <div class="navbar-event-info">
                            <p class="navbar-event-date"><i class="bi bi-calendar"></i> ${event.event_date}</p>
                            <h4>${event.event_name}</h4>
                            <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ${event.event_location}</p>
                            <a href="details.php?event_id=${event.event_id}">
                                <button class="navbar-event-button ${event.button_class}">${event.button_text}</button>
                            </a>
                        </div>
                    </div>
                `;
                    eventList.innerHTML += eventCard;
                });
            } else {
                eventList.innerHTML = "<p>No events found.</p>";
            }
        }
    </script>
</body>

</html>