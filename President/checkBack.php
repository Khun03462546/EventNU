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
        $event_date_formatted = $row['Event_Date'] ? date("d F Y", strtotime($row['Event_Date'])) : "No date available";
        $event_time_formatted = $row['Event_Time'] ? date("H:i", strtotime($row['Event_Time'])) : "No time available";
        $price_display = ($row['Event_Price'] == 0) ? "Free" : $row['Event_Price'];

        $registers[$row['register_id']] = [
            'event_id' => $row['EventID'],
            'register_id' => $row['register_id'],
            'event_name' => $row['Event_Name'],
            'event_date' => $row['Event_Date'],
            'event_time' => $row['Event_Time'],
            'event_date_formatted' => $event_date_formatted,
            'event_time_formatted' => $event_time_formatted,
            'event_location' => $row['Event_Location'],
            'event_detail' => $row['Event_Detail'],  // ✅ เพิ่มให้แสดงรายละเอียดงาน
            'event_picture' => $row['Event_Picture'],
            'event_price' => $row['Event_Price'],
            'price_display' => $price_display,
            'ticket_count' => $row['ticket_count'],
            'register_status' => $row['register_status'],
            'ticket_totalprice' => $row['ticket_totalprice'],
            'booking_id' => $row['booking_id'],
            'booking_receipt' => $row['booking_receipt'],
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

    <div class="checkBack-setting-container">
        <div class="checkBack-setting-container-data">
            <div class="checkBack-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="checkBack-profile-img"></div>
                <h2><?php echo $first_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>

            <div class="checkBack-setting-menu">
                <a href="eventAllpre.php" class="checkBack-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="checkBack.php" class="checkBack-setting-menu-item active"><i class="bi bi-clock-fill"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="followSum.php" class="checkBack-setting-menu-item"><i class="bi bi-calendar"></i>
                    ติดตามอีเว้นท์</a>
                <a href="setting.php" class="checkBack-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="checkBack-setting-menu-item checkBack-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="checkBack-container">
            <div class="checkBack-name">
                <h3>คำสั่งซื้อทั้งหมด</h3>
                <div class="filter-container d-flex justify-content-end mb-3">
                    <div class="d-flex">
                        <select id="mainFilter" class="form-select me-2" style="width: auto;">
                            <option value="all">แสดงทั้งหมด</option>
                            <option value="month">เลือกเดือน</option>
                            <option value="year">เลือกปี</option>
                            <option value="last6months">6 เดือนล่าสุด</option>
                        </select>

                        <select id="monthFilter" class="form-select me-2" style="width: auto; display: none;">
                            <option value="">เลือกเดือน</option>
                            <option value="01">มกราคม</option>
                            <option value="02">กุมภาพันธ์</option>
                            <option value="03">มีนาคม</option>
                            <option value="04">เมษายน</option>
                            <option value="05">พฤษภาคม</option>
                            <option value="06">มิถุนายน</option>
                            <option value="07">กรกฎาคม</option>
                            <option value="08">สิงหาคม</option>
                            <option value="09">กันยายน</option>
                            <option value="10">ตุลาคม</option>
                            <option value="11">พฤศจิกายน</option>
                            <option value="12">ธันวาคม</option>
                        </select>

                        <select id="yearFilter" class="form-select" style="width: auto; display: none;"></select>
                    </div>
                </div>
            </div>

            <?php foreach ($registers as $register) { ?>
                <div class="checkBack-waiting-setting-container-event d-flex align-items-center p-3" data-id="event<?php echo $register['register_id']; ?>">
                    <div class="checkBack-waiting-event-date text-center me-3">
                        <h3 class="mb-0 fw-bold"><?php echo date("d", strtotime($register['event_date'])); ?></h3>
                        <p class="mb-0"><?php echo date("M Y", strtotime($register['event_date'])); ?></p>
                        <p class="mb-0"><?php echo date("H:i", strtotime($register['event_time'])); ?></p>
                    </div>
                    <div class="checkBack-waiting-setting-container-event-Image">
                        <img src="<?php echo htmlspecialchars($register['event_picture']); ?>" alt="Event Image" class="me-3 rounded">
                    </div>
                    <div class="checkBack-waiting-event-details flex-grow-1 d-flex flex-column justify-content-between" style="height: 100%">
                        <h2><?php echo htmlspecialchars($register['event_name']); ?></h2>
                        <div class="checkBack-waiting-d-flex">
                            <div class="checkBack-waiting-details-container">
                                <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($register['event_location']); ?></p>
                                <p style="text-align: left;"><i class="bi bi-piggy-bank-fill"></i> ยอดรวม: <?php echo $register['ticket_totalprice']; ?> ฿ (<?php echo $register['price_display']; ?>)</p>
                                <p style="text-align: left;"><i class="bi bi-person-fill"></i> ผู้เข้าชมทั้งหมด: <?php echo $register['ticket_count']; ?> คน</p>
                            </div>
                            <a href="checkBack2.php?event_id=<?php echo $register['event_id']; ?>"><button class="checkBack-waiting-btn-success">ตรวจสอบหลักฐานการโอน</button></a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <script src="../assets/js/navbar.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const eventsContainer = document.querySelector(".checkBack-container");
                const events = Array.from(document.querySelectorAll(".checkBack-waiting-setting-container-event"));
                const mainFilter = document.getElementById("mainFilter");
                const monthFilter = document.getElementById("monthFilter");
                const yearFilter = document.getElementById("yearFilter");

                function generateYearOptions() {
                    const currentYear = new Date().getFullYear();
                    yearFilter.innerHTML = "";
                    for (let i = 0; i < 3; i++) {
                        const year = currentYear - i;
                        const option = document.createElement("option");
                        option.value = year;
                        option.textContent = year;
                        yearFilter.appendChild(option);
                    }
                }

                function filterEvents() {
                    const selectedMonth = monthFilter.value;
                    const selectedYear = yearFilter.value;
                    const selectedFilter = mainFilter.value;
                    const sixMonthsAgo = new Date();
                    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 5);

                    const monthMap = {
                        "ม.ค.": "01",
                        "ก.พ.": "02",
                        "มี.ค.": "03",
                        "เม.ย.": "04",
                        "พ.ค.": "05",
                        "มิ.ย.": "06",
                        "ก.ค.": "07",
                        "ส.ค.": "08",
                        "ก.ย.": "09",
                        "ต.ค.": "10",
                        "พ.ย.": "11",
                        "ธ.ค.": "12"
                    };

                    events.forEach(event => {
                        const dateText = event.querySelector(".checkBack-waiting-event-date").textContent.trim();
                        const match = dateText.match(/(\d{1,2})\s+(\S+)\s+(\d{4})/);
                        if (!match) return;

                        const day = match[1];
                        const monthThai = match[2];
                        const year = match[3];
                        const month = monthMap[monthThai] || "";
                        const eventDate = new Date(year, parseInt(month) - 1, day);
                        let show = false;

                        if (selectedFilter === "all") {
                            show = true;
                        } else if (selectedFilter === "month" && selectedMonth === month) {
                            show = true;
                        } else if (selectedFilter === "year" && selectedYear === year) {
                            show = true;
                        } else if (selectedFilter === "last6months" && eventDate >= sixMonthsAgo) {
                            show = true;
                        }

                        event.style.display = show ? "flex" : "none";
                    });

                    sortEvents(); // ✅ เรียงลำดับหลังจากกรอง
                }

                function sortEvents() {
                    const visibleEvents = Array.from(eventsContainer.querySelectorAll(".checkBack-waiting-setting-container-event"))
                        .filter(event => event.style.display !== "none");

                    const monthMap = {
                        "ม.ค.": "01",
                        "ก.พ.": "02",
                        "มี.ค.": "03",
                        "เม.ย.": "04",
                        "พ.ค.": "05",
                        "มิ.ย.": "06",
                        "ก.ค.": "07",
                        "ส.ค.": "08",
                        "ก.ย.": "09",
                        "ต.ค.": "10",
                        "พ.ย.": "11",
                        "ธ.ค.": "12"
                    };

                    visibleEvents.sort((a, b) => {
                        const dateA = a.querySelector(".checkBack-waiting-event-date").textContent.trim();
                        const dateB = b.querySelector(".checkBack-waiting-event-date").textContent.trim();

                        const matchA = dateA.match(/(\d{1,2})\s+(\S+)\s+(\d{4})/);
                        const matchB = dateB.match(/(\d{1,2})\s+(\S+)\s+(\d{4})/);
                        if (!matchA || !matchB) return 0;

                        const dayA = parseInt(matchA[1]);
                        const monthA = parseInt(monthMap[matchA[2]] || "01");
                        const yearA = parseInt(matchA[3]);

                        const dayB = parseInt(matchB[1]);
                        const monthB = parseInt(monthMap[matchB[2]] || "01");
                        const yearB = parseInt(matchB[3]);

                        const dateObjA = new Date(yearA, monthA - 1, dayA);
                        const dateObjB = new Date(yearB, monthB - 1, dayB);

                        return dateObjB - dateObjA; // ✅ เรียงจากล่าสุด -> อดีต
                    });

                    // ✅ เคลียร์ container แล้วเพิ่มอีเวนต์ที่เรียงใหม่
                    visibleEvents.forEach(event => eventsContainer.appendChild(event));
                }

                mainFilter.addEventListener("change", function() {
                    monthFilter.style.display = "none";
                    yearFilter.style.display = "none";
                    monthFilter.value = "";
                    yearFilter.value = "";
                    if (mainFilter.value === "month") {
                        monthFilter.style.display = "inline-block";
                    } else if (mainFilter.value === "year") {
                        yearFilter.style.display = "inline-block";
                    }
                    filterEvents();
                });

                monthFilter.addEventListener("change", filterEvents);
                yearFilter.addEventListener("change", filterEvents);

                generateYearOptions();
                sortEvents();
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