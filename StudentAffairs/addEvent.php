<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../db.php';

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'] ?? '';

if (!empty($email)) {
    $stmt = $conn->prepare("SELECT StudentAffairs_Image, first_name, studentaffairs_email FROM studentaffairs WHERE studentaffairs_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "No user found with email: " . htmlspecialchars($email);
    }
} else {
    echo $user;
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $eventName = htmlspecialchars($_POST['eventName']);
    $eventDate = $_POST['eventDate'];
    $eventTime = $_POST['eventTime'];
    $eventPrice = $_POST['eventPrice'];
    $eventLocationName = htmlspecialchars($_POST['eventLocationName']);
    $eventDetails = htmlspecialchars($_POST['eventDetails']);
    $publicSaleDate = $_POST['ticketReleaseDate'];
    $eventLayout = $_POST['eventLocationLink'] ?? null;
    $organizerEmail = $_POST['OrgaName'];
    $categoryName = $_POST['categories'];
    $register = $_POST['register'];

    // ค่าเริ่มต้นสำหรับวันสิ้นสุดและเวลาสิ้นสุด
    $eventEndDate = $eventDate;
    $eventEndTime = $eventTime;
    $publicSaleTime = "00:00:00";

    // ตรวจสอบว่า categoryName มีค่า
    if (empty($categoryName)) {
        die("Error: หมวดหมู่ไม่ถูกต้อง");
    }

    // หา category_id
    $categoryID = null;
    $categoryQuery = "SELECT Category_ID FROM categories WHERE Category_Name = ?";
    $categoryStmt = $conn->prepare($categoryQuery);

    // ตรวจสอบว่าเตรียม statement สำเร็จหรือไม่
    if ($categoryStmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // bind_param สำหรับ categoryName
    $categoryStmt->bind_param("s", $categoryName);

    // Execute query
    $categoryStmt->execute();

    // bind_result สำหรับ Category_ID
    $categoryStmt->bind_result($categoryID);
    $categoryStmt->fetch();
    $categoryStmt->close();

    // ถ้าไม่พบ categoryID
    if ($categoryID === null) {
        die("Error: หมวดหมู่ไม่ถูกต้อง");
    }


    // อัปโหลดรูป
    $eventImage = null;
    if (!empty($_FILES["eventImage"]["name"]) && $_FILES["eventImage"]["error"] === 0) {
        $targetDir = "../assets/imgs/event/";
        $targetFile = $targetDir . basename($_FILES["eventImage"]["name"]);
        move_uploaded_file($_FILES["eventImage"]["tmp_name"], $targetFile);
        $eventImage = $targetFile;
    }

    // เพิ่มข้อมูลเข้า DB
    $sql = "INSERT INTO Event (Event_Name, EventOrganizer_email, Event_Date, Event_EndDate, Event_Time, Event_EndTime, public_sale_date, public_sale_time, Event_Location, Event_Detail, Event_Picture, Event_Layout, Category_ID, Event_Price, TypeRegister)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssssssssssssiis", $eventName, $organizerEmail, $eventDate, $eventEndDate, $eventTime, $eventEndTime, $publicSaleDate, $publicSaleTime, $eventLocationName, $eventDetails, $eventImage, $eventLayout, $categoryID, $eventPrice, $register);

    if ($stmt->execute()) {
        echo "<script>
            alert('บันทึกข้อมูลสำเร็จ!');
            window.location.href = 'eventAll.php';
        </script>";
    } else {
        echo "<script>
            alert('เกิดข้อผิดพลาด: " . htmlspecialchars($stmt->error) . "');
        </script>";
    }

    $stmt->close();
    $conn->close();
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
                    <img src="<?php echo htmlspecialchars($user['StudentAffairs_Image']); ?>" alt="Profile" class="profile-img">
                    <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="<?php echo htmlspecialchars($user['StudentAffairs_Image']); ?>" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo htmlspecialchars($user['first_name']); ?><br>
                                <small><?php echo htmlspecialchars($user['studentaffairs_email']); ?></small>
                            </p>
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

    <div class="addEvent-setting-container">
        <div class="addEvent-setting-container-data">
            <div class="addEvent-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="addEvent-profile-img"></div>
                <h2><?php echo htmlspecialchars($user['first_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['studentaffairs_email']); ?></p>
            </div>

            <div class="addEvent-setting-menu">
                <a href="eventAll.php" class="addEvent-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="addEvent-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="addEvent-setting-menu-item active"><i
                        class="bi bi-clipboard2-plus-fill"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="addEvent-setting-menu-item"><i class="bi bi-person-plus"></i>
                    เปลี่ยนโรล</a>
                <a href="setting.php" class="addEvent-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="addEvent-setting-menu-item addEvent-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <!-- ส่วนฟอร์มเพิ่มอีเว้นท์ -->
        <div class="addEvent-container">
            <div class="addEvent-name">
                <h3>เพิ่มอีเว้นท์</h3>
                <div class="addEvent-edit-form" id="editForm">
                    <form method="POST" enctype="multipart/form-data" id="eventForm">
                        <label>ชื่ออีเว้นท์</label>
                        <input type="text" name="eventName" id="eventName">

                        <label>วันที่จัดอีเว้นท์</label>
                        <input type="date" name="eventDate" id="Date" onkeydown="return false" onclick="this.showPicker()">

                        <label>วันวางจำหน่ายตั๋ว</label>
                        <input type="date" name="ticketReleaseDate" id="ticketReleaseDate" onkeydown="return false" onclick="this.showPicker()">

                        <label>เวลา</label>
                        <input type="time" name="eventTime" id="time" onkeydown="return false" onclick="this.showPicker()">

                        <label>ราคาบัตร</label>
                        <input type="number" name="eventPrice" id="price" min="0" step="1" placeholder="ระบุราคา">

                        <label>ชื่อสถานที่</label>
                        <input type="text" name="eventLocationName" id="eventlocationName">

                        <label>ลิงก์สถานที่</label>
                        <input type="url" name="eventLocationLink" id="location" placeholder="ใส่ลิงก์สถานที่ เช่น Google Maps">

                        <label>อีเมลผู้จัด</label>
                        <input type="text" name="OrgaName" id="OrgaName">

                        <label>รายละเอียด</label>
                        <textarea name="eventDetails" id="details" rows="4"></textarea>

                        <label>รูปภาพอีเว้นท์</label>
                        <input type="file" name="eventImage" id="eventImage" accept="image/*">

                        <label>หมวดหมู่</label>
                        <select name="categories" id="categories">
                            <?php
                            $sql = "SELECT * FROM categories";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['Category_Name'] . "'>" . $row['Category_Name'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>ไม่มีหมวดหมู่</option>";
                            }
                            ?>
                        </select>


                        <label>ลงทะเบียน</label>
                        <select name="register" id="register">
                            <option value="yes">ลงทะเบียน</option>
                            <option value="no">ไม่ลงทะเบียน</option>
                        </select>

                        <button type="submit" id="saveButton">บันทึก</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <script src="../assets/js/navbar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("eventForm");
            const saveButton = document.getElementById("saveButton");

            saveButton.addEventListener("click", function(e) {
                e.preventDefault();

                const eventName = document.getElementById("eventName").value.trim();
                const eventDate = document.getElementById("Date").value.trim();
                const eventTime = document.getElementById("time").value.trim();
                const eventLocationName = document.getElementById("eventlocationName").value.trim();
                const organizerEmail = document.getElementById("OrgaName").value.trim();

                if (!eventName || !eventDate || !eventTime || !eventLocationName || !organizerEmail) {
                    alert("กรุณากรอกข้อมูลให้ครบถ้วน!");
                    return;
                }

                form.submit();
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