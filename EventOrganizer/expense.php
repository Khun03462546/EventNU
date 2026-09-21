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
    $faculty = $row['FacultyID'];  
    $role = $row['Audience_Role'];
    $image = $row['Audience_Image'];
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
        // สร้างอาร์เรย์อีเวนต์
        $events[] = [
            'event_id' => $event_row['EventID'],
            'event_name' => $event_row['Event_Name'],
            'event_date' => $event_row['Event_Date'],
            'event_time' => $event_row['Event_Time'],
            'event_date_formatted' => $event_date_formatted,
            'event_time_formatted' => $event_time_formatted,
            'event_location' => $event_row['Event_Location'],
            'event_detail' => $event_row['Event_Detail'],
            'event_picture' => $event_row['Event_Picture'],
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

<body class="navbar-body">
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
                    <a class="nav-link" href="myEventDetails.php" data-target="home">อีเว้นท์ของฉัน</a>
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
                    <a href="expense.php">รายจ่าย</a>
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
                <a href="expense.php" class="buy-setting-menu-item active"><i class="bi bi-credit-card-fill"></i>
                    ค่าใช้จ่าย</a>
                <a href="private.php" class="buy-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="buy-setting-menu-item buy-setting-logout"><i class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="buy-container">
            <div class="buy-name">
                <h3>ค่าใช้จ่าย</h3>
            </div>
        
            <div class="expense-buy-content">
                <div id="expenseContent" class="buy-tab-content active expense-section">
            
                    <div class="expense-card">
                        <div class="expense-info">
                            <i class="bi bi-lightning-charge-fill expense-icon"></i>
                            <div class="expense-title">ค่าไฟ</div>
                        </div>
                        <div class="expense-amount">8 บาท/หน่วย</div>
                    </div>
            
                    <div class="expense-card">
                        <div class="expense-info">
                            <i class="bi bi-droplet-fill expense-icon"></i>
                            <div class="expense-title">ค่าน้ำ</div>
                        </div>
                        <div class="expense-amount">4 บาท/หน่วย</div>
                    </div>
            
                    <div class="expense-card">
                        <div class="expense-info">
                            <i class="bi bi-wifi expense-icon"></i>
                            <div class="expense-title">ค่าอินเทอร์เน็ต</div>
                        </div>
                        <div class="expense-amount">500 บาท/หน่วย</div>
                    </div>
            
                    <div class="expense-card">
                        <div class="expense-info">
                            <i class="bi bi-geo-alt expense-icon"></i>
                            <div class="expense-title">ค่าสถานที่</div>
                        </div>
                        <div class="expense-amount">
                            <a href="https://www.finance.nu.ac.th/circularsystem/upload/Announcements/Announ_870.pdf">ดูรายละเอียด</a>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>        

</body>

</html>