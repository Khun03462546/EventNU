<?php
session_start();
include "../db.php";

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
    $image = $row['committee_Image'];
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}

// ดึงข้อมูลอีเวนต์ทั้งหมดจากตาราง Event
$sql_event = "SELECT * FROM Event";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->execute();
$event_result = $stmt_event->get_result();

// ตรวจสอบว่ามีอีเวนต์หรือไม่
$events = [];
if ($event_result->num_rows > 0) {
    while ($event_row = $event_result->fetch_assoc()) {
        $event_id = $event_row['EventID'];
        $event_name = $event_row['Event_Name'];
        $event_date = $event_row['Event_Date'];  // รับค่า event_date จากผลลัพธ์
        $event_time = $event_row['Event_Time'];
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
        $event_location = $event_row['Event_Location'];
        $event_detail = $event_row['Event_Detail'];
        $event_picture = $event_row['Event_Picture'];
        $event_price = $event_row['Event_Price'];
        $price_display = ($event_price == 0) ? "Free" : $event_price;
        $event_id = $event_row['EventID'];
        $events[] = [
            'event_id' => $event_row['EventID'],
            'event_name' => $event_row['Event_Name'],
            'event_date' => $event_row['Event_Date'],
            'event_time' => $event_row['Event_Time'],
            'event_date_formatted' => $event_date_formatted,  // วันที่ที่แปลงแล้ว
            'event_time_formatted' => $event_time_formatted,  // เวลาที่แปลงแล้ว
            'event_location' => $event_row['Event_Location'],
            'event_detail' => $event_row['Event_Detail'],
            'event_picture' => $event_row['Event_Picture'], // ✅ เปลี่ยนให้เป็นตัวพิมพ์เล็ก
            'event_price' => $event_row['Event_Price'],
            'price_display' => $price_display,
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

<body data-spy="scroll" data-target=".navbar" data-offset="40" id="home">
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
                        if (count($events) > 0) {
                            // วนลูปแสดงอีเวนต์
                            foreach ($events as $event) {
                                // เก็บข้อมูลจากอีเวนต์
                                $event_date = $event['event_date_formatted'];
                                $event_name = $event['event_name'];
                                $event_location = $event['event_location'];
                                $event_picture = $event['event_picture'];
                                $event_price = $event['price_display'];
                                $event_id = $event['event_id'];

                                // สร้างปุ่มที่แสดงราคา
                                $button_class = "navbar-event-button";  // ไม่ต้องสนใจราคาบัตร
                                $button_text = "ดูรายละเอียด";  // แสดงข้อความ "ดูรายละเอียด" ตลอด

                        ?>
                                <div class="navbar-event-card">
                                    <img src="<?php echo $event_picture; ?>" alt="<?php echo $event_name; ?>">
                                    <div class="navbar-event-info">
                                        <p class="navbar-event-date"><i class="bi bi-calendar"></i> <?php echo $event_date; ?></p>
                                        <h4><?php echo $event_name; ?></h4>
                                        <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> <?php echo $event_location; ?></p>
                                        <a href="details.php?event_id=<?php echo $event['event_id']; ?>">
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
                    <a href="budget.php">งบประมาณ</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <!-- header -->
    <header id="home" class="header">
        <div class="overlay"></div>
        <div class="header-content container">
            <h1 class="header-title">
                <span class="up">NU</span>
                <span class="down">Event</span>
            </h1>
            <p class="header-subtitle">Naresuan University</p>
        </div>
    </header>
    <!-- end header -->

    <!-- event -->
    <div class="home-event-picture-soon">
        <div class="buttons-container">
            <button class="prev-button" onclick="changeEventImage('prev')">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        <div class="image-frame">
            <img src="<?php echo $events[3]['event_picture']; ?>" alt="Event Picture" class="event-image" id="eventImage" data-event-id="3">>
        </div>
        <div class="buttons-container">
            <button class="next-button" onclick="changeEventImage('next')">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="home-category">
        <h6 class="section-title">Upcoming Event</h6>
        <div class="categories-wrapper">
            <button class="arrow" onclick="scrollFaculties(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="categories-container" id="categoriesContainer">
            </div>
            <button class="arrow" onclick="scrollFaculties(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="main-container">
            <div class="event-card-container">
            </div>
        </div>
    </div>
    <!-- end event -->

    <!-- Footer -->
    <footer class="home-footer">
        <div class="home-container">
            <div class="home-footer-content">
                <p>&copy; 2025 EventNU. All rights reserved.</p>
                <ul class="home-footer-links">
                    <li><a href="../about.html"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="../contact.html"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="../terms.html"><i class="fas fa-file-contract"></i> Terms</a></li>
                    <li><a href="../privacy.html"><i class="fas fa-shield-alt"></i> Privacy</a></li>
                </ul>
            </div>
        </div>
    </footer>
    <!-- end footer-->

    <script>
        // เก็บข้อมูลรูปภาพของแต่ละ EventID ในอาร์เรย์
        const eventPictures = <?php echo json_encode(array_column($events, 'event_picture', 'event_id')); ?>;

        // สร้าง array ของ EventID ที่มีอยู่เพื่อใช้ในการวนลูป
        const eventIds = Object.keys(eventPictures).map(Number); // [1, 2, 3]

        // ฟังก์ชันสุ่มเลือก EventID และเปลี่ยนรูปภาพ
        function changeEventImage() {
            const currentImage = document.getElementById('eventImage');

            // สุ่มเลือก EventID ใหม่
            const randomEventID = eventIds[Math.floor(Math.random() * eventIds.length)];

            // เปลี่ยน src ของรูปภาพและ data-event-id
            currentImage.src = eventPictures[randomEventID];
            currentImage.setAttribute('data-event-id', randomEventID);
        }

        // ฟังก์ชันเปลี่ยนรูปภาพตามทิศทาง
        function changeEventImageByDirection(direction) {
            const currentImage = document.getElementById('eventImage');
            let currentEventID = parseInt(currentImage.getAttribute('data-event-id'));

            // หา index ของ EventID ปัจจุบัน
            let currentIndex = eventIds.indexOf(currentEventID);

            // ถ้าคลิก "next"
            if (direction === 'next') {
                currentIndex = (currentIndex + 1) % eventIds.length; // หมุนวนไปตัวถัดไป
            }
            // ถ้าคลิก "prev"
            else if (direction === 'prev') {
                currentIndex = (currentIndex - 1 + eventIds.length) % eventIds.length; // หมุนวนกลับ
            }

            // อัปเดต EventID ใหม่
            let newEventID = eventIds[currentIndex];

            // เปลี่ยน src ของรูปภาพและ data-event-id
            currentImage.src = eventPictures[newEventID];
            currentImage.setAttribute('data-event-id', newEventID);
        }

        // เพิ่ม event listener เมื่อ DOM โหลดเสร็จ
        document.addEventListener("DOMContentLoaded", function() {
            // เปลี่ยนรูปภาพแบบสุ่มเมื่อโหลดหน้า
            changeEventImage();

            // เพิ่มฟังก์ชันการคลิกเพื่อไปที่หน้าอีเวนต์
            document.getElementById("eventImage").addEventListener("click", function() {
                let eventId = this.getAttribute("data-event-id");
                window.location.href = "details.php?event_id=" + eventId;
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
                        <img src="${event.event_picture}" alt="${event.event_name}">
                        <div class="navbar-event-info">
                            <p class="navbar-event-date"><i class="bi bi-calendar"></i> ${event.event_date}</p>
                            <h4>${event.event_name}</h4>
                            <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ${event.event_location}</p>
                            <a href="details.php?event_id=${event.event_id}">
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
                        <img src="${event.event_picture}" alt="${event.event_name}">
                        <div class="navbar-event-info">
                            <p class="navbar-event-date"><i class="bi bi-calendar"></i> ${event.event_date}</p>
                            <h4>${event.event_name}</h4>
                            <p class="navbar-event-location"><i class="bi bi-geo-alt"></i> ${event.event_location}</p>
                            <a href="details.php?event_id=${event.event_id}">
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
    <script src="../assets/js/barhome.js"></script>

    <!-- bootstrap 3 affix -->
    <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

    <!-- Meyawo js -->
    <script src="../assets/js/meyawo.js"></script>
</body>

</html>