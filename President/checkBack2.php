<?php
session_start();

// ตรวจสอบว่า user ล็อกอินหรือไม่
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่ได้ล็อกอินให้กลับไปที่หน้า login
    exit();
}

include "../db.php"; // เชื่อมต่อฐานข้อมูล

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

// รับค่า eventId จาก URL
$eventId = $_GET['event_id'];
if (empty($eventId)) {
    echo "Event ID is missing.";
    exit(); // ถ้าไม่มี eventId ส่งมา ให้หยุดการทำงาน
}

// ตรวจสอบค่าของ eventId ที่ได้รับจาก URL

// Fetch orders from the database
$query = "SELECT r.*, e.event_date, e.event_time, e.event_name, e.event_picture, b.booking_id, b.ticket_count, b.ticket_totalprice, b.booking_receipt, a.audience_FirstName AS buyer_name, a.StudentID, a.audience_email AS buyer_email 
FROM register r 
JOIN event e ON r.EventID = e.EventID 
JOIN booking b ON r.booking_id = b.booking_id 
JOIN audience a ON r.audience_email = a.audience_email 
WHERE r.EventID = ? AND r.register_status = 'verifying'";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("s", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

// ตรวจสอบและเก็บข้อมูลคำสั่งซื้อในตัวแปร orders
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// ถ้าไม่พบคำสั่งซื้อ
if (count($orders) === 0) {
    echo "No orders found for this event.";
}

// Fetch completed orders
$query = "SELECT r.*, e.event_date, e.event_time, e.event_picture, b.booking_id, b.ticket_count, b.ticket_totalprice, b.booking_receipt, a.audience_FirstName AS buyer_name, a.StudentID, a.audience_email AS buyer_email 
FROM register r 
JOIN event e ON r.EventID = e.EventID 
JOIN booking b ON r.booking_id = b.booking_id 
JOIN audience a ON r.audience_email = a.audience_email 
WHERE r.EventID = ? AND r.register_status = 'completed'";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("s", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$complete = [];

// เก็บคำสั่งซื้อที่เสร็จแล้ว
while ($row = $result->fetch_assoc()) {
    $complete[] = $row;
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
// ปิดการเชื่อมต่อฐานข้อมูล
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
                    <a class="nav-link" href="approveEvent.php" data-target="home">อนุมัติขอจัดอีเว้นท์</a>
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
                <div class="nav-right">
                    <button class="user-button" id="userButton">
                        <img src="<?php echo $image; ?>" alt="Profile" class="profile-img">
                        <span><?php echo $first_name; ?></span>
                    </button>

                    <div class="user-menu" id="userMenu">
                        <div class="d-flex align-items-center px-3 py-2">
                            <img src="<?php echo $image; ?>" alt="Profile" class="profile-img">
                            <div>
                                <p class="m-0"><?php echo $first_name; ?><br>
                                    <small><?php echo $email; ?></small>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <a href="eventAllpre.php">อีเว้นท์ทั้งหมด</a>
                        <a href="checkBack.php">คำสั่งซื้อทั้งหมด</a>
                        <a href="followSum.php">ติดตามอีเว้นท์</a>
                        <a href="setting.php">ข้อมูลส่วนตัว</a>
                        <hr>
                        <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                    </div>
                </div>
            </div>
    </nav>
    <!-- end navbar -->
    <div class="checkBack2-setting-container">
        <div class="checkBack2-setting-container-data">
            <div class="checkBack2-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="checkBack2-profile-img"></div>
                <h2><?php echo $first_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>

            <div class="checkBack2-setting-menu">
                <a href="eventAll.php" class="checkBack2-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="checkBack.php" class="checkBack2-setting-menu-item active"><i class="bi bi-clock-fill"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="setting.php" class="checkBack2-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="checkBack2-setting-menu-item checkBack2-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="checkBack2-container">
            <div class="checkBack2-back-button-container">
                <div class="checkBack2-back-button" onclick="goBack()">
                    <i class="bi bi-chevron-left"></i> คำสั่งซื้อทั้งหมด
                </div>
            </div>

            <div class="checkBack2-content">
                <?php foreach ($orders as $order): ?>
                    <div class="checkBack2-approvesetting-container-event d-flex align-items-center p-3">
                        <div class="checkBack2-approveevent-date text-center me-3">
                            <p><?= date("d", strtotime($order['event_date'])) ?></p>
                            <p class="mb-0"><?= date("M Y", strtotime($order['event_date'])) ?></p>
                            <p class="mb-0"><?= date("H:i", strtotime($order['event_time'])) ?></p>
                        </div>

                        <div class="checkBack2-approvesetting-container-event-Image">
                            <img src="<?= $order['event_picture'] ?>" alt="Event Image">
                        </div>

                        <div class="checkBack2-approveevent-details">
                            <h2>ชื่อผู้ซื้อ: <?= $order['buyer_name'] ?></h2>
                            <p>รหัสนิสิต: <?= $order['StudentID'] ?></p>
                            <p>Email: <?= $order['buyer_email'] ?></p>
                            <div class="checkBack2-approveevent-summary">
                                <p><i class="bi bi-ticket-perforated"></i> บัตร x <?= $order['ticket_count'] ?></p>
                                <p class="checkBack2-approveevent-total">Total: <?= number_format($order['ticket_totalprice'], 2) ?>฿</p>
                            </div>
                        </div>
                        <button class="checkBack2-approvestatus" onclick="openReceiptPopup('receiptPopup_<?= $order['booking_id'] ?>')">
                            <p class="checkBack2-approveorder-status">ตรวจสอบหลักฐานการโอน</p>
                        </button>
                    </div>
                <?php endforeach; ?>

                <!-- ถ้าต้องการแสดงรายการคำสั่งซื้อที่ 'completed' -->
                <?php foreach ($complete as $order): ?>
                    <div class="checkBack2-approvesetting-container-event d-flex align-items-center p-3">
                        <div class="checkBack2-approveevent-date text-center me-3">
                            <p><?= date("d", strtotime($order['event_date'])) ?></p>
                            <p class="mb-0"><?= date("M Y", strtotime($order['event_date'])) ?></p>
                            <p class="mb-0"><?= date("H:i", strtotime($order['event_time'])) ?></p>
                        </div>

                        <div class="checkBack2-approvesetting-container-event-Image">
                            <img src="<?= $order['event_picture'] ?>" alt="Event Image">
                        </div>

                        <div class="checkBack2-approveevent-details">
                            <h2>ชื่อผู้ซื้อ: <?= $order['buyer_name'] ?></h2>
                            <p>รหัสนิสิต: <?= $order['StudentID'] ?></p>
                            <p>Email: <?= $order['buyer_email'] ?></p>
                            <div class="checkBack2-approveevent-summary">
                                <p><i class="bi bi-ticket-perforated"></i> บัตร x <?= $order['ticket_count'] ?></p>
                                <p class="checkBack2-approveevent-total">Total: <?= number_format($order['ticket_totalprice'], 2) ?>฿</p>
                            </div>
                        </div>
                        <button class="checkBack2-approvestatus" onclick="openReceiptPopup('receiptPopup_<?= $order['booking_id'] ?>')" disabled>
                            <p class="checkBack2-approveorder-status">ตรวจสอบหลักฐานการโอน</p>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ป๊อปอัพแสดงใบเสร็จ -->
        <?php foreach ($orders as $order): ?>
            <div id="receiptPopup_<?= $order['booking_id'] ?>" class="checkBack2-receipt-popup">
                <div class="checkBack2-receipt-popup-content">
                    <h2>ใบเสร็จคำสั่งซื้อ</h2>
                    <p>รหัสคำสั่งซื้อ: #<?= $order['booking_id'] ?></p>
                    <p>ชื่องาน: <?= $order['event_name'] ?></p>
                    <p>จำนวนบัตร: <?= $order['ticket_count'] ?> ใบ</p>
                    <p>ราคารวม: <?= number_format($order['ticket_totalprice'], 2) ?> ฿</p>
                    <img src="../receipt/<?= $order['booking_receipt'] ?>" alt="Receipt Image" class="checkBack2-receipt-image">
                    <button class="checkBack2-receipt-close-btn" onclick="closeReceiptPopup('receiptPopup_<?= $order['booking_id'] ?>')">ปิด</button>
                </div>
            </div>
        <?php endforeach; ?>
        <script>
            function goBack() {
                window.history.back();
            }

            function openReceiptPopup(popupId) {
                document.getElementById(popupId).style.display = "flex";
            }

            function closeReceiptPopup(popupId) {
                document.getElementById(popupId).style.display = "none";
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