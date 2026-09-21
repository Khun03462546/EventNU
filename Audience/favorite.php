<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// คิวรีข้อมูลจากตาราง favorite เพื่อตรวจสอบว่าอีเว้นท์นี้เป็นของผู้ใช้ที่เลือกหรือไม่
$sql = "SELECT f.favorite_id, 
               e.*, 
               IF(n.favorite_id IS NOT NULL, 1, 0) AS is_notified
        FROM favorite f
        INNER JOIN event e ON f.EventId = e.EventID
        LEFT JOIN notification n ON n.favorite_id = f.favorite_id AND n.Audience_email = ?
        WHERE f.Audience_email = ?
        GROUP BY f.favorite_id";  // ใช้ GROUP BY เพื่อไม่ให้ได้ข้อมูลซ้ำ

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("ss", $email, $email); // ส่ง email เป็น parameter ไปใน query
$stmt->execute();
$result = $stmt->get_result();
$events = [];

if ($result->num_rows > 0) {
    // ใช้ while loop เพื่อดึงข้อมูลทุกแถวจากผลลัพธ์
    while ($row = $result->fetch_assoc()) {
        // ดึงข้อมูลของ event
        $event_picture = $row['Event_Picture'];
        $event_name = $row['Event_Name'];
        $event_date = $row['Event_Date'];
        $event_time = $row['Event_Time'];
        $event_location = $row['Event_Location'];
        $event_price = $row['Event_Price'];
        $event_detail = $row['Event_Detail'];
        $event_id = $row['EventID'];
        $eventTypeRe = $row['TypeRegister'];
        $favorite_id = $row['favorite_id']; // ดึง Favorite_id จากตาราง favorite
        $is_notified = $row['is_notified'];
        // ตรวจสอบว่า event_price เป็น 0 หรือไม่
        if ($event_price == 0) {
            $price_display = "Free";
        } else {
            $price_display = $event_price;
        }

        // แปลงวันที่เป็นรูปแบบที่ต้องการ
        $event_date_formatted = date("d F Y", strtotime($event_date));  // วันที่ในรูปแบบ "01 January 2025"

        // ตรวจสอบให้แน่ใจว่าเวลาในฐานข้อมูลมีรูปแบบที่ถูกต้อง ถ้าไม่ ให้แสดงเวลาเป็น "00:00"
        $event_time_formatted = date("H:i", strtotime($event_time));  // เวลาในรูปแบบ "18:00"

        $days_th = [
            "Sunday" => "อาทิตย์",
            "Monday" => "จันทร์",
            "Tuesday" => "อังคาร",
            "Wednesday" => "พุธ",
            "Thursday" => "พฤหัสบดี",
            "Friday" => "ศุกร์",
            "Saturday" => "เสาร์"
        ];

        $months_th = [
            "01" => "มกราคม",
            "02" => "กุมภาพันธ์",
            "03" => "มีนาคม",
            "04" => "เมษายน",
            "05" => "พฤษภาคม",
            "06" => "มิถุนายน",
            "07" => "กรกฎาคม",
            "08" => "สิงหาคม",
            "09" => "กันยายน",
            "10" => "ตุลาคม",
            "11" => "พฤศจิกายน",
            "12" => "ธันวาคม"
        ];

        $day_of_week = $days_th[date("l", strtotime($event_date))];
        $day = date("d", strtotime($event_date));
        $month = $months_th[date("m", strtotime($event_date))];
        $year = date("Y", strtotime($event_date)) + 543; // แปลงเป็น พ.ศ.

        $events[] = [
            'picture' => $event_picture,
            'name' => $event_name,
            'date_formatted' => $event_date_formatted,
            'time_formatted' => $event_time_formatted,
            'location' => $event_location,
            'price_display' => $price_display,
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'id' => $event_id,
            'favorite_id' => $favorite_id,
            'is_notified' => $is_notified,
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
        // Normalize variable names and format date/time safely
        $event_id = $event_row['EventID'];
        $event_name = $event_row['Event_Name'];
        $event_date = $event_row['Event_Date'];
        $event_time = $event_row['Event_Time'];
        $event_location = $event_row['Event_Location'];
        $event_detail = $event_row['Event_Detail'];
        $event_picture = $event_row['Event_Picture'];
        $event_price = $event_row['Event_Price'];

        $event_date_formatted = $event_date ? date("d F Y", strtotime($event_date)) : "No date available";
        $event_time_formatted = $event_time ? date("H:i", strtotime($event_time)) : "No time available";
        $price_display = ($event_price == 0) ? "Free" : $event_price;

        // สร้างอาร์เรย์อีเวนต์
        $eventss[] = [
            'event_ids' => $event_id,
            'event_names' => $event_name,
            'event_dates' => $event_date,
            'event_times' => $event_time,
            'event_dates_formatted' => $event_date_formatted,
            'event_times_formatted' => $event_time_formatted,
            'event_locations' => $event_location,
            'event_details' => $event_detail,
            'event_pictures' => $event_picture,
            'event_prices' => $event_price,
            'price_displays' => $price_display,
        ];

    }
} else {
    echo "No events found.";
}

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
                    <img src="<?php echo $image;?>" alt="Profile" class="profile-img">
                    <span><?php echo $first_name ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="<?php echo $image;?>" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo $first_name ?><br><small><?php echo $email ?></small></p>
                        </div>
                    </div>
                    <hr>
                    <a href="ticketsoon.php">บัตรของฉัน</a>
                    <a href="buy.php">คำสั่งซื้อของฉัน</a>
                    <a href="favorite.php">อีเว้นท์ที่ติดตาม</a>
                    <a href="private.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="favorite-setting-container">
        <div class="favorite-setting-container-data">
            <div class="favorite-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="favorite-profile-img"></div>
                <h2><?php echo $first_name ?></h2>
                <p><?php echo $email ?></p>
            </div>

            <div class="favorite-setting-menu">
                <a href="ticketsoon.php" class="favorite-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    บัตรของฉัน</a>
                <a href="buy.php" class="favorite-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อของฉัน</a>
                <a href="favorite.php" class="favorite-setting-menu-item active"><i class="bi bi-star"></i>
                    อีเว้นท์ที่ติดตาม</a>
                <a href="private.php" class="favorite-setting-menu-item"><i class="bi bi-gear"></i> ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="favorite-setting-menu-item favorite-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="favorite-setting-container-extra">
            <h2>อีเว้นท์ที่ติดตาม</h2>
            <?php foreach ($events as $event): ?>
                <div class="favorite-setting-container-event">
                    <div class="favorite-icon-group">
                        <i class="fas fa-star favorite-star-icon" onclick="deleteFavorite(<?php echo $event['id']; ?>)"></i>
                        <?php $bellClass = $event['is_notified'] ? 'bi-bell-fill text-warning' : 'bi-bell'; ?>
                        <i class="bi <?= $event['is_notified'] ? 'bi-bell-fill text-warning' : 'bi-bell' ?> favorite-bell-icon"
                            data-favorite-id="<?= htmlspecialchars($event['favorite_id']) ?>"
                            onclick="toggleNotification(this, <?= htmlspecialchars($event['favorite_id']) ?>)">
                        </i>
                    </div>
                    <div class="favorite-ticket-body">
                        <img src="<?php echo $event['picture']; ?>" alt="Event Image" class="favorite-ticket-image">
                        <div class="favorite-ticket-details">
                            <p><?php echo "{$event['day']} {$event['month']} {$event['year']}"; ?> | <?php echo $event['time_formatted']; ?></p>
                            <h4><?php echo $event['name']; ?></h4>
                            <p><i class="bi bi-geo-alt-fill"></i> <?php echo $event['location']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- Meyawo js -->
    <script src="../assets/js/meyawo.js"></script>
    <script src="../assets/js/comment.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function checkEmptyEvents() {
                const containerExtra = document.querySelector(".favorite-setting-container-extra");
                const eventContainers = containerExtra.querySelectorAll(".favorite-setting-container-event");

                if (eventContainers.length === 0) {
                    const emptyState = document.createElement("div");
                    emptyState.classList.add("favorite-empty-events");
                    emptyState.innerHTML = `
                <div class="empty-icon">
                    <i class="bi bi-star-fill" style="font-size: 50px; color: #fff;"></i>
                </div>
                <h4>ไม่มีการติดตาม</h4>
                <p>กดติดตามเพื่อให้ไม่พลาดทุกการอัพเดท</p>
                <button class="btn btn-primary">ดูอีเว้นท์อื่นๆ</button>
            `;

                    containerExtra.appendChild(emptyState);
                }
            }

            document.querySelectorAll(".favorite-star-icon").forEach(function(icon) {
                icon.addEventListener("click", function() {
                    let eventContainer = this.closest(".favorite-setting-container-event");
                    if (eventContainer) {
                        eventContainer.remove();
                        checkEmptyEvents();
                    }
                });
            });

            checkEmptyEvents();
        });
        // ฟังก์ชันสำหรับส่งคำขอ AJAX เพื่อลบข้อมูล
        function deleteFavorite(eventId) {
            if (confirm("คุณต้องการลบรายการโปรดนี้ใช่หรือไม่?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_favorite.php", true); // ชื่อไฟล์ PHP ที่จะรับคำขอ
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        // ถ้าลบสำเร็จให้ลบไอคอนหรือซ่อนส่วนที่ต้องการ
                        alert("ลบรายการโปรดเรียบร้อยแล้ว!");
                        location.reload(); // รีเฟรชหน้าเพื่อแสดงผลการลบ
                    } else {
                        alert("เกิดข้อผิดพลาดในการลบ");
                    }
                };
                xhr.send("event_id=" + eventId); // ส่งค่า event_id ไปยัง PHP
            }
        }

        function toggleNotification(icon, favoriteId = null) {
            if (!favoriteId) {
                favoriteId = icon.getAttribute("data-favorite-id"); // ดึงค่า favorite_id จาก data-attribute
            }

            if (!favoriteId) {
                console.error("Favorite ID is missing!");
                return;
            }

            let isNotified = icon.classList.contains("bi-bell-fill");
            let action = isNotified ? "remove" : "add";

            fetch("toggle_notification.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `favorite_id=${favoriteId}&action=${action}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Server Response: ", data);

                    if (data.success) {
                        if (action === "add") {
                            icon.classList.remove("bi-bell");
                            icon.classList.add("bi-bell-fill", "text-warning");
                            alert("เปิดแจ้งเตือนอีเวนต์เรียบร้อย!");
                        } else {
                            icon.classList.remove("bi-bell-fill", "text-warning");
                            icon.classList.add("bi-bell");
                            alert("ปิดแจ้งเตือนอีเวนต์เรียบร้อย!");
                        }
                    } else {
                        alert("เกิดข้อผิดพลาด โปรดลองใหม่อีกครั้ง! \n" + data.error);
                    }
                })
                .catch(error => console.error("Fetch Error: ", error));
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