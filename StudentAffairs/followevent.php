<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

// ดึงข้อมูล StudentAffairs
$email = $_SESSION['email'];
$sql = "SELECT * FROM StudentAffairs WHERE StudentAffairs_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบว่าพบผู้ใช้หรือไม่
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $image = $row['StudentAffairs_Image'];
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}

$user = $result->fetch_assoc();
$stmt->close();

// ดึงข้อมูล president (กรณีมีคนเดียว)
$sql_president = "SELECT * FROM President LIMIT 1";
$result_president = $conn->query($sql_president);
$president = $result_president->fetch_assoc();

// ดึงอีเวนต์ที่ status = completed พร้อม JOIN Audience
$sql_completed = "
    SELECT re.*, a.Audience_FirstName, a.Audience_LastName, a.Audience_phone
    FROM request_event re
    INNER JOIN Audience a ON re.Audience_email = a.Audience_email
    WHERE re.request_event_status = 'completed'
";
$result_completed = $conn->query($sql_completed);

// เตรียมข้อมูลสำหรับ JS
$completed_events = [];
while ($row = $result_completed->fetch_assoc()) {
    // แปลงวันที่
    $dateObj = date_create($row['event_date']);
    $row['date'] = date_format($dateObj, "d");

    // แปลงเดือนให้เป็นภาษาไทย
    $monthMap = [
        "01" => "ม.ค.",
        "02" => "ก.พ.",
        "03" => "มี.ค.",
        "04" => "เม.ย.",
        "05" => "พ.ค.",
        "06" => "มิ.ย.",
        "07" => "ก.ค.",
        "08" => "ส.ค.",
        "09" => "ก.ย.",
        "10" => "ต.ค.",
        "11" => "พ.ย.",
        "12" => "ธ.ค."
    ];
    $monthNumber = date_format($dateObj, "m");
    $row['month'] = $monthMap[$monthNumber];
    $row['year'] = date_format($dateObj, "Y");

    // จัดชื่อ field ให้ JS ใช้
    $row['time'] = $row['request_event_time'];
    $row['title'] = $row['request_event_name'];
    $row['organizer'] = $row['Audience_FirstName'] . " " . $row['Audience_LastName'];
    $row['image'] = $row['request_event_picture'] ?: '../assets/imgs/default.png'; // ใช้ภาพดีฟอลต์ถ้าไม่มีภาพ
    $row['location'] = $row['request_event_location'];
    $row['event_id'] = $row['request_event_id'];

    // เพิ่มข้อมูลผู้อนุมัติ
    $row['modalImage'] = $president['President_Image'] ?: '../assets/imgs/jusmine.png';
    $row['modalName'] = $president['President_firstname'] . " " . $president['President_lastname'];
    $row['modalEmail'] = $president['President_email'];
    $row['modalPosition'] = 'อธิการบดี';

    $completed_events[] = $row;
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
            $event_dates_formatted = date("d F Y", strtotime($event_dates));  // วันที่ในรูปแบบ "01 January 2025"
        } else {
            $event_dates_formatted = "No date available";  // ถ้าไม่มีวันที่ในข้อมูล
        }

        if ($event_times) {
            $event_times_formatted = date("H:i", strtotime($event_times));  // เวลาในรูปแบบ "18:00"
        } else {
            $event_times_formatted = "No time available";  // ถ้าไม่มีเวลาในข้อมูล
        }
        $event_locations = $event_row['Event_Location'];
        $event_details = $event_row['Event_Detail'];
        $event_pictures = $event_row['Event_Picture'];
        $event_prices = $event_row['Event_Price'];
        $price_displays = ($event_prices == 0) ? "Free" : $event_prices;
        $event_ids = $event_row['EventID'];
        // สร้างอาร์เรย์อีเวนต์
        $eventss[] = [
            'event_ids' => $event_row['EventID'],
            'event_names' => $event_row['Event_Name'],
            'event_dates' => $event_row['Event_Date'],
            'event_times' => $event_row['Event_Time'],
            'event_dates_formatted' => $event_dates_formatted,
            'event_times_formatted' => $event_times_formatted,
            'event_locations' => $event_row['Event_Location'],
            'event_details' => $event_row['Event_Detail'],
            'event_pictures' => $event_row['Event_Picture'],
            'event_prices' => $event_row['Event_Price'],
            'price_displays' => $price_displays,
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
                    <a class="nav-link" href="followevent.php" data-target="home">การดำเนินการขอจัดอีเว้นท์</a>
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
                    <a href="eventAll.php">อีเว้นท์ทั้งหมด</a>
                    <a href="allOrder.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="addEvent.php">เพิ่มอีเว้นท์</a>
                    <a href="changeRole.php">เปลี่ยนโรล</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="followevent-setting-container">
        <div class="followevent-container">
            <div class="followevent-name">
                <h3>การดำเนินการขอจัดกิจกรรม</h3>
                <div class="followevent-setting-navbar">
                    <span id="followevent" class="followevent">อนุมัติแล้ว</span>
                </div>
            </div>

            <div class="followevent-content">
                <div id="followeventContent" class="followevent-tab-content followevent"></div>
            </div>

        </div>
    </div>

    <!-- core  -->
    <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
    <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
    <script src="../assets/js/navbar.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <script>
        function followeventOpenPopup() {
            document.getElementById("popupModal").style.display = "flex";
        }

        function closePopup() {
            document.getElementById("popupModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("popupModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };

        const events = <?php echo json_encode($completed_events); ?>;

        function renderEvents() {
            const eventContent = document.getElementById('followeventContent');
            eventContent.innerHTML = ''; // เคลียร์ก่อน

            events.forEach(event => {
                const eventElement = document.createElement('div');
                eventElement.classList.add('followevent-setting-container-event', 'd-flex', 'align-items-center', 'p-3');

                eventElement.innerHTML = `
            <div class="followevent-event-date text-center me-3">
                <h3 class="mb-0 fw-bold">${event.date}</h3>
                <p class="mb-0">${event.month} ${event.year}</p>
                <p class="mb-0">${event.time}</p>
            </div>
            <img src="${event.image || '../assets/imgs/default.png'}" alt="Event Image" class="me-3 rounded" style="height: 100px; width: auto;">
            <div class="followevent-event-details flex-grow-1 d-flex flex-column justify-content-between" style="height: 100%">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <h2>${event.title}</h2>
                    <p class="fw-bold text-end">ชื่อผู้จัด: <span>${event.organizer}</span></p>
                </div>
                <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div class="followevent-d-flex d-flex gap-2">
                           <a href="requesteventdetail.php?request_event_id=${event.event_id}" style="color: white;" class="btn btn-warning">ตรวจสอบเอกสาร</a>
                        </div>
                    </div>
                </div>
            </div>
        `;

                eventContent.appendChild(eventElement);
            });
        }



        function followeventOpenPopup(name, position, imageUrl) {
            document.getElementById('popupName').innerText = name;
            document.getElementById('popupPosition').innerText = `ตำแหน่ง: ${position}`;
            document.getElementById('popupEventImage').src = imageUrl;
            document.getElementById('popupModal').style.display = 'flex';
        }

        function downloadFile() {
            const link = document.createElement('a');
            link.href = 'path/to/your/file.ext'; // เปลี่ยนเป็นลิงก์ของไฟล์ที่ต้องการดาวน์โหลด
            link.download = 'filename.ext'; // ตั้งชื่อไฟล์ที่ดาวน์โหลด
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // เรียกใช้งานฟังก์ชัน renderEvents เมื่อโหลดหน้า
        window.onload = renderEvents;
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