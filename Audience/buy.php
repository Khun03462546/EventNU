<?php
session_start(); // เริ่มต้น session
include "../db.php";  // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบการเข้าสู่ระบบ
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

// เตรียมคำสั่ง SQL
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
        e.Event_Detail,
        IFNULL(b.ticket_count, 0) AS ticket_count,  -- ถ้า ticket_count เป็น NULL ให้แสดงเป็น 0
        IFNULL(b.ticket_totalprice, 0) AS ticket_totalprice  -- ถ้า ticket_totalprice เป็น NULL ให้แสดงเป็น 0
    FROM register r
    LEFT JOIN Event e ON r.EventID = e.EventID
    LEFT JOIN booking b ON r.booking_id = b.booking_id
    WHERE r.Audience_email = ?
";

// เตรียมการ query
$stmt = $conn->prepare($sql);
if ($stmt) {
    // ผูก parameter
    $stmt->bind_param("s", $email);

    // ดำเนินการ query
    $stmt->execute();

    // รับผลลัพธ์
    $result = $stmt->get_result();

    // ตรวจสอบผลลัพธ์และแสดงข้อมูล
    $registers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $register_id = $row['register_id'];
            $booking_id = $row['booking_id'];
            $event_name = $row['Event_Name'];
            $event_price = $row['Event_Price'];
            $event_date = $row['Event_Date'];
            $event_time = $row['Event_Time'];
            $ticket_count = $row['ticket_count'];
            $ticket_totalprice = $row['ticket_totalprice'];
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
            $event_location = $row['Event_Location'];
            $event_picture = $row['Event_Picture'];
            $register_date = $row['register_date'];
            $status = $row['register_status'];
            $price_display = ($event_price == 0) ? "Free" : $event_price;
            $registers[$register_id] = [
                'event_id' => $row['EventID'],
                'register_id' => $row['register_id'],
                'event_name' => $row['Event_Name'],
                'event_date' => $row['Event_Date'],
                'event_time' => $row['Event_Time'],
                'event_date_formatted' => $event_date_formatted,  // วันที่ที่แปลงแล้ว
                'event_time_formatted' => $event_time_formatted,  // เวลาที่แปลงแล้ว
                'event_location' => $row['Event_Location'],
                'event_detail' => $row['Event_Detail'],
                'event_picture' => $row['Event_Picture'], // ✅ เปลี่ยนให้เป็นตัวพิมพ์เล็ก
                'event_price' => $row['Event_Price'],
                'price_display' => $price_display,
                'ticket_count' => $row['ticket_count'],
                'register_status' => $row['register_status'],
                'ticket_totalprice' => $row['ticket_totalprice'],
                'booking_id' => $row['booking_id'],
            ];
        }
    } else {
        echo "";
    }

    // ปิด statement
    $stmt->close();
} else {
    echo "Error preparing query: " . $conn->error;
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
        $events[] = [
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
$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
// หลังจากที่ดึงข้อมูลจากฐานข้อมูลมาแล้ว
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
                        if (count($events) > 0) {
                            // วนลูปแสดงอีเวนต์
                            foreach ($events as $event) {
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
                    <a class="nav-link" href="followrequest.html" data-target="home">จัดอีเว้นท์</a>
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
                    <span><?php echo $first_name; ?></span>
                </button>
                <button id="hamburgerMenu" class="navbar-toggler">
                    <i class="bi bi-list"></i>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="<?php echo $image;?>" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo $first_name; ?><br><small><?php echo $email; ?></small></p>
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

    <div class="buy-setting-container">
        <div class="buy-setting-container-data">
            <div class="buy-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="buy-profile-img"></div>
                <h2><?php echo $first_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>

            <div class="buy-setting-menu">
                <a href="ticketsoon.php" class="buy-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    บัตรของฉัน</a>
                <a href="buy.php" class="buy-setting-menu-item active"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อของฉัน</a>
                <a href="favorite.php" class="buy-setting-menu-item"><i class="bi bi-star"></i> อีเว้นท์ที่ติดตาม</a>
                <a href="private.php" class="buy-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="buy-setting-menu-item buy-setting-logout"><i class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="buy-container">
            <div class="buy-name">
                <h3>คำสั่งซื้อของฉัน</h3>
                <div class="buy-setting-navbar">
                    <span id="history" class="active" onclick="changeTab('history')">คำสั่งซื้อทั้งหมด</span>
                    <span id="pending" onclick="changeTab('pending')">รอชำระเงิน</span>
                    <span id="checkreceipt" onclick="changeTab('checkreceipt')">รอการตรวจสอบ</span>
                    <span id="purchase" onclick="changeTab('purchase')">คำสั่งซื้อสำเร็จ</span>
                </div>
            </div>

            <div id="pendingContent" class="buy-tab-content"></div>
            <div id="checkreceiptContent" class="buy-tab-content"></div>
            <div id="purchaseContent" class="buy-tab-content"></div>
            <div id="historyContent" class="buy-tab-content active"></div>
        </div>
        <script>
            function changeTab(tabName) {
                // รีเซ็ตแท็บทั้งหมด
                document.querySelectorAll('.buy-setting-navbar span').forEach(tab => tab.classList.remove('active'));
                document.getElementById(tabName).classList.add('active');

                // รีเซ็ตเนื้อหาทั้งหมด
                document.querySelectorAll('.buy-tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabName + 'Content').classList.add('active');
            }

            function displayEvents() {
                const historyContent = document.getElementById('historyContent');
                const pendingContent = document.getElementById('pendingContent');
                const checkreceiptContent = document.getElementById('checkreceiptContent');
                const purchaseContent = document.getElementById('purchaseContent');

                // เคลียร์ข้อมูลเก่า
                historyContent.innerHTML = '';
                pendingContent.innerHTML = '';
                checkreceiptContent.innerHTML = '';
                purchaseContent.innerHTML = '';

                const events = [];

                <?php foreach ($registers as $registerss): ?>
                    events.push({
                        event_id: <?php echo $registerss['event_id']; ?>,
                        register_id: <?php echo $registerss['register_id']; ?>,
                        date: "<?php echo date('d M Y', strtotime($registerss['event_date'])); ?>",
                        time: "<?php echo date('H:i', strtotime($registerss['event_time'])); ?>",
                        title: "<?php echo $registerss['event_name']; ?>",
                        location: "<?php echo $registerss['event_location']; ?>",
                        tickets: <?php echo $registerss['ticket_count']; ?>,
                        totalPrice: <?php echo $registerss['ticket_totalprice']; ?>,
                        status: "<?php echo ($registerss['register_status'] ?: 'NULL'); ?>",
                        imgSrc: "<?php echo $registerss['event_picture']; ?>"
                    });
                <?php endforeach; ?>

                events.forEach(event => {
                    const eventDiv = document.createElement('div');
                    eventDiv.classList.add('history-setting-container-event', 'd-flex', 'align-items-center', 'p-3');

                    eventDiv.innerHTML = `
                        <div class="history-event-date text-center me-3">
                            <h3 class="mb-0 fw-bold">${event.date.split(' ')[0]}</h3>
                            <p class="mb-0">${event.date.split(' ')[1]} ${event.date.split(' ')[2]}</p>
                            <p class="mb-0">${event.time}</p>
                        </div>
                        <img src="${event.imgSrc}" alt="Event Image" class="me-3 rounded" style="height: 100px; width: auto;">
                        <div class="history-event-details">
                            <h2>${event.title}</h2>
                            <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                            <div class="d-flex justify-content-between align-items-end" style="border-top: 1px solid #ccc; margin-top: 30px;">
                                <p style="text-align: left;">
                                    <i class="bi bi-ticket-perforated"></i> บัตร x ${event.tickets}
                                </p>
                            </div>
                            <div class="order-status-container">
                                <div class="order-status" data-status="${event.status}"> 
                                    <p class="order-status-text"></p>
                                </div>
                            </div>
                        </div>
                    `;

                    const orderStatusDiv = eventDiv.querySelector(".order-status");
                    const orderStatusText = orderStatusDiv.querySelector(".order-status-text");

                    const status = orderStatusDiv.getAttribute("data-status");

                    const statusMapping = {
                        NULL: "กิจกรรมฟรี",
                        pending: "รอชำระ",
                        paid: "ชำระสำเร็จ",
                        verifying: "รอการตรวจสอบ",
                        completed: "คำสั่งซื้อสำเร็จ",
                    };

                    orderStatusText.textContent = statusMapping[status] || "ไม่ทราบสถานะ";

                    historyContent.appendChild(eventDiv);

                    // 📌 Pending - รายการที่ต้องชำระเงิน
                    if (event.status === 'pending') {
                        const pendingDiv = document.createElement('div');
                        pendingDiv.classList.add('pending-setting-container-event', 'd-flex', 'align-items-center', 'p-3');

                        pendingDiv.innerHTML = `
                            <div class="pending-event-date text-center me-3">
                                <h3 class="mb-0 fw-bold">${event.date.split(' ')[0]}</h3>
                                <p class="mb-0">${event.date.split(' ')[1]} ${event.date.split(' ')[2]}</p>
                                <p class="mb-0">${event.time}</p>
                            </div>
                            <img src="${event.imgSrc}" alt="Event Image" class="me-3 rounded" style="height: 100px; width: auto;">
                            <div class="pending-event-details flex-grow-1 d-flex flex-column justify-content-between" style="height: 100%">
                                <h2>${event.title}</h2>
                                <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                                <div class="pending-d-flex">
                                    <div class="pending-details-container">
                                        <p class="pending-details"><i class="bi bi-ticket-perforated"></i> บัตร x ${event.tickets}</p>
                                        <p class="pending-total-price">Total: ${event.totalPrice}฿</p>
                                    </div>
                                    <div class="pending-button-topay">
                                        <button class="pending-btn-cancel" onclick="removeEvent(this)" data-register-id="${event.register_id}">ยกเลิก</button>

                                        <a href="payment.php?register_id=${event.register_id} "class="pending-btn-success">ชำระเงิน</a>
                                    </div>
                                </div>
                            </div>
                        `;

                        pendingContent.appendChild(pendingDiv);
                    }
                    // 📌 Verifying - รายการรอตรวจสอบ
                    if (event.status === 'verifying') {
                        const checkDiv = document.createElement('div');
                        checkDiv.classList.add('checkreceipt-setting-container-event', 'd-flex', 'align-items-center', 'p-3');

                        checkDiv.innerHTML = `
                            <div class="checkreceipt-event-date text-center me-3">
                                <h3 class="mb-0 fw-bold">${event.date.split(' ')[0]}</h3>
                                <p class="mb-0">${event.date.split(' ')[1]} ${event.date.split(' ')[2]}</p>
                                <p class="mb-0">${event.time}</p>
                            </div>
                            <img src="${event.imgSrc}" alt="Event Image" class="me-3 rounded" style="height: 100px; width: auto;">
                            <div class="checkreceipt-event-details">
                                <h2>${event.title}</h2>
                                <p class="checkreceipt-event-location"><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                                <div class="checkreceipt-event-summary">
                                    <p><i class="bi bi-ticket-perforated"></i> บัตร x ${event.tickets}</p>
                                    <p class="checkreceipt-event-total">Total: ${event.totalPrice}฿</p>
                                </div>
                            </div>
                            <div class="checkreceipt-status">
                                <p class="checkreceipt-order-status">คำสั่งซื้อของคุณอยู่ระหว่างการตรวจสอบ</p>
                            </div>
                        `;
                        checkreceiptContent.appendChild(checkDiv);
                    }

                    // 📌 Completed - รายการที่ซื้อสำเร็จ
                    if (event.status === 'completed') {
                        const purchaseDiv = document.createElement('div');
                        purchaseDiv.classList.add('purchase-setting-container-event', 'd-flex', 'align-items-center', 'p-3');

                        purchaseDiv.innerHTML = `
                            <div class="purchase-event-date text-center me-3">
                                <h3 class="mb-0 fw-bold">${event.date.split(' ')[0]}</h3>
                                <p class="mb-0">${event.date.split(' ')[1]} ${event.date.split(' ')[2]}</p>
                                <p class="mb-0">${event.time}</p>
                            </div>
                            <img src="${event.imgSrc}" alt="Event Image" class="me-3 rounded" style="height: 100px; width: auto;">
                            <div class="purchase-event-details flex-grow-1 d-flex flex-column justify-content-between" style="height: 100%">
                                <div>
                                    <h2>${event.title}</h2>
                                    <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end" style="border-top: 1px solid #ccc; margin-top: 30px;">
                                    <p style="text-align: left;"><i class="bi bi-ticket-perforated"></i> บัตร x ${event.tickets}</p>
                                    <a href="ticketdetail.php?register_id=${event.register_id}" class="purchase-event-link text-decoration-none">
                                        <i class="bi bi-box-arrow-up-right"></i> ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        `;
                        purchaseContent.appendChild(purchaseDiv);
                    }
                });
            }

            function removeEvent(button) {
                let registerId = button.getAttribute("data-register-id"); // ดึงค่า register_id จากปุ่มที่กด

                if (!confirm("คุณต้องการยกเลิกการลงทะเบียนนี้ใช่หรือไม่?")) {
                    return;
                }

                fetch("delete_register.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "register_id=" + encodeURIComponent(registerId)
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "success") {
                            alert("ลบข้อมูลสำเร็จ!");
                            location.reload(); // รีโหลดหน้าใหม่เพื่ออัปเดต
                        } else {
                            alert("เกิดข้อผิดพลาด: " + data);
                        }
                    })
                    .catch(error => console.error("Error:", error));
            }

            window.onload = displayEvents;
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
        <!-- core  -->
        <script src="../assets/vendors/jquery/jquery-3.4.1.js"></script>
        <script src="../assets/vendors/bootstrap/bootstrap.bundle.js"></script>
        <script src="../assets/js/navbar.js"></script>

        <!-- bootstrap 3 affix -->
        <script src="../assets/vendors/bootstrap/bootstrap.affix.js"></script>

</body>

</html>