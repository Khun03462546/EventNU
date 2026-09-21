<?php
session_start();
include "../db.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่มี session, เปลี่ยนเส้นทางไปหน้า login
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลตาม email
$email = $_SESSION['email'];
$sql = "SELECT * FROM committee WHERE committee_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email); // ส่ง email เป็น parameter ไปใน query
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบผู้ใช้หรือไม่
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['committee_firstname'];
    $last_name = $row['committee_lastname'];
    $faculty = $row['FacultyID'];  // หรือเพิ่มข้อมูลอื่นๆ ตามต้องการ
    $image = $row['committee_Image']; // เปลี่ยนเป็นชื่อคอลัมน์ที่เก็บภาพ
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}
$sql_event = "SELECT re.*, a.Audience_Image, a.Audience_FirstName, a.Audience_LastName,a.Audience_Role
              FROM request_event re
              LEFT JOIN audience a ON re.Audience_email = a.Audience_email";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->execute();
$event_result = $stmt_event->get_result();

// ตรวจสอบว่ามีอีเวนต์หรือไม่
$requestevents = [];
if ($event_result->num_rows > 0) {
    while ($event_row = $event_result->fetch_assoc()) {
        $event_id = $event_row['request_event_id'];
        $auemail = $event_row['Audience_email'];
        $aufirst_name = $event_row['Audience_FirstName'];
        $aulast_name = $event_row['Audience_LastName'];
        $image = $event_row['Audience_Image'];
        $role = $event_row['Audience_Role'];
        $event_name = $event_row['request_event_name'];
        $event_date = $event_row['request_event_date'];  // รับค่า event_date จากผลลัพธ์
        $event_time = $event_row['request_event_time'];  // รับค่า event_time จากผลลัพธ์
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
        $event_location = $event_row['request_event_location'];
        $event_detail = $event_row['request_event_detail'];
        $event_picture = $event_row['request_event_picture'];
        $event_price = $event_row['request_event_price'];
        $price_display = ($event_price == 0) ? "Free" : $event_price;
        $point = $event_row['request_event_point'];
        $status = $event_row['request_event_status'];
        $registype = $event_row['request_event_type'];
        $requestevents[$event_id] = [
            'event_id' => $event_row['request_event_id'],
            'event_name' => $event_row['request_event_name'],
            'event_date' => $event_row['request_event_date'],
            'event_time' => $event_row['request_event_time'],
            'event_date_formatted' => $event_date_formatted,  // วันที่ที่แปลงแล้ว
            'event_time_formatted' => $event_time_formatted,  // เวลาที่แปลงแล้ว
            'event_location' => $event_row['request_event_location'],
            'event_detail' => $event_row['request_event_detail'],
            'event_picture' => $event_row['request_event_picture'], // ✅ เปลี่ยนให้เป็นตัวพิมพ์เล็ก
            'event_price' => $event_row['request_event_price'],
            'price_display' => $price_display,
            'point' => $event_row['request_event_point'],
            'status' => $event_row['request_event_status'],
            'auemail' => $event_row['Audience_email'],
            'aufirst_name' => $event_row['Audience_FirstName'],
            'aulast_name' => $event_row['Audience_LastName'],
            'registype' => $event_row['request_event_type'],
            'role' => $event_row['Audience_Role'],
            'image' => $event_row['Audience_Image'],
        ];
    }
}

// ในการดึงข้อมูลโหวตจากตาราง vote
$sql_vote = "SELECT v.*, c.committee_firstname, c.committee_lastname 
              FROM vote v
              LEFT JOIN committee c ON v.committee_email = c.committee_email";
$stmt_vote = $conn->prepare($sql_vote);
$stmt_vote->execute();
$vote_result = $stmt_vote->get_result();

// สร้าง array สำหรับเก็บข้อมูลการโหวต
$votes = [];
if ($vote_result->num_rows > 0) {
    while ($vote_row = $vote_result->fetch_assoc()) {
        $vote_id = $vote_row['vote_id'];
        $request_event_id = $vote_row['request_event_id'];  // ใช้ request_event_id ในการเชื่อมโยงข้อมูล
        $comfname = $vote_row['committee_firstname'];
        $comlname = $vote_row['committee_lastname'];
        $vote_status = $vote_row['vote_status'];
        $vote_approved = ($vote_status == 'อนุมัติ') ? 1 : 0;  // แก้ไขการนับ
        // เก็บข้อมูลโหวตใน array โดยใช้ request_event_id เป็น key
        $votes[$request_event_id] = [
            'comfname' => $comfname,
            'comlname' => $comlname,
            'vote_status' => $vote_status,
            'vote_approved' => $vote_approved,
        ];
    }
}

// สมมติว่า $requestevents มีข้อมูลที่ต้องการ
$users = []; // Array สำหรับเก็บข้อมูลผู้ใช้
foreach ($requestevents as $event_id => $requestevent) {
    // ข้อมูลจาก $requestevents
    $event_date = $requestevent['event_date_formatted'];
    $event_time = $requestevent['event_time_formatted'];
    $event_location = $requestevent['event_location'];
    $event_image = $requestevent['event_picture'];
    $aufirst_name = $requestevent['aufirst_name'];
    $aulast_name = $requestevent['aulast_name'];
    $role = $requestevent['role'];
    $auemail = $requestevent['auemail'];
    $request_event_id = $requestevent['event_id'];
    $image = $requestevent['image'];

    // ข้อมูลจาก $votes (ตรวจสอบให้มั่นใจว่า index ตรงกัน)
    $comfname = '';
    $comlname = '';
    $vote_status = '';
    if (isset($votes[$request_event_id])) {
        $vote = $votes[$request_event_id]; // ดึงข้อมูลที่ถูกต้องมาเก็บไว้ในตัวแปร $vote
        $comfname = isset($vote['comfname']) ? $vote['comfname'] : '';
        $comlname = isset($vote['comlname']) ? $vote['comlname'] : '';
        $vote_status = isset($vote['vote_status']) ? $vote['vote_status'] : '';
    } else {
        $vote_status = "Vote not found for this event."; // ถ้าไม่มีข้อมูลการโหวต
    }

    // เพิ่มข้อมูลลงใน array ของ users
    $users[] = [
        'id' => "user" . $request_event_id, // ตั้งชื่อ id โดยใช้ event_id
        'name' => $aufirst_name . ' ' . $aulast_name,
        'role' => $role, // ใช้ชื่อผู้ขอจัดอีเว้นท์เพื่อสร้าง role ตัวอย่าง
        'email' => $auemail, // ใช้ชื่อผู้ขอจัดอีเว้นท์เพื่อสร้าง email ตัวอย่าง
        'eventDate' => $event_date,
        'eventTime' => $event_time,
        'eventLocation' => $event_location,
        'eventImage' => "$event_image",
        'voteStatus' => $vote_status,
        'request_event_id' => $request_event_id,
        'image' => $image,
    ];
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
$conn->close();
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
                    <h3>Recommended</h3>
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
                    <a class="nav-link" href="assessment.php" data-target="assessment">ประเมินอีเว้นท์</a>
                </li>
            </ul>
            <div class="nav-right">
                <button class="notification-button" id="notificationButton">
                    <i class="fi fi-rr-bell"></i>
                </button>
                <div class="notification-panel" id="notificationPanel">
                    <h5>Notifications</h5>
                    <hr>
                    <div class="notification-empty">
                        <i class="bi bi-envelope" style="font-size: 50px; color: #666;"></i>
                        <p>No Notifications</p>
                        <small>You currently have no notifications</small>
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
                    <a href="checkInfo.php">ตรวจสอบเอกสาร</a>
                    <a href="budget.html">งบประมาณ</a>
                    <a href="setting.html">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="checkInfo-container">
        <div class="checkInfo-container-data">
            <div class="checkInfo-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="<?php echo $image; ?>" alt="Profile">
                </div>
                <h2><?php echo $first_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>

            <div class="checkInfo-menu">
                <a href="checkInfo.php" class="checkInfo-menu-item active"><i class="bi bi-file-earmark-fill"></i>
                    ตรวจสอบเอกสาร</a>
                <a href="budget.html" class="checkInfo-menu-item"><i class="bi-file-earmark-pdf"></i>
                    งบประมาณ</a>
                <a href="setting.html" class="checkInfo-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="checkInfo-menu-item checkInfo-logout"><i class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="changeInfo-role-container">
            <h3 style="text-align: left;">ตรวจสอบเอกสาร</h3>
            <div class="changeRole-content">
                <div id="changerole-pending-list">
                    <div class="changeInfo-content">
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/meyawo.js"></script>
    <script>
        // ใช้ข้อมูลที่ส่งมาจาก PHP
        const users = <?php echo json_encode($users); ?>; // แปลง PHP array ไปเป็น JavaScript array

        // ฟังก์ชันในการเติมข้อมูลลงใน pending list
        function populatePendingList() {
            const pendingList = document.getElementById("changerole-pending-list");

            // วนลูปผ่านข้อมูลใน users
            users.forEach(user => {
                const userDiv = document.createElement("div");
                userDiv.className = "changeInfo-content";

                userDiv.innerHTML = `
                <div class="changeInfo-tab-content changeInfo">
                    <div class="changeInfo-setting-container-event d-flex align-items-center p-3">
                        <div class="changeInfo-event-date text-center me-3">
                            <h3 class="mb-0 fw-bold">${user.eventDate.split(' ')[0]}</h3>
                            <p class="mb-0">${user.eventDate.split(' ')[1]}</p>
                            <p class="mb-0">${user.eventTime}</p>
                        </div>
                        <div class="changeInfo-event-image">
                            <img src="${user.eventImage}" alt="Event Image" class="me-3 rounded">
                        </div>
                        <div class="changeInfo-event-details">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h2>${user.name}</h2>
                            </div>
                            <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${user.eventLocation}</p>
                            <div class="changeInfo-role-card" id="${user.id}" data-original-role="${user.role}">
                                <img src="${user.image}" alt="profile">
                                <div class="changerole-role-info">
                                    <strong>${user.name}</strong><br>
                                    <span class="changerole-role-text">${user.role}</span>
                                    <p class="waiting-buyer-email">Email: ${user.email}</p>
                                </div>
                                <a href="detailsInfo.php?request_event_id= ${user.request_event_id}"><button class="checkInfo-doc">ตรวจสอบเอกสาร</button></a>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                // เพิ่มข้อมูลใน pending list
                pendingList.appendChild(userDiv);
            });
        }

        // เรียกฟังก์ชัน populatePendingList
        populatePendingList();
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

</body>

</html>