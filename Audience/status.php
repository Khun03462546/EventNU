<?php
session_start(); // เริ่มต้น session
include "../db.php";  // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่มี session, เปลี่ยนเส้นทางไปหน้า login
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลตาม email
$email = $_SESSION['email'];
$sql = "SELECT * FROM Audience WHERE Audience_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email); // ส่ง email เป็น parameter ไปใน query
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบผู้ใช้หรือไม่
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'];
    $last_name = $row['Audience_LastName'];
    $faculty = $row['FacultyID'];  // หรือเพิ่มข้อมูลอื่นๆ ตามต้องการ
    $role = $row['Audience_Role'];
    $image = $row['Audience_Image'];
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}

// ดึงข้อมูล request_event จากฐานข้อมูล
$sql_event = "SELECT * FROM request_event WHERE Audience_email = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("s", $email);
$stmt_event->execute();
$result_event = $stmt_event->get_result();

// ตรวจสอบว่าได้ข้อมูลหรือไม่และเก็บในตัวแปร $events
$events = [];
while ($row_event = $result_event->fetch_assoc()) {
    $events[] = $row_event;
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


$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล

// ฟังก์ชันที่ใช้ในการจัดการขั้นตอน
function stepCompleted($stepIndex, $status)
{
    if ($status == 'completed') {
        return 'completed';  // ทุกขั้นตอนต้อง completed เมื่อสถานะเป็น completed
    }

    // ถ้า status คือ committee, president ให้ทำการส่ง class "completed" เฉพาะในขั้นตอนที่เหมาะสม
    if (($status == 'committee' && $stepIndex <= 1) || ($status == 'president' && $stepIndex <= 2)) {
        return 'completed';
    }

    return '';
}
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
                    <a class="nav-link" href="followrequest.php" data-target="home">จัดอีเว้นท์</a>
                </li>
                <li class="navbar-nav-item dropdown">
                    <button class="nav-link dropdown-toggle" id="eventDropdown">
                        หมวดหมู่
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="eventDropdown">
                        <li><a class="dropdown-item" href="#">อีเว้นท์ทั้งหมด</a></li>
                        <li><a class="dropdown-item" href="#">ละครเวที</a></li>
                        <li><a class="dropdown-item" href="#">งานบุญ</a></li>
                        <li><a class="dropdown-item" href="#">คอนเสิร์ต</a></li>
                        <li><a class="dropdown-item" href="#">ตลาด</a></li>
                        <li><a class="dropdown-item" href="#">กีฬา</a></li>
                        <li><a class="dropdown-item" href="#">การศึกษา</a></li>
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

    <!-- เนื้อหาหลัก -->
    <div class="followrequest-container">
        <button class="followrequest-back-btn" onclick="window.history.back();">
            <i class="bi bi-arrow-left"></i> ย้อนกลับ
        </button>
        <div class="followrequest-profile-section">
            <img src="../assets/imgs/jusmine.png" alt="Profile Picture" class="followrequest-profile-picture">
            <div class="followrequest-profile-info">
                <div class="followrequest-username"><?php echo $first_name ?></div>
                <div class="followrequest-role"><?php echo $role ?></div>
            </div>
        </div>
        <?php
        // สมมุติว่า $events คือ array ที่เก็บข้อมูล request_event จากฐานข้อมูล
        foreach ($events as $request_event) {
            $status = $request_event['request_event_status'];
            $event_name = $request_event['request_event_name'];
            $event_date = $request_event['request_event_date'];
            $event_time = $request_event['request_event_time'];
            $event_picture = $request_event['request_event_picture'];
            $contact_email = $request_event['Audience_email'];
            $event_price = $request_event['request_event_price'];
            $event_location = $request_event['request_event_location'];
        ?>
            <!-- HTML for each event -->
            <div class="container-box-status">
                <!-- Progress Steps -->
                <div class="followrequest-action-section">
                    <div class="followrequest-step <?= stepCompleted(0, $status) ?>">
                        <div class="followrequest-circle2"><i class="followrequest-bi bi-file-earmark-check"></i></div>
                        <span>ยื่น</span>
                    </div>
                    <div class="followrequest-line <?= stepCompleted(1, $status) ?>"></div>

                    <div class="followrequest-step <?= stepCompleted(1, $status) ?>">
                        <div class="followrequest-circle2"><i class="followrequest-bi bi-file-earmark-check"></i></div>
                        <span>ถึงกรรมการ</span>
                    </div>
                    <div class="followrequest-line <?= stepCompleted(2, $status) ?>"></div>

                    <div class="followrequest-step <?= stepCompleted(2, $status) ?>">
                        <div class="followrequest-circle2"><i class="followrequest-bi bi-file-earmark-text"></i></div>
                        <span>คณะบดี</span>
                    </div>
                    <div class="followrequest-line <?= stepCompleted(3, $status) ?>"></div>

                    <div class="followrequest-step <?= stepCompleted(3, $status) ?>">
                        <div class="followrequest-circle3"><i class="followrequest-bi bi-file-earmark-text"></i></div>
                        <span>อนุมัติ</span>
                    </div>
                </div>

                <!-- Event Info -->
                <div class="checkBack-waiting-setting-container-event d-flex align-items-center p-3" data-id="event1">
                    <div class="checkBack-waiting-event-date text-center me-3">
                        <h3 class="mb-0 fw-bold"><?= date('d', strtotime($event_date)) ?></h3>
                        <p class="mb-0"><?= date('M Y', strtotime($event_date)) ?></p>
                        <p class="mb-0"><?= $event_time ?></p>
                    </div>
                    <div class="checkBack-waiting-setting-container-event-Image">
                        <img src="<?= $event_picture ?>" alt="Event Image" class="me-3 rounded" style="width: 100px; height: auto;">
                    </div>
                    <div class="checkBack-waiting-event-details flex-grow-1 d-flex flex-column justify-content-between" style="height: 100%">
                        <h2><?= $event_name ?></h2>
                        <div class="checkBack-waiting-d-flex">
                            <div class="checkBack-waiting-details-container">
                                <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> <?= $event_location ?></p>
                                <p style="text-align: left;"><i class="bi bi-piggy-bank-fill"></i> ราคาบัตร : <?= number_format($event_price, 2) ?> บาท</p>
                                <p style="text-align: left;"><i class="bi bi-person-fill"></i> ข้อมูลผู้ติดต่อ : <?= $contact_email ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- core  -->
    <script src="assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- Meyawo js -->
    <script src="../assets/js/meyawo.js"></script>
    <script src="../assets/js/comment.js"></script>
</body>

</html>