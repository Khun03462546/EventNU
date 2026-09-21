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
    $image = $row['committee_Image']; // เปลี่ยนให้เป็นตัวพิมพ์เล็ก
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}
$sql_event = "SELECT re.*, a.Audience_FirstName, a.Audience_LastName 
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
        $aufirst_name = $event_row['Audience_FirstName'];
        $aulast_name = $event_row['Audience_LastName'];
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
        // ตรวจสอบว่า request_event_point >= 14 หรือไม่
        if ($point >= 14) {
            // อัปเดตค่า register_event_status เป็น "president"
            $sql_update_status = "UPDATE request_event SET request_event_status = 'president' WHERE request_event_id = ?";
            $stmt_update_status = $conn->prepare($sql_update_status);
            $stmt_update_status->bind_param("i", $event_id);
            $stmt_update_status->execute();
        }
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
            'aufirst_name' => $event_row['Audience_FirstName'],
            'aulast_name' => $event_row['Audience_LastName'],
            'registype' => $event_row['request_event_type']
        ];
    }
}

$sql_vote = "SELECT v.*, c.committee_firstname, c.committee_lastname 
              FROM vote v
              LEFT JOIN committee c ON v.committee_email = c.committee_email";
$stmt_vote = $conn->prepare($sql_vote);

if ($stmt_vote === false) {
    echo "Error preparing statement for votes: " . $conn->error;
    exit(); // หยุดการทำงานหากคำสั่ง SQL ผิดพลาด
}
$stmt_vote->execute();
$vote_result = $stmt_vote->get_result();


$votes = [];
if ($vote_result->num_rows > 0) {
    while ($vote_row = $vote_result->fetch_assoc()) {
        $vote_id = $vote_row['vote_id'];
        $request_event_id = $vote_row['request_event_id'];
        $comfname = $vote_row['committee_firstname'];
        $comlname = $vote_row['committee_lastname'];
        $vote_status = $vote_row['vote_status'];
        $vote_approved = ($vote_status == 'อนุมัติ') ? 1 : 0;  // แก้ไขการนับ
        $votes[] = [
            'request_event_id' => $vote_row['request_event_id'],
            'comfname' => $vote_row['committee_firstname'],
            'comlname' => $vote_row['committee_lastname'],
            'vote_status' => $vote_row['vote_status'],
            'vote_approved' => $vote_approved,
        ];
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
                    <input type="text" class="form-control"
                        placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน">
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
                    <span><?php echo $first_name ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="<?php echo $image; ?>" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo $first_name ?><br><small><?php echo $email ?></small></p>
                        </div>
                    </div>
                    <hr>
                    <a href="checkInfo.php">ตรวจสอบเอกสาร</a>
                    <a href="budget.php">งบประมาณ</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->
    <div class="checkInfo-container">
        <div class="assessment-container">
            <div class="assessment-datail">
                <h2>ประเมินอีเว้นท์</h2>
                <div class="assessment-custom-container">
                    <select id="assessmentFilter" class="assessment-custom-dropdown">
                        <option value="all">ทั้งหมด</option>
                        <option value="pass">ผ่าน</option>
                        <option value="npass">ไม่ผ่าน</option>
                        <option value="wpass">รอการประเมิน</option>
                    </select>
                </div>

                <?php
                foreach ($requestevents as $requestevent) {
                    // กำหนดสถานะ "ผ่าน", "ไม่ผ่าน", หรือ "รอการประเมิน"
                    if ($requestevent['point'] >= 14) {
                        $status = 'ผ่าน';
                        $status_class = 'assessment-detail-pass';
                    } elseif ($requestevent['point'] == 0) {
                        $status = 'รอการประเมิน';
                        $status_class = 'assessment-detail-wpass';
                    } else {
                        $status = 'ไม่ผ่าน';
                        $status_class = 'assessment-detail-npass';
                    }

                    // ตรวจสอบการโหวตที่ตรงกับ request_event_id
                    $approved_count = 0;
                    $vote_status = '';
                    $committee = 'ไม่มีข้อมูลคณะกรรมการ';

                    // ลูปผ่าน $votes และหาค่าที่ตรงกับ request_event_id
                    foreach ($votes as $vote) {
                        if ($vote['request_event_id'] == $requestevent['event_id']) {
                            $approved_count++; // เพิ่มจำนวนการโหวตอนุมัติ
                            $committee_list[] = [
                                'comfname' => $vote['comfname'] . ' ' . $vote['comlname'], // ชื่อคณะกรรมการ
                                'vote_status' => $vote['vote_status'] // สถานะการโหวต
                            ];
                        }
                    }

                    // ข้อมูลผู้ขอจัดอีเว้นท์
                    $requester_name = $requestevent['aufirst_name'] . ' ' . $requestevent['aulast_name'];
                    $event_name = $requestevent['event_name'];
                    $event_location = $requestevent['event_location'];
                    $registration_type = $requestevent['registype'];

                ?>
                    <div class="assessment-container-event">
                        <div class="<?= $status_class ?>">
                            <p><?= $status ?></p>
                        </div>
                        <div class="assessment-event-date text-center me-3">
                            <h3>25</h3>
                            <p class="mb-0"><?= date('M Y', strtotime($requestevent['event_date'])) ?></p>
                            <p class="mb-0"><?= date('H:i', strtotime($requestevent['event_time'])) ?></p>
                        </div>
                        <div class="assessment-container-event-Image">
                            <img src="<?= $requestevent['event_picture'] ?>" alt="Event Image" class="me-3 rounded">
                        </div>
                        <div class="assessment-event-details">
                            <h2>ผู้ขอจัดอีเว้นท์ : <?= $requester_name ?></h2>
                            <div class="assessment-details-container">
                                <p>ชื่ออีเว้นท์: <?= $event_name ?></p>
                                <p>สถานที่: <?= $event_location ?></p>
                                <p>ลงทะเบียน: <?= $registration_type ?></p>
                            </div>
                        </div>
                        <div class="assessment-buttons">
                            <button class="button-detail">จำนวนผู้ลงคะแนนอนุมัติ <?= $approved_count ?> คน</button>
                            <div id="assessment-popup-detail-overlay" class="assessment-popup-detail-overlay">
                                <div class="assessment-popup-detail-box">
                                    <h2>รายชื่อผู้ลงคะแนน</h2>
                                    <span class="assessment-popup-detail-close">&times;</span>
                                    <ul>
                                        <?php foreach ($committee_list as $committee): ?>
                                            <li><?= $committee['comfname'] ?>
                                                <button class="assessment-popup-detail-approve"><?= $committee['vote_status'] ?></button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>


            </div>

            <script src="../assets/js/navbar.js"></script>
            <script src="../assets/js/meyawo.js"></script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // เปิดป็อปอัพ "ผลการประเมิน"
                    document.querySelectorAll(".button-detail").forEach(button => {
                        button.addEventListener("click", function() {
                            let popup = this.closest(".assessment-container-event").querySelector(".assessment-popup-detail-overlay");
                            if (popup) popup.style.display = "flex";
                        });
                    });

                    // ปิดป็อปอัพ "ผลการประเมิน"
                    document.querySelectorAll(".assessment-popup-detail-close").forEach(button => {
                        button.addEventListener("click", function() {
                            this.closest(".assessment-popup-detail-overlay").style.display = "none";
                        });
                    });

                    // ปิดป็อปอัพ "อนุมัติ"
                    document.querySelectorAll(".assessment-popup-approve-close").forEach(button => {
                        button.addEventListener("click", function() {
                            this.closest(".assessment-popup-approve").style.display = "none";
                        });
                    });

                    // ปิดป็อปอัพเมื่อกดพื้นที่ดำ
                    document.addEventListener("click", function(event) {
                        if (event.target.classList.contains("assessment-popup-detail-overlay")) {
                            event.target.style.display = "none";
                        }
                        if (event.target.classList.contains("assessment-popup-approve")) {
                            event.target.style.display = "none";
                        }
                    });
                });

                document.addEventListener("DOMContentLoaded", function() {
                    const filterDropdown = document.getElementById("assessmentFilter");
                    const events = document.querySelectorAll(".assessment-container-event");

                    filterDropdown.addEventListener("change", function() {
                        const selectedFilter = filterDropdown.value;

                        events.forEach(event => {
                            // ตรวจสอบว่าภายในมี class ของประเภทผลการประเมินใด
                            const isPass = event.querySelector(".assessment-detail-pass") !== null;
                            const isNPass = event.querySelector(".assessment-detail-npass") !== null;
                            const isWPass = event.querySelector(".assessment-detail-wpass") !== null;

                            // ตรวจสอบว่าควรแสดงหรือซ่อน
                            if (selectedFilter === "all" ||
                                (selectedFilter === "pass" && isPass) ||
                                (selectedFilter === "npass" && isNPass) ||
                                (selectedFilter === "wpass" && isWPass)) {
                                event.style.display = "flex"; // แสดง
                            } else {
                                event.style.display = "none"; // ซ่อน
                            }
                        });
                    });
                });
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