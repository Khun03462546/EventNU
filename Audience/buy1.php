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
                <li class="navbar-nav-item">
                    <a class="nav-link" href="followrequest.html" data-target="home">จัดอีเว้นท์</a>
                </li>
                <li class="navbar-nav-item dropdown">
                    <button class="nav-link dropdown-toggle" id="eventDropdown">
                        งานแสดง
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="eventDropdown">
                        <li><a class="dropdown-item" href="#">งานทั้งหมด</a></li>
                        <li><a class="dropdown-item" href="#">นันทนาการ</a></li>
                        <li><a class="dropdown-item" href="#">คอนเสิร์ต</a></li>
                        <li><a class="dropdown-item" href="#">ละครเวที</a></li>
                        <li><a class="dropdown-item" href="#">กีฬา</a></li>
                        <li><a class="dropdown-item" href="#">วัฒนธรรม</a></li>
                        <li><a class="dropdown-item" href="#">สุขภาพ</a></li>
                        <li><a class="dropdown-item" href="#">การศึกษา</a></li>
                        <li><a class="dropdown-item" href="#">ประกวด</a></li>
                        <li><a class="dropdown-item" href="#">บันเทิง</a></li>
                        <li><a class="dropdown-item" href="#">ตลาด</a></li>
                        <li><a class="dropdown-item" href="#">อบรม</a></li>
                        <li><a class="dropdown-item" href="#">นิทรรศการ</a></li>
                        <li><a class="dropdown-item" href="#">ครบรอบ</a></li>
                        <li><a class="dropdown-item" href="#">จิตอาสา</a></li>
                    </ul>
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
                    <span><?php echo $first_name; ?></span>
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
                    <a href="ticketsoon.html">บัตรของฉัน</a>
                    <a href="buy.html">คำสั่งซื้อของฉัน</a>
                    <a href="favorite.html">งานที่ติดตาม</a>
                    <a href="private.html">ข้อมูลส่วนตัว</a>
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
                <a href="ticketsoon.html" class="buy-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    บัตรของฉัน</a>
                <a href="buy.php" class="buy-setting-menu-item active"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อของฉัน</a>
                <a href="favorite.html" class="buy-setting-menu-item"><i class="bi bi-star"></i> งานที่ติดตาม</a>
                <a href="private.html" class="buy-setting-menu-item "><i class="bi bi-gear"></i>
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

            <div class="buy-content">
                <div id="historyContent" class="buy-tab-content active">
                    <?php foreach ($registers as $register): ?>
                        <div class="history-setting-container-event d-flex align-items-center p-3">
                            <div class="history-event-date text-center me-3">
                                <h3 class="mb-0 fw-bold"><?php echo date('d', strtotime($register['event_date'])); ?></h3>
                                <p class="mb-0"><?php echo date('M Y', strtotime($register['event_date'])); ?></p>
                                <p class="mb-0"><?php echo date('H:i', strtotime($register['event_time'])); ?></p>
                            </div>
                            <img src="<?php echo $register['event_picture']; ?>" alt="Event Image" class="me-3 rounded"
                                style="height: 100px; width: auto;">
                            <div class="history-event-details">
                                <h2><?php echo htmlspecialchars($register['event_name']); ?></h2>
                                <p style="text-align: left;">
                                    <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($register['event_location']); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-end"
                                    style="border-top: 1px solid #ccc; margin-top: 30px;">
                                    <p style="text-align: left;">
                                        <i class="bi bi-ticket-perforated"></i> บัตร x <?php echo ($register['ticket_count']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pending Content Display -->
            <div class="pending-content">
                <div id="pendingContent" class="buy-tab-content">
                    <?php foreach ($registers as $register): ?>
                        <?php
                        // ตรวจสอบค่า register_status 
                        ?>
                        <?php if ($register['register_status'] == 'pending'): ?>
                            <div class="pending-setting-container-event d-flex align-items-center p-3">
                                <div class="pending-event-date text-center me-3">
                                    <h3 class="mb-0 fw-bold"><?php echo date('d', strtotime($register['event_date'])); ?></h3>
                                    <p class="mb-0"><?php echo date('M Y', strtotime($register['event_date'])); ?></p>
                                    <p class="mb-0"><?php echo date('H:i', strtotime($register['event_time'])); ?></p>
                                </div>
                                <img src="<?php echo $register['event_picture']; ?>" alt="Event Image" class="me-3 rounded"
                                    style="height: 100px; width: auto;">
                                <div class="pending-event-details flex-grow-1 d-flex flex-column justify-content-between"
                                    style="height: 100%">
                                    <h2><?php echo htmlspecialchars($register['event_name']); ?></h2>
                                    <p style="text-align: left;">
                                        <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($register['event_location']); ?>
                                    </p>
                                    <div class="pending-d-flex">
                                        <div class="pending-details-container">
                                            <p class="pending-details">
                                                <i class="bi bi-ticket-perforated"></i> บัตร x <?php echo ($register['ticket_count']); ?>
                                            </p>
                                        </div>

                                        <div class="pending-button-topay">
                                            <button class="pending-btn-cancel" onclick="removeEvent(this)">ยกเลิก</button>
                                            <a href="payment.html"><button class="pending-btn-success">ชำระเงิน</button></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="checkreceiptContent" class="buy-tab-content">
                <div class="checkreceipt-setting-container-event d-flex align-items-center p-3">
                    <div class="checkreceipt-event-date text-center me-3">
                        <h3 class="mb-0 fw-bold">14</h3>
                        <p class="mb-0">ธ.ค. 2024</p>
                        <p class="mb-0">12:00</p>
                    </div>
                    <img src="../assets/imgs/astro night.png" alt="Event Image" class="me-3 rounded"
                        style="height: 100px; width: auto;">
                    <div class="checkreceipt-event-details">
                        <h2>Astro Night</h2>
                        <p class="checkreceipt-event-location"><i class="bi bi-geo-alt-fill"></i> NU life</p>

                        <div class="checkreceipt-event-summary">
                            <p><i class="bi bi-ticket-perforated"></i> บัตร x 3</p>
                            <p class="checkreceipt-event-total">Total: 60.00฿</p>
                        </div>
                    </div>
                    <div class="checkreceipt-status">
                        <p class="checkreceipt-order-status">คำสั่งซื้อของคุณอยู่ระหว่างการตรวจสอบ</p>
                    </div>
                </div>
            </div>
            <div id="purchaseContent" class="buy-tab-content">
                <div class="purchase-setting-container-event d-flex align-items-center p-3">
                    <div class="purchase-event-date text-center me-3">
                        <h3 class="mb-0 fw-bold">14</h3>
                        <p class="mb-0">ธ.ค. 2024</p>
                        <p class="mb-0">12:00</p>
                    </div>
                    <img src="../assets/imgs/astro night.png" alt="Event Image" class="me-3 rounded"
                        style="height: 100px; width: auto;">
                    <div class="purchase-event-details flex-grow-1 d-flex flex-column justify-content-between"
                        style="height: 100%">
                        <div>
                            <h2>Astro Night</h2>
                            <p style="text-align: left;"><i class="bi bi-geo-alt-fill"></i> NU life</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-end"
                            style="border-top: 1px solid #ccc; margin-top: 30px;">
                            <p style="text-align: left;">
                                <i class="bi bi-ticket-perforated"></i> บัตร x 2
                            </p>
                            <a href="ticket_detail.html" class="purchase-event-link text-decoration-none"><i
                                    class="bi bi-box-arrow-up-right"></i>
                                ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function changeTab(tabName) {
            const tabs = document.querySelectorAll('.buy-setting-navbar span');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            const activeTab = document.getElementById(tabName);
            activeTab.classList.add('active');

            const tabContents = document.querySelectorAll('.buy-tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            const activeContent = document.getElementById(tabName + 'Content');
            activeContent.classList.add('active');
        }
    </script>


</body>

</html>