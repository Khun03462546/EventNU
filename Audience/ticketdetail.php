<?php
session_start(); // เริ่มต้น session
include "../db.php";

// เปิดโหมดแสดงข้อผิดพลาด
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบค่า register_id จาก URL
$register_id = isset($_GET['register_id']) ? intval($_GET['register_id']) : 0;
if ($register_id <= 0) {
    die("Invalid register ID!");
}

$email = $_SESSION['email']; // อีเมลของผู้ใช้ที่ล็อกอิน

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$sql = "SELECT 
            a.*, 
            f.Faculty_Name, 
            m.Major_Name 
        FROM Audience a
        LEFT JOIN Faculty f ON a.FacultyID = f.FacultyID
        LEFT JOIN Major m ON a.MajorID = m.MajorID
        WHERE a.Audience_email = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error (User Query): " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'] ?? "N/A";
    $last_name = $row['Audience_LastName'] ?? "N/A";
    $faculty = $row['Faculty_Name'] ?? "N/A";
    $major = $row['Major_Name'] ?? "N/A";
    $role = $row['Audience_Role'] ?? "N/A";
    $id = $row['StudentID'] ?? "N/A";
    $phone = $row['Audience_Phone'] ?? "N/A";
    $image = $row['Audience_Image'] ?? "../assets/imgs/jusmine.png"; // ใช้ภาพเริ่มต้นถ้าไม่มีภาพในฐานข้อมูล
} else {
    die("User not found!");
}

// ดึงข้อมูลการลงทะเบียนและอีเวนต์
$sql = "
    SELECT 
        r.register_id, 
        r.Audience_email, 
        r.EventID,
        r.booking_id,
        r.register_date, 
        r.register_status,
        e.Event_Name, 
        e.Event_Price, 
        e.Event_Date, 
        e.Event_Time, 
        e.Event_Location, 
        e.Event_Picture,
        b.ticket_count,
        b.ticket_totalprice,
        b.paytime
    FROM register r
    LEFT JOIN Event e ON r.EventID = e.EventID
    LEFT JOIN booking b ON r.booking_id = b.booking_id
    WHERE r.register_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error (Event Query): " . $conn->error);
}
$stmt->bind_param("i", $register_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // กำหนดค่าตัวแปรจากฐานข้อมูล
    $register_id = $row['register_id'];
    $booking_id = $row['booking_id'];
    $event_name = $row['Event_Name'];
    $event_price = $row['Event_Price'];
    $event_date = $row['Event_Date'];
    $event_time = $row['Event_Time'];
    $ticket_count = $row['ticket_count'];
    $ticket_totalprice = $row['ticket_totalprice'];
    $event_location = $row['Event_Location'];
    $event_picture = $row['Event_Picture'];
    $register_date = $row['register_date'];
    $status = $row['register_status'];
    $paytime = $row['paytime'];

    // แปลงวันที่และเวลา
    $event_date_formatted = $event_date ? date("d F Y", strtotime($event_date)) : "No date available";
    $event_time_formatted = $event_time ? date("H:i", strtotime($event_time)) : "No time available";

    // กำหนดราคาตั๋ว
    $price_display = ($event_price == 0) ? "Free" : $event_price;

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

    $day_of_week = $days_th[date("l", strtotime($register_date))];
    $day = date("d", strtotime($register_date));
    $month = $months_th[date("m", strtotime($register_date))];
    $year = date("Y", strtotime($register_date)) + 543; // แปลงเป็น พ.ศ.

    $day_of_weeks = $days_th[date("l", strtotime($event_date))];
    $days = date("d", strtotime($event_date));
    $months = $months_th[date("m", strtotime($event_date))];
    $years = date("Y", strtotime($event_date)) + 543; // แปลงเป็น พ.ศ.

    $day_of_weekp = $days_th[date("l", strtotime($paytime))];
    $dayp = date("d", strtotime($paytime));
    $monthp = $months_th[date("m", strtotime($paytime))];
    $yearp = date("Y", strtotime($paytime)) + 543; // แปลงเป็น พ.ศ.

} else {
    die("No event found.");
}

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

// ปิด statement และ connection
$stmt->close();
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

<body class="navbar-body" style="margin-top: 100px;">
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
                    <a href="ticketsoon.php">บัตรของฉัน</a>
                    <a href="buy.php">คำสั่งซื้อของฉัน</a>
                    <a href="favorite.php">งานที่ติดตาม</a>
                    <a href="private.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="ticketdetails-container">
        <div class="ticketdetails-left">
            <div class="ticketdetails-invoice-container">
                <div class="ticketdetails-invoice-header">
                    <span class="ticketdetails-invoice-title" onclick="history.back()" style="cursor: pointer;">&lt; รายละเอียดคำสั่งซื้อ<strong></strong></span>
                </div>
                <div class="ticketdetails-invoice-status">
                    <span class="ticketdetails-status-badge">วันที่สั่งซื้อ</span>
                    <span class="ticketdetails-due-date" id="due-date"><?php echo "$day $month $year"; ?></span>
                    <span class="ticketdetails-due-time" id="due-time"><?php echo date("H : i", strtotime($register_date)); ?> น.</span>
                </div>
                <div class="ticketdetails-notification-container">
                    <div class="ticketdetails-notification-content">
                        <svg aria-hidden="true" focusable="false" class="ticketdetails-notification-icon" role="img"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20">
                            <path fill="currentColor"
                                d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z">
                            </path>
                        </svg>
                        <div class="ticketdetails-notification-text">
                            <p class="ticketdetails-notification-title">คำสั่งซื้อสำเร็จ</p>
                            <p class="ticketdetails-notification-message">คำสั่งซื้อของคุณได้รับการยืนยันแล้ว
                                ดูรายการสั่งซื้อของคุณด้านล่างนี้</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ticketdetails-event-card">
                <img alt="concert image" loading="lazy" width="100" height="140" decoding="async"
                    class="ticketdetails-event-image" src="<?php echo $event_picture; ?>">
                <div class="ticketdetails-event-details">
                    <p class="ticketdetails-event-title"><?php echo $event_name; ?></p>
                    <p class="ticketdetails-event-date"><?php echo "$days $month $years"; ?> | <?php echo $event_time_formatted; ?></p>
                    <p class="ticketdetails-event-location"><?php echo $event_location; ?></p>
                </div>
            </div>
            <div class="ticketdetails-order-summary">
                <div class="ticketdetails-summary-header">
                    <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="clipboard-list"
                        class="ticketdetails-icon" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"
                        width="24">
                        <path fill="currentColor"
                            d="M320 64H280h-9.6C263 27.5 230.7 0 192 0s-71 27.5-78.4 64H104 64C28.7 64 0 92.7 0 128V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64zM80 112v24c0 13.3 10.7 24 24 24h88 88c13.3 0 24-10.7 24-24V112h16c8.8 0 16 7.2 16 16V448c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V128c0-8.8 7.2-16 16-16H80zm88-32a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zM136 272a24 24 0 1 0 -48 0 24 24 0 1 0 48 0zm40-16c-8.8 0-16 7.2-16 16s7.2 16 16 16h96c8.8 0 16-7.2 16-16s-7.2-16-16-16H176zm0 96c-8.8 0-16 7.2-16 16s7.2 16 16 16h96c8.8 0 16-7.2 16-16s-7.2-16-16-16H176zm-64 40a24 24 0 1 0 0-48 24 24 0 1 0 0 48z">
                        </path>
                    </svg>
                    <p class="ticketdetails-summary-title">สรุปคำสั่งซื้อ</p>
                </div>
                <div class="ticketdetails-summary-body">
                    <p class="ticketdetails-item-title">บัตร</p>
                    <div class="ticketdetails-item-list">
                        <div class="ticketdetails-item">
                            <div class="ticketdetails-item-details">
                                <p class="ticketdetails-item-description">ราคาทั้งหมด<span>(<span
                                            class="ticketdetails-currency">฿</span> <?php echo $event_price; ?>
                                        x<?php echo $ticket_count; ?>)</span></p>
                                <p class="ticketdetails-item-price"><span class="ticketdetails-currency">฿</span>
                                    <?php echo $ticket_totalprice; ?></p>
                            </div>
                        </div>
                        <div class="ticketdetails-item">
                            <div class="ticketdetails-item-details">
                                <p class="ticketdetails-item-description">ค่าธรรมเนียมการออกบัตร<span>(ฟรี)</span></p>
                                <p class="ticketdetails-item-price"><span class="ticketdetails-currency">฿</span> 0.00
                                </p>
                            </div>
                        </div>
                        <div class="ticketdetails-total">
                            <div class="ticketdetails-total-details">
                                <p>รวม</p>
                                <p class="ticketdetails-total-price"><span class="ticketdetails-currency">฿</span>
                                    <?php echo $ticket_totalprice; ?></p>
                            </div>
                            <div class="ticketdetails-total-details">
                                <p>ค่าธรรมเนียมการชำระเงิน (ฟรี)</p>
                                <p class="ticketdetails-total-price"><span class="ticketdetails-currency">฿</span> 0.00
                                </p>
                            </div>
                            <div class="ticketdetails-total-details">
                                <p>ยอดรวม</p>
                                <p class="ticketdetails-total-price"><span class="ticketdetails-currency">฿</span>
                                    <?php echo $ticket_totalprice; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ticketdetails-right">
            <div class="ticketdetails-right-top">
                <div class="ticketdetails-profile-card">
                    <img src="../assets/imgs/jusmine.png" alt="Profile Image">
                    <div class="ticketdetails-event">
                        <div class="ticketdetails-event-detailss">
                            <p><i class="ticketdetails-bi bi-person-fill"></i><strong> Name:</strong> <?php echo $first_name; ?>
                            </p>
                            <p><i class="ticketdetails-bi bi-telephone-fill"></i><strong> Number: 099-999-9999</strong></p>
                            <p><i class="ticketdetails-bi bi-envelope-fill"></i><strong> Email: <?php echo $email; ?></strong></p>
                            <p><i class="ticketdetails-bi bi-credit-card-fill"></i><strong> Student ID:</strong>
                            <?php echo $id; ?></p>
                            <p><i class="ticketdetails-bi bi-building"></i><strong> Faculty:</strong> <?php echo $faculty; ?></p>
                            <p><i class="ticketdetails-bi bi-bookmark-fill"></i><strong> Major:</strong> <?php echo $major; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ticketdetails-right-bottom">
                <div class="ticketdetails-payment-summary">
                    <div class="ticketdetails-payment-header">
                        <svg aria-hidden="true" focusable="false" class="ticketdetails-wallet-icon" role="img"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path fill="currentColor"
                                d="M88 32C39.4 32 0 71.4 0 120V392c0 48.6 39.4 88 88 88H424c48.6 0 88-39.4 88-88V216c0-48.6-39.4-88-88-88H120c-13.3 0-24 10.7-24 24s10.7 24 24 24H424c22.1 0 40 17.9 40 40V392c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V120c0-22.1 17.9-40 40-40H456c13.3 0 24-10.7 24-24s-10.7-24-24-24H88zM384 336a32 32 0 1 0 0-64 32 32 0 1 0 0 64z">
                            </path>
                        </svg>
                        <p class="ticketdetails-payment-title">ชำระเงิน</p>
                    </div>
                    <div class="ticketdetails-payment-details">
                        <span class="ticketdetails-payment-status">ชำระเงินแล้ว</span>
                        <div class="ticketdetails-payment-info">
                            <div class="ticketdetails-payment-amount">
                                <div class="ticketdetails-amount-row">
                                    <p>จำนวนเงินที่ชำระ:</p>
                                    <p class="ticketdetails-amount">฿ <?php echo $ticket_totalprice; ?></p>
                                </div>
                                <div class="ticketdetails-amount-row">
                                    <p>ชำระเมื่อ:</p>
                                    <p class="ticketdetails-payment-date"><?php echo "$dayp $monthp $yearp"?>, <?php echo date("H : i", strtotime($paytime)); ?></p>
                                </div>
                                <div class="ticketdetails-amount-row">
                                    <p>ช่องทางชำระเงิน:</p>
                                    <p class="ticketdetails-payment-method">QR PromptPay</p>
                                </div>
                            </div>
                            <div class="ticketdetails-payment-qr">
                                <div class="ticketdetails-qr-image"></div>
                                <span class="ticketdetails-qr-text">ชำระด้วย QR PromptPay</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

</body>

</html>