<?php
session_start(); // เริ่มต้น session
include "../db.php";

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบ event_id ก่อนใช้
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo "Invalid Event ID!";
    exit();
}

$event_id = intval($_GET['event_id']); // แปลงให้เป็นตัวเลข ป้องกัน SQL Injection
$email = $_SESSION['email']; // อีเมลของผู้ใช้ที่ล็อกอิน

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลตาม email
$sql = "SELECT 
            a.*, 
            f.Faculty_Name, 
            m.Major_Name 
        FROM Audience a
        LEFT JOIN Faculty f ON a.FacultyID = f.FacultyID
        LEFT JOIN Major m ON a.MajorID = m.MajorID
        WHERE a.Audience_email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'];
    $last_name = $row['Audience_LastName'];
    $faculty = $row['Faculty_Name'];
    $major = $row['Major_Name'];
    $role = $row['Audience_Role'];
    $id = $row['StudentID'];
    $phone = $row['Audience_Phone'];
    $image = $row['Audience_Image'];
} else {
    echo "User not found!";
    exit();
}

// ดึงข้อมูลของ Event ตาม EventID
$sql = "SELECT * FROM Event WHERE EventID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $event_picture = $row['Event_Picture'];
    $event_name = $row['Event_Name'];
    $event_date = $row['Event_Date'];
    $event_time = $row['Event_Time'];
    $event_location = $row['Event_Location'];
    $event_price = $row['Event_Price'];
    $event_detail = $row['Event_Detail'];
    $event_id = $row['EventID'];
    $eventTypeRe = $row['TypeRegister'];
} else {
    echo "No event found.";
    exit();
}

// ✅ บันทึกข้อมูลลงตาราง register เมื่อกดปุ่ม Confirm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_register'])) {
    // ตรวจสอบว่าลงทะเบียนแล้วหรือยัง
    $check_sql = "SELECT * FROM register WHERE Audience_email = ? AND EventID = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $event_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('You have already registered for this event!');</script>";
    } else {
        // กำหนดค่า register_status
        $register_status = ($event_price == 0) ? NULL : 'pending';

        $booking_id = ($event_price == 0) ? NULL : uniqid("booking_");

        // บันทึกข้อมูลลงตาราง register
        $insert_sql = "INSERT INTO register (Audience_email, EventID, Register_date, register_status, booking_id) 
                       VALUES (?, ?, NOW(), ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("siss", $email, $event_id, $register_status,$booking_id);

        if ($insert_stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='buy.php';</script>";
        } else {
            echo "<script>alert('Registration failed, please try again!');</script>";
        }
    }
}

// ดึงข้อมูลอีเวนต์ทั้งหมดจากตาราง Event
$sql_event = "SELECT * FROM Event";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->execute();
$event_result = $stmt_event->get_result();

// ตรวจสอบว่ามีอีเวนต์หรือไม่
$eventss = [];
if ($event_result->num_rows > 0) {
    while ($event_row = $event_result->fetch_assoc()) {
        $event_ids = $event_row['EventID'];
        $event_names = $event_row['Event_Name'];
        $event_dates = $event_row['Event_Date'];  // รับค่า event_date จากผลลัพธ์
        $event_times = $event_row['Event_Time'];
        if ($event_dates) {
            $event_dates_formatted = date("d F Y", strtotime($event_date));  // วันที่ในรูปแบบ "01 January 2025"
        } else {
            $event_dates_formatted = "No date available";  // ถ้าไม่มีวันที่ในข้อมูล
        }

        if ($event_times) {
            $event_times_formatted = date("H:i", strtotime($event_time));  // เวลาในรูปแบบ "18:00"
        } else {
            $event_times_formatted = "No time available";  // ถ้าไม่มีเวลาในข้อมูล
        }
        $event_locations = $event_row['Event_Location'];
        $event_details = $event_row['Event_Detail'];
        $event_pictures = $event_row['Event_Picture'];
        $event_prices = $event_row['Event_Price'];
        $price_displays = ($event_price == 0) ? "Free" : $event_price;
        $event_ids = $event_row['EventID'];
        // สร้างอาร์เรย์อีเวนต์
        $eventss[] = [
            'event_ids' => $event_row['EventID'],
            'event_names' => $event_row['Event_Name'],
            'event_dates' => $event_row['Event_Date'],
            'event_times' => $event_row['Event_Time'],
            'event_dates_formatted' => $event_date_formatted,
            'event_times_formatted' => $event_time_formatted,
            'event_locations' => $event_row['Event_Location'],
            'event_details' => $event_row['Event_Detail'],
            'event_pictures' => $event_row['Event_Picture'],
            'event_prices' => $event_row['Event_Price'],
            'price_displays' => $price_display,
        ];

        // ตรวจสอบราคาอีเวนต์

    }
} else {
    echo "No events found.";
}

?>
<!DOCTYPE html>
<html lang="en">

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

<body>

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
                        if (count($eventss) > 0) {
                            // วนลูปแสดงอีเวนต์
                            foreach ($eventss as $event) {
                                // เก็บข้อมูลจากอีเวนต์
                                $event_dates = $event['event_dates_formatted'];
                                $event_names = $event['event_names'];
                                $event_locations = $event['event_locations'];
                                $event_pictures = $event['event_pictures'];
                                $event_prices = $event['price_displays'];
                                $event_ids = $event['event_ids'];

                                // สร้างปุ่มที่แสดงราคา
                                $button_class = "navbar-event-button";  // ไม่ต้องสนใจราคาบัตร
                                $button_text = "ดูรายละเอียด";  // แสดงข้อความ "ดูรายละเอียด" ตลอด
                        ?>
                                <div class="navbar-event-card">
                                    <img src="<?php echo $event_pictures; ?>" alt="<?php echo $event_names; ?>">
                                    <div class="navbar-event-info">
                                        <p class="navbar-event-date"><i class="bi bi-calendar"></i> <?php echo $event_dates; ?></p>
                                        <h4><?php echo $event_names; ?></h4>
                                        <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> <?php echo $event_locations; ?></p>
                                        <a href="details.php?event_id=<?php echo $event['event_ids']; ?>">
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
                    <a class="nav-link" href="followrequest.html" data-target="home">จัดอีเว้นท์</a>
                </li>
                <li class="navbar-nav-item dropdown">
                    <button class="nav-link dropdown-toggle" id="eventDropdown">
                        งานแสดง
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="eventDropdown">
                        <li><a class="dropdown-item" href="#">งานทั้งหมด</a></li>
                        <li><a class="dropdown-item" href="#">นันทนาการ</a></li>
                        <li><a class="dropdown-item" href="#">คอนเสิร์ต</a></li>
                        <li><a class="dropdown-item" href="#">ละครเวที</a></li>
                        <li><a class="dropdown-item" href="#">กีฬา</a></li>
                        <li><a class="dropdown-item" href="#">วัฒนธรรม</a></li>
                        <li><a class="dropdown-item" href="#">สุขภาพ</a></li>
                        <li><a class="dropdown-item" href="#">การศึกษา</a></li>
                        <li><a class="dropdown-item" href="#">ประกวด</a></li>
                        <li><a class="dropdown-item" href="#">บันเทิง</a></li>
                        <li><a class="dropdown-item" href="#">ตลาด</a></li>
                        <li><a class="dropdown-item" href="#">อบรม</a></li>
                        <li><a class="dropdown-item" href="#">นิทรรศการ</a></li>
                        <li><a class="dropdown-item" href="#">ครบรอบ</a></li>
                        <li><a class="dropdown-item" href="#">จิตอาสา</a></li>
                    </ul>
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

    <div class="aRegisterForFree-container">
        <div class="aRegisterForFree-event-container">
            <div class="aRegisterForFree-left-side">
                <div class="aRegisterForFree-event-card">
                    <img src="<?php echo $event_picture; ?>" alt="Event Image">
                    <h2>Event : <?php echo $event_name; ?></h2>
                </div>
            </div>

            <div class="aRegisterForFree-right-side">
                <div class="aRegisterForFree-profile-card">
                    <img src="../assets/imgs/jusmine.png" alt="Profile Image">
                    <h3><?php echo $first_name; ?></h3>
                    <div class="aRegisterForFree-event">
                        <div class="aRegisterForFree-event-details">
                            <p><i class="aRegisterForFree-bi bi-person-fill"></i><strong> Name:</strong> <?php echo $first_name; ?></p>
                            <p><i class="aRegisterForFree-bi bi-telephone-fill"></i><strong> Number:</strong> <?php echo $phone; ?></p>
                            <p><i class="aRegisterForFree-bi bi-envelope-fill"></i><strong> Email:</strong> <?php echo $email; ?></p>
                            <p><i class="aRegisterForFree-bi bi-credit-card-fill"></i><strong> Student ID:</strong> <?php echo $id; ?></p>
                            <p><i class="aRegisterForFree-bi bi-building"></i><strong> Faculty:</strong> <?php echo $faculty; ?></p>
                            <p><i class="aRegisterForFree-bi bi-bookmark-fill"></i><strong> Major:</strong> <?php echo $major; ?></p>
                        </div>
                        <div class="aRegisterForFree-btn">
                            <button class="aRegisterForFree-btn-cancel" onclick="cancelActionn()">Cancel</button>
                            <button class="aRegisterForFree-btn-confirm" onclick="openModal()">Confirm</button>
                        </div>
                        <script>
                            function cancelActionn() {
                                document.getElementById('registerModal').classList.remove('active');

                                window.location.href = 'details.php?event_id=' + <?php echo $event_id; ?>;
                            }
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Pop-up Modal -->
    <div class="aRegisterForFree-modal" id="registerModal">
        <div class="aRegisterForFree-modal-dialog">
            <div class="aRegisterForFree-modal-body">
                <p>Do you confirm your registration for this event?</p>
            </div>
            <div class="aRegisterForFree-modal-footer">
                <form method="POST">
                    <button type="button" class="aRegisterForFree-btn-cancel" onclick="cancelAction()">Cancel</button>
                    <button type="submit" name="confirm_register" class="aRegisterForFree-btn-confirm">Confirm</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function cancelAction() {
            document.getElementById('registerModal').classList.remove('active');

            window.location.href = 'registerForFree.php?event_id=' + <?php echo $event_id; ?>;
        }
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
                        <img src="${event.event_pictures}" alt="${event.event_names}">
                        <div class="navbar-event-info">
                            <p class="navbar-event-date"><i class="bi bi-calendar"></i> ${event.event_dates}</p>
                            <h4>${event.event_names}</h4>
                            <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ${event.event_locations}</p>
                            <a href="details.php?event_id=${event.event_ids}">
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

    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- Meyawo js -->
    <script src="../assets/js/meyawo.js"></script>
</body>

</html>