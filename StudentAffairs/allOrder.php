<?php
session_start();
include '../db.php';

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// ดึงข้อมูลผู้ใช้
$sql = "SELECT first_name, last_name FROM StudentAffairs WHERE StudentAffairs_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
} else {
    echo "User not found!";
    exit();
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
        e.Event_Name, 
        e.Event_Price, 
        e.Event_Date, 
        e.Event_Time, 
        e.Event_Location, 
        e.Event_Picture,
        e.Event_Detail,  -- ✅ เพิ่ม Event_Detail
        IFNULL(b.ticket_count, 0) AS ticket_count,  
        IFNULL(b.ticket_totalprice, 0) AS ticket_totalprice,  
        b.booking_receipt
    FROM register r
    LEFT JOIN Event e ON r.EventID = e.EventID
    LEFT JOIN booking b ON r.booking_id = b.booking_id
    WHERE r.register_status = 'verifying';
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$registers = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $event_dates_formatted = $row['Event_Date'] ? date("d F Y", strtotime($row['Event_Date'])) : "No date available";
        $event_times_formatted = $row['Event_Time'] ? date("H:i", strtotime($row['Event_Time'])) : "No time available";
        $price_displays = ($row['Event_Price'] == 0) ? "Free" : $row['Event_Price'];

        $registers[$row['register_id']] = [
            'event_id' => $row['EventID'],
            'register_id' => $row['register_id'],
            'event_name' => $row['Event_Name'],
            'event_date' => $row['Event_Date'],
            'event_time' => $row['Event_Time'],
            'event_date_formatted' => $event_dates_formatted,
            'event_time_formatted' => $event_times_formatted,
            'event_location' => $row['Event_Location'],
            'event_detail' => $row['Event_Detail'],  // ✅ เพิ่มให้แสดงรายละเอียดงาน
            'event_picture' => $row['Event_Picture'],
            'event_price' => $row['Event_Price'],
            'price_display' => $price_displays,
            'ticket_count' => $row['ticket_count'],
            'register_status' => $row['register_status'],
            'ticket_totalprice' => $row['ticket_totalprice'],
            'booking_id' => $row['booking_id'],
            'booking_receipt' => $row['booking_receipt'],
        ];
    }
}

$stmt->close();
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
                        <div class="navbar-event-card">
                            <img src="../assets/imgs/bf.png" alt="Music Festival">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 08 มี.ค. 2025</p>
                                <h4>NU Book Fair</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> THE PIRATES PARK HATYAI
                                </p>
                                <button class="navbar-event-button">ดูรายละเอียด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/bf.png" alt="Music Festival">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 08 มี.ค. 2025</p>
                                <h4>NU Book Fair</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> THE PIRATES PARK HATYAI
                                </p>
                                <button class="navbar-event-button">ดูรายละเอียด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/artyoung.jpg" alt="Northern Fest">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 01 ก.พ. 2025</p>
                                <h4>ศิลป์เสมอ</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ขนส่ง 3</p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/firststage.jpg" alt="Northern Fest">
                            <div class="navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 01 ก.พ. 2025</p>
                                <h4>First Stage</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ขนส่ง 3</p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>

                        <div class="navbar-event-card">
                            <img src="../assets/imgs/artyoung.jpg" alt="Northern Fest">
                            <div class="navbar-navbar-event-info">
                                <p class="navbar-event-date"><i class="bi bi-calendar"></i> 01 ก.พ. 2025</p>
                                <h4>ศิลป์เสมอ</h4>
                                <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ขนส่ง 3</p>
                                <button class="navbar-event-button sold-out">บัตรหมด</button>
                            </div>
                        </div>
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
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                    <?php echo $first_name; ?>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo $first_name; ?><br><small><?php echo $email; ?></small></p>
                        </div>
                    </div>
                    <hr>
                    <a href="eventAll.php">อีเว้นท์ทั้งหมด</a>
                    <a href="allOrder.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="addEvent.php">เพิ่มอีเว้นท์</a>
                    <a href="changeRole.php">คำขอจัดอีเว้นท์</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="allOrder-setting-container">
        <div class="allOrder-setting-container-data">
            <div class="allOrder-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="allOrder-profile-img"></div>
                <h2><?php echo $first_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>

            <div class="allOrder-setting-menu">
                <a href="eventAll.php" class="allOrder-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="allOrder-setting-menu-item active"><i class="bi bi-clock-fill"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="allOrder-setting-menu-item"><i class="bi bi-clipboard2-plus"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="allOrder-setting-menu-item"><i class="bi bi-person-plus"></i>
                    คำขอจัดอีเว้นท์</a>
                <a href="setting.php" class="allOrder-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="allOrder-setting-menu-item allOrder-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="allOrder-container">
            <div class="allOrder-name">
                <h3>คำสั่งซื้อทั้งหมด</h3>
            </div>
            <div id="waitingEventsContainer"></div>
        </div>
    </div>
    <script src="../assets/js/navbar.js"></script>
    <script>
        const events = [
            <?php
            $first = true; // ใช้เช็กว่าเป็นตัวแรกหรือไม่
            foreach ($registers as $register) {
                $event_id = $register['event_id'];
                $register_id = $register['register_id'];
                $event_name = $register['event_name'];
                $event_date = $register['event_date_formatted'];
                $event_time = $register['event_time_formatted'];
                $event_location = $register['event_location'];
                $event_picture = $register['event_picture'];
                $event_price = $register['event_price'];
                $price_display = $register['price_display'];
                $ticket_count = $register['ticket_count'];
                $register_status = $register['register_status'];
                $ticket_totalprice = $register['ticket_totalprice'];
                $booking_id = $register['booking_id'];
                $booking_receipt = $register['booking_receipt'];

                // กำหนดสถานะ disabled หากสถานะเป็น 'pending'
                $disabled = $register_status == 'pending' ? 'disabled' : '';
                // สร้างลิงก์ไปยังหน้าใบเสร็จ
                $link = $register_status == 'pending' ? '#' : "allOrder2.php?event_id=$event_id";
                // กำหนดภาพ
                $image = $event_picture ? "$event_picture" : "https://via.placeholder.com/150";

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

                // แสดงข้อมูลเป็น JavaScript object
                if (!$first) echo ","; // เพิ่ม `,` คั่นระหว่างอ็อบเจ็กต์
                echo json_encode([
                    'id' => $register_id,
                    'title' => $event_name,
                    'date' => $day,
                    'month' => $month,
                    'year' => $year,
                    'time' => $event_time,
                    'location' => $event_location,
                    'image' => $image,
                    'price' => $event_price,
                    'price_display' => $price_display,
                    'ticket_count' => $ticket_count,
                    'disabled' => $disabled,
                    'link' => $link
                ]);
                $first = false; // เปลี่ยนค่าเป็น false หลังจากเพิ่มตัวแรกแล้ว
            }
            ?>
        ];
        console.log(events);

        function renderEvents() {
            const container = document.getElementById('waitingEventsContainer');
            const displayedEvents = new Set(); // ใช้เก็บ EventID ที่เคยแสดงแล้ว

            events.forEach(event => {
                if (!displayedEvents.has(event.event_id)) { // ถ้ายังไม่มี EventID นี้
                    displayedEvents.add(event.event_id); // บันทึกว่าเคยแสดงแล้ว

                    const eventElement = document.createElement('div');
                    eventElement.classList.add('waiting-setting-container-event', 'd-flex', 'align-items-center', 'p-3');
                    eventElement.setAttribute('data-id', event.id);

                    eventElement.innerHTML = `
                <div class="waiting-event-date text-center me-3">
                    <h3 class="mb-0 fw-bold">${event.date}</h3>
                    <p class="mb-0">${event.month} ${event.year}</p>
                    <p class="mb-0">${event.time}</p>
                </div>
                <div class="waiting-setting-container-event-Image">
                    <img src="${event.image}" alt="Event Image" class="me-3 rounded">
                </div>
                <div class="waiting-event-details flex-grow-1 d-flex flex-column justify-content-between" style="height: 100%">
                    <h2>${event.title}</h2>
                    <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                    <div class="waiting-d-flex">
                        <div class="waiting-details-container"></div>
                        <a href="${event.link}">
                            <button class="waiting-btn-success" ${event.disabled ? 'disabled' : ''}>
                                ตรวจสอบหลักฐานการโอน
                            </button>
                        </a>
                    </div>
                </div>
            `;

                    container.appendChild(eventElement);
                }
            });
        }

        // โหลดหน้าแล้วเรียกใช้งาน
        window.onload = renderEvents;
    </script>


</body>

</html>