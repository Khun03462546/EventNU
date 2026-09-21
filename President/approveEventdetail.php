<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่ได้ล็อกอินให้กลับไปที่หน้า login
    exit();
}

include "../db.php"; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่มี session, เปลี่ยนเส้นทางไปหน้า login
    exit();
}

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

// ตรวจสอบว่ามี event_id ใน URL หรือไม่
if (!isset($_GET['request_event_id']) || empty($_GET['request_event_id'])) {
    echo "Event not found!";
    exit();
}

if (isset($_GET['request_event_id']) && is_numeric($_GET['request_event_id'])) {
    $event_id = $_GET['request_event_id'];  // รับค่า request_event_id จาก URL
    $decoded_event_id = trim($event_id);  // ลบช่องว่างถ้ามี

    // สร้าง SQL Query
    $sql = "SELECT re.*, a.Audience_FirstName, a.Audience_LastName, a.Audience_Role, a.Audience_Phone
            FROM request_event re
            LEFT JOIN audience a ON re.Audience_email = a.Audience_email
            WHERE re.request_event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $decoded_event_id); // ผูกค่ากับ parameter แบบ integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // ดึงข้อมูลที่ต้องการ
        $event_id = $row['request_event_id'];
        $auemail = $row['Audience_email'];
        $aufirst_name = $row['Audience_FirstName'];
        $aulast_name = $row['Audience_LastName'];
        $auphone = $row['Audience_Phone'];
        $role = $row['Audience_Role'];
        $event_name = $row['request_event_name'];
        $event_date = $row['request_event_date'];
        $event_time = $row['request_event_time'];

        // ตรวจสอบและแสดงวันที่และเวลา
        $event_date_formatted = $event_date ? date("d F Y", strtotime($event_date)) : "No date available";
        $event_time_formatted = $event_time ? date("H:i", strtotime($event_time)) : "No time available";

        // ดึงข้อมูลที่เหลือ
        $event_location = $row['request_event_location'];
        $event_detail = $row['request_event_detail'];
        $event_picture = $row['request_event_picture'];
        $event_map = $row['request_event_map'];
        $event_price = $row['request_event_price'];
        $price_display = ($event_price == 0) ? "Free" : $event_price;
        $point = $row['request_event_point'];
        $status = $row['request_event_status'];
        $registype = $row['request_event_type'];
        $event_budget = $row['request_event_budget'];
    } else {
        echo "Event not found!";
    }
} else {
    echo "Invalid event ID.";
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
                        <span><?php echo $first_name ?></span>
                    </button>

                    <div class="user-menu" id="userMenu">
                        <div class="d-flex align-items-center px-3 py-2">
                            <img src="<?php echo $image; ?>" alt="Profile" class="profile-img">
                            <div>
                                <p class="m-0"><?php echo $first_name ?><br>
                                    <small><?php echo $email ?></small>
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

    <div class="detailInfo-container">
        <div class="detailInfo-datail">
            <a href="#" class="detailInfo-back-btn" id="back-button"><i class="bi bi-chevron-left"></i>
                รายละเอียด</a>
            <div class="detailInfo-navbar">
                <span data-tab="basicinfo" class="active" onclick="changeTab(this)">ข้อมูลพื้นฐาน</span>
                <span data-tab="resinfo" onclick="changeTab(this)">ข้อมูลผู้รับผิดชอบ</span>
            </div>
            <div class="detailInfo-content">
                <div id="basicinfo" class="detailInfo-tab-content active">
                    <div class="detailInfo-detail-space">
                        <h2><i class="bi bi-info-circle"></i> ข้อมูลพื้นฐาน</h2>
                        <div class="detailInfo-datail-spacetop">
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">ชื่ออีเว้นท์ :</label>
                                <div class="detailInfo-form-control"><?php echo $event_name; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">รูปภาพ :</label>
                                <div class="detailInfo-form-control">
                                    <img src="<?php echo $event_picture; ?>" alt="Event Image" style="max-width: 200px; height: auto;">
                                </div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">วันเริ่มอีเว้นท์ :</label>
                                <div class="detailInfo-form-control"><?php echo $event_date_formatted; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">เวลาเริ่มอีเว้นท์ :</label>
                                <div class="detailInfo-form-control"><?php echo $event_time_formatted; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">ราคาบัตร :</label>
                                <div class="detailInfo-form-control"><?php echo $event_price; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">สถานที่จัด :</label>
                                <div class="detailInfo-form-control"><?php echo $event_location; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">แผนที่ :</label>
                                <div class="detailInfo-form-control"><?php echo $event_map; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">หมวดหมู่ของอีเว้นท์ :</label>
                                <div class="detailInfo-form-control">การศึกษา</div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">รายละเอียด :</label>
                                <div class="detailInfo-form-control"><?php echo $event_detail; ?></div>
                            </div>
                            <div class="detailInfo-basicinfo">
                                <label class="form-label">ลงทะเบียน :</label>
                                <div class="detailInfo-form-control"><?php echo $registype; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="resinfo" class="detailInfo-tab-content">
                    <div class="detailInfo-detail-space">
                        <h2><i class="bi bi-person-circle"></i> ข้อมูลผู้รับผิดชอบ</h2>
                        <div class="detailInfo-datail-spacetop">
                            <div class="detailInfo-resinfo">
                                <label class="form-label">ชื่อผู้จัด :</label>
                                <div class="detailInfo-form-control"><?php echo $aufirst_name; ?> <?php echo $aulast_name; ?></div>
                            </div>
                            <div class="detailInfo-resinfo">
                                <label class="form-label">ตำแหน่งของผู้จัด :</label>
                                <div class="detailInfo-form-control"><?php echo $role; ?></div>
                            </div>
                            <div class="detailInfo-resinfo">
                                <label class="form-label">ข้อมูลติดต่อ:</label>
                            </div>
                            <div class="detailInfo-resinfo">
                                <label class="form-label">อีเมล์ :</label>
                                <div class="detailInfo-form-control"><?php echo $auemail; ?></div>
                            </div>
                            <div class="detailInfo-resinfo">
                                <label class="form-label">เบอร์โทร :</label>
                                <div class="detailInfo-form-control"><?php echo $auphone; ?></div>
                            </div>
                            <div class="detailInfo-resinfo">
                                <label class="form-label">งบประมาณทั้งหมด :</label>
                                <div class="detailInfo-form-control"><?php echo $event_budget; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/navbar.js"></script>

    <script>
        function changeTab(tabElement) {
            let tabName = tabElement.getAttribute("data-tab");

            // ลบ active class ออกจากทุกแท็บ
            document.querySelectorAll('.detailInfo-navbar span').forEach(tab => {
                tab.classList.remove('active');
            });

            // เพิ่ม active class ให้แท็บที่ถูกคลิก
            tabElement.classList.add('active');

            // ซ่อนทุกแท็บเนื้อหา
            document.querySelectorAll('.detailInfo-tab-content').forEach(content => {
                content.style.display = 'none';
            });

            // แสดงเฉพาะแท็บที่ถูกเลือก
            document.getElementById(tabName).style.display = 'block';
        }

        // ตั้งค่าเริ่มต้นให้แสดงเฉพาะ basicinfo เมื่อโหลดหน้าเว็บ
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.detailInfo-tab-content').forEach(content => {
                content.style.display = 'none'; // ซ่อนทุกแท็บก่อน
            });

            // แสดงเฉพาะ basicinfo
            document.getElementById('basicinfo').style.display = 'block';

            // ปุ่มย้อนกลับ
            const backButton = document.getElementById("back-button");
            if (backButton) {
                backButton.addEventListener("click", function(event) {
                    event.preventDefault();
                    history.back();
                });
            }
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