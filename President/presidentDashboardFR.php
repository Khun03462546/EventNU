<?php
session_start();
include '../db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Event not found!";
    exit();
}
$email = $_SESSION['email'];
$event_id = $_GET['event_id']; // รับ event_id จาก URL

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลตาม email
$email = $_SESSION['email'];
$sql = "SELECT * FROM president WHERE President_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email); // ส่ง email เป็น parameter ไปใน query
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบผู้ใช้หรือไม่
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['President_firstname'];
    $last_name = $row['President_lastname'];
    $image = $row['President_Image'];
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}
$stmt->close();

// ดึงข้อมูลการลงทะเบียน
$sql = "
    SELECT 
        r.register_id, 
        r.Audience_email, 
        r.EventID,
        r.booking_id,
        DATE_FORMAT(r.register_date, '%Y-%m-%d') AS register_date, 
        r.register_status,
        e.EventID,
        e.Event_Name, 
        e.Event_Price, 
        e.Event_Date, 
        e.Event_Time, 
        e.Event_Location, 
        e.Event_Picture,
        e.Event_Detail, 
        IFNULL(b.ticket_count, 0) AS ticket_count,  
        IFNULL(b.ticket_totalprice, 0) AS ticket_totalprice,  
        b.booking_receipt,
        a.Audience_email,
        a.Audience_FirstName,
        a.Audience_LastName,
        a.FacultyID,
        f.Faculty_Name,
        a.MajorID,
        m.Major_Name,
        a.GenderID,
        g.GenderType, 
        a.Audience_Role
    FROM register r
    LEFT JOIN Event e ON r.EventID = e.EventID
    LEFT JOIN booking b ON r.booking_id = b.booking_id
    LEFT JOIN Audience a ON r.Audience_email = a.Audience_email
    LEFT JOIN Faculty f ON a.FacultyID = f.FacultyID
    LEFT JOIN Major m ON a.MajorID = m.MajorID
    LEFT JOIN Gender g ON a.GenderID = g.GenderID
    WHERE e.EventID = ?;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$registers[$event_id] = []; // ให้แน่ใจว่าเป็นอาร์เรย์ก่อน

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event_date_formatted = $row['Event_Date'] ? date("d F Y", strtotime($row['Event_Date'])) : "No date available";
        $event_time_formatted = $row['Event_Time'] ? date("H:i", strtotime($row['Event_Time'])) : "No time available";
        $price_display = ($row['Event_Price'] == 0) ? "Free" : $row['Event_Price'];

        // เก็บข้อมูลเป็น array ของหลายรายการ
        $registers[$event_id][] = [
            'event_id' => $row['EventID'],
            'register_id' => $row['register_id'],
            'event_name' => $row['Event_Name'],
            'event_date' => $row['Event_Date'],
            'event_time' => $row['Event_Time'],
            'event_date_formatted' => $event_date_formatted,
            'event_time_formatted' => $event_time_formatted,
            'event_location' => $row['Event_Location'],
            'event_detail' => $row['Event_Detail'],
            'event_picture' => $row['Event_Picture'],
            'event_price' => $row['Event_Price'],
            'price_display' => $price_display,
            'ticket_count' => $row['ticket_count'],
            'register_status' => $row['register_status'],
            'ticket_totalprice' => $row['ticket_totalprice'],
            'booking_id' => $row['booking_id'],
            'booking_receipt' => $row['booking_receipt'],
            'audience_email' => $row['Audience_email'],
            'audience_first_name' => $row['Audience_FirstName'],
            'audience_last_name' => $row['Audience_LastName'],
            'audience_role' => $row['Audience_Role'],
            'faculty_name' => $row['Faculty_Name'],
            'major_name' => $row['Major_Name'],
            'gender' => $row['GenderType'],
        ];
    }
} else {
    echo "No registrations found for this event.";
    exit();
}

// ส่งข้อมูลไปยัง JavaScript
echo "<script>";
echo "var registers = " . json_encode($registers[$event_id]) . ";";  // ส่งข้อมูลจาก PHP ไปยัง JavaScript
echo "</script>";

// ตรวจสอบว่ามีข้อมูล event_id หรือไม่
if (!isset($registers[$event_id]) || !is_array($registers[$event_id])) {
    $total_participants = $students = $lecturers = $guests = 0;
} else {
    // คำนวณจำนวนคนทั้งหมด
    $total_participants = count($registers[$event_id]);

    // คำนวณตาม audience_role
    $students = count(array_filter($registers[$event_id], function ($r) {
        return $r['audience_role'] === 'Student';
    }));
    $lecturers = count(array_filter($registers[$event_id], function ($r) {
        return $r['audience_role'] === 'Lecturer';
    }));
    $guests = count(array_filter($registers[$event_id], function ($r) {
        return $r['audience_role'] === 'Guest User';
    }));
}

$sql = "SELECT * 
        FROM favorite f
        INNER JOIN event e ON f.EventId = e.EventID
        WHERE f.EventID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id); // ส่ง event_id ไปใน query
$stmt->execute();
$result = $stmt->get_result();
$events = [];

if ($result->num_rows > 0) {
    // ใช้ while loop เพื่อดึงข้อมูลทุกแถวจากผลลัพธ์
    while ($row = $result->fetch_assoc()) {
        // ดึงข้อมูลของ event
        $event_pictures = $row['Event_Picture'];
        $event_names = $row['Event_Name'];
        $event_dates = $row['Event_Date'];
        $event_times = $row['Event_Time'];
        $event_locations = $row['Event_Location'];
        $event_prices = $row['Event_Price'];
        $event_details = $row['Event_Detail'];
        $event_ids = $row['EventID'];
        $favorite_id = $row['favorite_id']; // ดึง Favorite_id จากตาราง favorite

        // ตรวจสอบว่า event_price เป็น 0 หรือไม่
        if ($event_prices == 0) {
            $price_displays = "Free";
        } else {
            $price_displays = $event_prices;
        }

        // แปลงวันที่เป็นรูปแบบที่ต้องการ
        $event_dates_formatted = date("d F Y", strtotime($event_dates));  // วันที่ในรูปแบบ "01 January 2025"
        $event_times_formatted = date("H:i", strtotime($event_times));  // เวลาในรูปแบบ "18:00"

        // แปลงวันที่เป็น พ.ศ.
        $eventss[] = [
            'pictures' => $event_pictures,
            'names' => $event_names,
            'dates_formatted' => $event_dates_formatted,
            'times_formatted' => $event_times_formatted,
            'locations' => $event_locations,
            'price_displays' => $price_displays,
            'ids' => $event_ids,
            'favorite_id' => $favorite_id,
        ];
    }
}

// คำสั่ง SQL เพื่อดึงจำนวนผู้ที่ถูกใจแยกตามประเภท
$sql_student = "SELECT COUNT(*) AS student_count FROM favorite f
                LEFT JOIN Audience a ON f.Audience_email = a.Audience_email
                WHERE f.EventID = ? AND a.Audience_role = 'Student'";

$sql_lecturer = "SELECT COUNT(*) AS lecturer_count FROM favorite f
                 LEFT JOIN Audience a ON f.Audience_email = a.Audience_email
                 WHERE f.EventID = ? AND a.Audience_role = 'Lecturer'";

$sql_guest = "SELECT COUNT(*) AS guest_count FROM favorite f
              LEFT JOIN Audience a ON f.Audience_email = a.Audience_email
              WHERE f.EventID = ? AND a.Audience_role = 'Guest User'";

// คำนวณจำนวนผู้ที่ถูกใจทั้งหมด
$sql_total = "SELECT COUNT(*) AS total_count FROM favorite f
              WHERE f.EventID = ?";

// Prepare และ execute แต่ละคำสั่ง
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $event_id);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_count = $total_result->fetch_assoc()['total_count'];

$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $event_id);
$stmt_student->execute();
$student_result = $stmt_student->get_result();
$student_count = $student_result->fetch_assoc()['student_count'];

$stmt_lecturer = $conn->prepare($sql_lecturer);
$stmt_lecturer->bind_param("i", $event_id);
$stmt_lecturer->execute();
$lecturer_result = $stmt_lecturer->get_result();
$lecturer_count = $lecturer_result->fetch_assoc()['lecturer_count'];

$stmt_guest = $conn->prepare($sql_guest);
$stmt_guest->bind_param("i", $event_id);
$stmt_guest->execute();
$guest_result = $stmt_guest->get_result();
$guest_count = $guest_result->fetch_assoc()['guest_count'];

// ส่งค่าจำนวนที่ได้ไปยัง JavaScript
echo "<script>
        document.getElementById('totalInterested').innerText = $total_count;
        document.getElementById('studentInterested').innerText = $student_count;
        document.getElementById('lecturerInterested').innerText = $lecturer_count;
        document.getElementById('guestInterested').innerText = $guest_count;
      </script>";

$sql = "SELECT f.feedback_comment, f.feedback_point, f.feedback_option, a.Audience_FirstName, a.Audience_LastName 
      FROM feedback f
      JOIN Audience a ON f.Audience_email = a.Audience_email
      WHERE f.EventID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$feedback_result = $stmt->get_result();

$feedbacks = [];
if ($feedback_result->num_rows > 0) {
    while ($feedback_row = $feedback_result->fetch_assoc()) {
        // คำนวณคะแนนดาวจาก feedback_point
        $rating = 0;
        if ($feedback_row['feedback_point'] >= 15) {
            $rating = 5;
        } elseif ($feedback_row['feedback_point'] >= 12) {
            $rating = 4;
        } elseif ($feedback_row['feedback_point'] >= 9) {
            $rating = 3;
        } elseif ($feedback_row['feedback_point'] >= 6) {
            $rating = 2;
        } else {
            $rating = 1;
        }

        // เก็บข้อมูล feedback และคะแนนดาว
        $feedbacks[] = [
            "username" => $feedback_row['Audience_FirstName'] . " " . $feedback_row['Audience_LastName'],
            "profilePic" => "../assets/imgs/jusmine.png", // ปรับตามเส้นทางของรูปภาพโปรไฟล์
            "rating" => $rating,
            "options" => json_decode($feedback_row['feedback_option'], true),
            "comment" => $feedback_row['feedback_comment'],
        ];
    }
}
// แปลง array ของ feedbacks เป็น JSON และส่งไปยัง JavaScript
$feedbacks_json = json_encode($feedbacks, JSON_UNESCAPED_UNICODE);


// ดึงข้อมูลคณะที่มีอยู่ในระบบ
$sql = "SELECT * FROM Faculty";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$faculties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $faculties[] = [
            'faculty_id' => $row['FacultyID'],
            'faculty_name' => $row['Faculty_Name'],
        ];
    }
} else {
    echo "No faculties found.";
    exit();
}

// คำนวณจำนวนผู้เข้าร่วมในแต่ละคณะ
$faculty_counts = [];
foreach ($faculties as $faculty) {
    $faculty_counts[$faculty['faculty_name']] = 0; // เริ่มต้นที่ 0
}

// คำนวณจำนวนผู้เข้าร่วมในแต่ละคณะจากข้อมูลการลงทะเบียน
foreach ($registers[$event_id] as $register) {
    $faculty_name = $register['faculty_name']; // ค้นหาคณะจากข้อมูลผู้ลงทะเบียน
    if (isset($faculty_counts[$faculty_name])) {
        $faculty_counts[$faculty_name]++;
    }
}

// เตรียมข้อมูลที่ส่งไปให้ JavaScript (กรองเฉพาะคณะที่มีผู้เข้าร่วม)
$faculties_js = array_map(function ($faculty) use ($faculty_counts) {
    $value = $faculty_counts[$faculty['faculty_name']] ?? 0;
    if ($value > 0) { // เฉพาะคณะที่มีคน
        return [
            'name' => $faculty['faculty_name'],
            'value' => $value,
            'color' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.6)', // สุ่มสี
            'border' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 1)' // สุ่มสี
        ];
    }
    return null; // คณะที่ไม่มีผู้เข้าร่วมจะถูกข้าม
}, $faculties);

// กรองค่า null ออกจากอาร์เรย์
$faculties_js = array_filter($faculties_js, function ($faculty) {
    return $faculty !== null;  // กรองออกค่าที่เป็น null
});

// รีเซ็ตดัชนีของอาร์เรย์
$faculties_js = array_values($faculties_js);

// แปลงเป็น JSON
$json_faculties_js = json_encode($faculties_js);

// ตรวจสอบข้อผิดพลาดจาก json_encode()
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON encode error: " . json_last_error_msg();
} else {
    // ใช้ $json_faculties_js ที่แปลงสำเร็จ
    //echo $json_faculties_js;
}

// ดึงข้อมูลจากฐานข้อมูลเกี่ยวกับเพศ (Gender)
$sql = "SELECT * FROM Gender";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$genders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $genders[] = [
            'gender_id' => $row['GenderID'],
            'gender_type' => $row['GenderType'],
        ];
    }
} else {
    echo "No genders found.";
    exit();
}

// คำนวณจำนวนผู้เข้าร่วมตามเพศ
$gender_counts = ['male' => 0, 'female' => 0, 'other' => 0];

// ใช้ข้อมูลจาก register เพื่อคำนวณจำนวนเพศ
foreach ($registers[$event_id] as $register) {
    $gender = strtolower($register['gender']);  // แปลงเป็นตัวพิมพ์เล็ก

    // ตรวจสอบว่าเพศที่ได้เป็นหนึ่งในค่า 'male', 'female', หรือ 'other'
    if (isset($gender_counts[$gender])) {
        $gender_counts[$gender]++;
    } else {
        // ถ้าไม่ใช่ 'male' หรือ 'female' ให้ถือว่าเป็น 'other'
        $gender_counts['other']++;
    }
}

// ส่งข้อมูลไปยัง JavaScript
echo "<script>";
echo "var genderData = " . json_encode($gender_counts) . ";";  // ส่งข้อมูลจาก PHP ไปยัง JavaScript
echo "</script>";
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- font icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css" />
    <link rel="stylesheet" href="../assets/vendors/themify-icons/css/themify-icons.css" />
    <!-- meyao -->
    <link rel="stylesheet" href="../assets/css/meyawo.css" />
    <!-- font-awesome icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <!-- Bootstrap icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />
    <!-- Material icons-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>EventNU</title>
</head>

<body class="navbar-body">
    <nav class="navbar-container">
        <div class="navbar-content">
            <div class="navbar-logo">
                <a href="home.php">EVENT NU</a>
            </div>
            <div class="navbar-search-container">
                <input type="hidden" class="form-control navbar-search-bar" id="searchInput" readonly />
            </div>
            <ul class="navbar-nav navbar-menu-items">
                <li class="navbar-nav-item">
                    <a class="nav-link" href="home.php" data-target="home">หน้าแรก</a>
                </li>
            </ul>
            <div class="nav-right">
                <button class="notification-button" id="notificationButton">
                    <i class="fi fi-rr-bell"></i>
                </button>
                <div class="notification-panel" id="notificationPanel">
                    <h5>การแจ้งเตือน</h5>
                    <hr />
                    <div class="notification-empty">
                        <i class="bi bi-envelope" style="font-size: 50px; color: #666"></i>
                        <p>ไม่มีการแจ้งเตือน</p>
                        <small>ขณะนี้ยังไม่มีการแจ้งเตือนถึงคุณ</small>
                    </div>
                </div>
                <button class="user-button" id="userButton">
                    <img src="<?php echo $image; ?>" alt="Profile" class="profile-img" />
                    <span><?php echo $first_name; ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="<?php echo $image; ?>" alt="Profile" class="profile-img" />
                        <div>
                            <p class="m-0">
                                <?php echo $first_name; ?><br /><small><?php echo $email; ?></small>
                            </p>
                        </div>
                    </div>
                    <hr />
                    <a href="eventAll.php">อีเว้นท์ทั้งหมด</a>
                    <a href="allOrder.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="addEvent.php">เพิ่มอีเว้นท์</a>
                    <a href="changeRole.php">เปลี่ยนโรล</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr />
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="eventDash-container" style="margin-top: 5px">
        <h1 style="margin-top: 10px">ชื่ออีเว้นท์ : <span id="eventTitle"><?php echo $registers[$event_id][0]['event_name'] ?></span></h1>
        <div class="eventDash-section eventDash-section1">
            <h2 style="font-size: 20px;">สถิติผู้เข้าร่วม</h2>
            <div class="eventDash-stat-card">
                <div class="eventDash-icon">
                    <span>👥</span>
                </div>
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;"><?php echo $total_participants; ?></h3>
                    <p>ทั้งหมด</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-icon">
                    <span>🎓</span>
                </div>
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;"><?php echo $students; ?></h3>
                    <p>นิสิต</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-icon">
                    <span>👨‍🏫</span>
                </div>
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;"><?php echo $lecturers; ?></h3>
                    <p>อาจารย์</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-icon">
                    <span>🏢</span>
                </div>
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;"><?php echo $guests; ?></h3>
                    <p>บุคคลทั่วไป</p>
                </div>
            </div>
        </div>
        <div class="eventDash-section">
            <h2 style="font-size: 20px;">สถิติผู้ที่ถูกใจ</h2>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="totalInterested"><?php echo $total_count; ?></h3>
                    <p>ทั้งหมด</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="studentInterested"><?php echo $student_count; ?></h3>
                    <p>นิสิต</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="lecturerInterested"><?php echo $lecturer_count; ?></h3>
                    <p>อาจารย์</p>
                </div>
            </div>

            <div class="eventDash-stat-card">
                <div class="eventDash-stat-info">
                    <h3 style="font-size: 20px;" id="guestInterested"><?php echo $guest_count; ?></h3>
                    <p>บุคคลทั่วไป</p>
                </div>
            </div>
        </div>


        <div class="eventDash-section eventDash-section2">
            <div class="eventDash-dashboard">
                <div class="eventDash-card1">
                    <h3>คณะที่เข้าร่วม</h3>
                    <label for="facultySelect">เลือกคณะ:</label>
                    <select id="facultySelect" onchange="updateFacultyChart()">
                        <option value="all">ทั้งหมด</option>
                    </select>
                    <div id="eventDash-top5Faculties"></div>
                    <canvas id="eventDash-lineChart" width="400" height="200"></canvas>
                </div>
                <div class="eventDash-card2">
                    <h3>เพศ</h3>
                    <label for="gender-select">เลือกเพศ:</label>
                    <select id="gender-select" onchange="updateGenderChart()">
                        <option value="all">ทั้งหมด</option>
                        <option value="male">ชาย</option>
                        <option value="female">หญิง</option>
                        <option value="other">อื่นๆ</option>
                    </select>

                    <div id="totalAmount"></div>
                    <canvas id="eventDash-donutChart"></canvas>
                </div>
            </div>
        </div>
        <div class="eventDash-section eventDash-section3">
            <h2>รายได้</h2>
            <div class="eventDash-filter">
                <label for="role-select">เลือกกลุ่ม:</label>
                <select id="role-select" onchange="filterByRole()">
                    <option value="all">ทั้งหมด</option>
                    <option value="student">นิสิต</option>
                    <option value="lecturer">อาจารย์</option>
                    <option value="guest">บุคคลทั่วไป</option>
                </select>
            </div>
            <div id="total-amount-summary">
                <p style="font-family: 'itim';">ยอดรวมทั้งหมด: <span id="total-amount">0</span> บาท</p>
            </div>

            <div class="eventDash-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>โรล</th>
                            <th>อีเมลของผู้เข้าชม</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>จำนวนบัตรที่ซื้อ</th>
                            <th>ยอดเงินรวม</th>
                            <th>หลักฐานการโอน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registers[$event_id] as $register): ?>
                            <tr class="<?= strtolower($register['audience_role']); ?>">
                                <td><?= ucfirst($register['audience_role']); ?></td>
                                <td><?= $register['audience_email']; ?></td>
                                <td><?= $register['audience_first_name'] . ' ' . $register['audience_last_name']; ?></td>
                                <td><?= $register['ticket_count']; ?></td>
                                <td><span class="amount"><?= number_format($register['ticket_totalprice']); ?></span></td>
                                <td>
                                    <button class="eventDash-view-slip" data-slid="slip<?= $register['register_id']; ?>"
                                        data-receipt="<?= $register['booking_receipt']; ?>">
                                        จ่ายแล้ว
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Popup สำหรับสลิปโอนเงิน -->
            <?php foreach ($registers[$event_id] as $register): ?>
                <div id="slip<?= $register['register_id']; ?>" class="eventDash-payment-popup">
                    <div class="eventDash-popup-content">
                        <!-- แสดงหลักฐานการโอนเงินจาก booking_receipt -->
                        <img src="<?= $register['booking_receipt']; ?>" alt="Payment Slip" />
                        <button class="eventDash-close-btn">Close</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="eventDash-affairsdashboard-section3">
            <h3>ความคิดเห็นจากผู้เข้าร่วม</h3>
            <label for="filterStars">กรองตามดาว:</label>
            <select id="filterStars">
                <option value="all">แสดงทั้งหมด</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="2">⭐⭐</option>
                <option value="1">⭐</option>
            </select>
            <div id="eventDash-commentsContainer"></div>
        </div>
    </div>
    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const feedbacks = <?php echo $feedbacks_json; ?>; // รับค่าจาก PHP

            function generateStars(starCount) {
                let starsHTML = "";
                for (let i = 1; i <= 5; i++) {
                    if (i <= starCount) {
                        starsHTML += '<i class="fas fa-star"></i>';
                    } else {
                        starsHTML += '<i class="far fa-star"></i>';
                    }
                }
                return starsHTML;
            }

            function displayComments(filter = "all") {
                const container = document.getElementById("eventDash-commentsContainer");
                container.innerHTML = "";

                const filteredComments =
                    filter === "all" ?
                    feedbacks :
                    feedbacks.filter((comment) => comment.rating === parseInt(filter));

                if (filteredComments.length === 0) {
                    container.innerHTML =
                        '<p style="text-align: center; color: #ffa726">ไม่มีคอมเมนต์ที่ตรงกับจำนวนดาวนี้</p>';
                    return;
                }

                // วนลูปแสดงคอมเมนต์
                filteredComments.forEach((comment) => {
                    const commentBox = document.createElement("div");
                    commentBox.classList.add("aFeedback-comment-box");

                    // ตรวจสอบว่ามี options หรือไม่ แล้วแปลงเป็นข้อความ HTML
                    const optionsText = comment.options ?
                        Object.entries(comment.options)
                        .map(([key, value]) => `${key}: ${value}`)
                        .join("<br>") :
                        "";

                    commentBox.innerHTML = `
            <img src="${comment.profilePic}" alt="Profile Picture" class="aFeedback-profile-pic">
            <div class="aFeedback-comment-content">
                <span class="aFeedback-username">${comment.username}</span>
                <div class="aFeedback-rating">
                    ${generateStars(comment.rating)}
                </div>
                <div class="aFeedback-comment-text-user">
                    ${optionsText}
                    <div class="aFeedback-comment-bubble">${comment.comment}</div>
                </div>
            </div>
            `;

                    container.appendChild(commentBox);
                });
            }

            // เรียกใช้ฟังก์ชันแสดงคอมเมนต์ตามค่าเริ่มต้น
            displayComments();

            // กรองตามดาว
            document.getElementById("filterStars").addEventListener("change", function() {
                displayComments(this.value);
            });
        });


        function filterByRole() {
            const selectedRole = document.getElementById('role-select').value;
            let totalAmount = 0;

            // ดึงแถวทั้งหมดในตาราง
            const rows = document.querySelectorAll('tbody tr');

            // ลูปผ่านทุกแถวเพื่อตรวจสอบโรลและคำนวณยอดรวม
            rows.forEach(row => {
                const role = row.classList[0]; // รับค่า class ซึ่งคือ "student", "lecturer", หรือ "guest"
                const amount = parseFloat(row.querySelector('.amount').textContent);

                // ถ้ากลุ่มที่เลือกคือ 'all' หรือกลุ่มที่ตรงกับโรลในแถว
                if (selectedRole === 'all' || role === selectedRole) {
                    row.style.display = ""; // แสดงแถวนี้
                    totalAmount += amount; // คำนวณยอดรวม
                } else {
                    row.style.display = "none"; // ซ่อนแถวนี้
                }
            });

            // อัปเดตยอดรวมในหน้าเว็บ
            document.getElementById('total-amount').textContent = totalAmount.toFixed(0);
        }

        // เรียกฟังก์ชันเพื่อคำนวณยอดรวมตามค่าเริ่มต้น (กรณีเลือก "ทั้งหมด")
        filterByRole();

        // เปิด Popup เมื่อคลิกปุ่ม "จ่ายแล้ว"
        document.querySelectorAll(".eventDash-view-slip").forEach((button) => {
            button.addEventListener("click", (e) => {
                const slipId = e.target.getAttribute("data-slid");
                const bookingReceipt = e.target.getAttribute("data-receipt"); // ดึงข้อมูล booking_receipt

                // แสดง popup และปรับปรุงเนื้อหาภายในให้ตรงกับหลักฐานการโอน
                const popup = document.getElementById(slipId);
                popup.querySelector("img").src = bookingReceipt; // ตั้งค่าภาพที่จะแสดง

                // แสดง popup
                popup.style.display = "flex";
            });
        });

        // ปิด Popup เมื่อคลิกปุ่ม Close
        document.querySelectorAll(".eventDash-close-btn").forEach((button) => {
            button.addEventListener("click", (e) => {
                e.target.closest(".eventDash-payment-popup").style.display = "none";
            });
        });



        // รับข้อมูล JSON จาก PHP
        const faculties_js = <?php echo $json_faculties_js; ?>;

        // ตรวจสอบข้อมูล
        console.log(faculties_js);

        // ฟังก์ชันเพื่อเพิ่มตัวเลือกคณะใน select
        const selectElement = document.getElementById("facultySelect");
        faculties_js.forEach((faculty) => {
            const option = document.createElement("option");
            option.value = faculty.name;
            option.textContent = faculty.name;
            selectElement.appendChild(option);
        });

        // ข้อมูลสำหรับกราฟ
        const labels = faculties_js.map(faculty => faculty.name);
        const data = faculties_js.map(faculty => faculty.value);
        const colors = faculties_js.map(faculty => faculty.color);
        const borders = faculties_js.map(faculty => faculty.border);

        const ctx = document.getElementById("eventDash-lineChart").getContext("2d");

        let facultyChart;

        // ฟังก์ชันสำหรับสร้างกราฟ
        function createFacultyChart(filteredFaculties) {
            if (facultyChart) facultyChart.destroy();
            facultyChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: filteredFaculties.map((f) => f.name),
                    datasets: [{
                        label: `จำนวนผู้เข้าร่วม (${filteredFaculties.reduce((sum, f) => sum + f.value, 0)})`,
                        data: filteredFaculties.map((f) => f.value),
                        backgroundColor: filteredFaculties.map((f) => f.color),
                        borderColor: filteredFaculties.map((f) => f.border),
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1,
                                callback: (value) => value + " คน", // แสดงจำนวนคน
                            },
                            title: {
                                display: true,
                                text: "จำนวนคน"
                            },
                        }
                    }
                }
            });
        }

        // ฟังก์ชันสำหรับอัปเดตกราฟตามคณะที่เลือก
        function updateFacultyChart() {
            const selectedFaculty = selectElement.value;
            const filteredData = selectedFaculty === "all" ? faculties_js : faculties_js.filter((f) => f.name === selectedFaculty);
            createFacultyChart(filteredData);
        }

        // สร้างกราฟเริ่มต้น
        createFacultyChart(faculties_js);

        // ========================== กราฟโดนัทเพศ ==========================
        console.log(genderData); // ตรวจสอบข้อมูลที่ได้รับจาก PHP

        let genderChart;

        function updateGenderChart() {
            const selectedGender = document.getElementById("gender-select").value;
            let filteredData, labels, backgroundColors;

            // กรองข้อมูลตามเพศที่เลือก
            if (selectedGender === "all") {
                filteredData = Object.values(genderData);
                labels = ["ชาย", "หญิง", "อื่นๆ"];
                backgroundColors = ["#4A90E2", "#F5C147", "#F57E77"];
            } else {
                filteredData = [genderData[selectedGender]];
                labels = [selectedGender === "male" ? "ชาย" : selectedGender === "female" ? "หญิง" : "อื่นๆ"];
                backgroundColors = [selectedGender === "male" ? "#4A90E2" : selectedGender === "female" ? "#F5C147" : "#F57E77"];
            }

            // แสดงจำนวนผู้เข้าร่วมใน `totalAmount`
            document.getElementById("totalAmount").innerHTML = `<strong style="font-weight: lighter;">จำนวน: ${filteredData.reduce((acc, curr) => acc + curr, 0).toLocaleString()} คน</strong>`;

            // ถ้ามีกราฟอยู่แล้ว ให้ลบกราฟเก่าออกก่อนแล้วสร้างกราฟใหม่
            if (genderChart) genderChart.destroy();

            const donutCtx = document.getElementById("eventDash-donutChart").getContext("2d");
            genderChart = new Chart(donutCtx, {
                type: "doughnut",
                data: {
                    labels: labels,
                    datasets: [{
                        data: filteredData,
                        backgroundColor: backgroundColors
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: "bottom"
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return `${tooltipItem.label}: ${tooltipItem.raw.toLocaleString()} คน`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // เรียกใช้ updateGenderChart เมื่อเริ่มต้นเพื่อแสดงกราฟทั้งหมด
        updateGenderChart();
    </script>
    <script src="../assets/js/navbar.js"></script>
</body>

</html>