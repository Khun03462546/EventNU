<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่มี session, เปลี่ยนเส้นทางไปหน้า login
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูลตาม email
$email = $_SESSION['email'];
$sql = "SELECT * FROM StudentAffairs WHERE StudentAffairs_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email); // ส่ง email เป็น parameter ไปใน query
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

$sql = "
    SELECT 
        r.*, 
        a.Audience_FirstName, 
        a.Audience_LastName, 
        a.FacultyID, 
        a.MajorID, 
        a.Audience_Phone, 
        a.Audience_Role,
        a.Audience_email
    FROM 
        request_event r
    JOIN 
        Audience a ON r.Audience_email = a.Audience_email
    WHERE 
        r.request_event_status = 'completed'
";

$result = $conn->query($sql);
$registered_users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $registered_users[] = $row;
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
$stmt->close();
$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
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

    <div class="changeRole-setting-container">
        <div class="changeRole-setting-container-data">
            <div class="changeRole-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="<?php echo $image; ?>" alt="Profile"
                        class="changeRole-profile-img"></div>
                <h2><?php echo $first_name ?></h2>
                <p><?php echo $email ?></p>
            </div>

            <div class="changeRole-setting-menu">
                <a href="eventAll.php" class="changeRole-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="changeRole-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="changeRole-setting-menu-item"><i class="bi bi-clipboard2-plus"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="changeRole-setting-menu-item active"><i
                        class="bi bi-person-plus-fill"></i>
                    เปลี่ยนโรล</a>
                <a href="setting.php" class="changeRole-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="changeRole-setting-menu-item changeRole-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="changeRole-container">
            <div class="changeRole-name">
                <h3>เปลี่ยนโรล</h3>
                <div class="changeRole-setting-navbar">
                    <span id="pending" class="active" onclick="changeTab('pending')">รอเปลี่ยนโรล</span>
                    <span id="changed" onclick="changeTab('changed')">เปลี่ยนโรลแล้ว</span>
                    <span id="reverted" onclick="changeTab('reverted')">คืนค่าโรลแล้ว</span>
                </div>
            </div>

            <div class="changeRole-content">
                <div id="pendingContent" class="changeRole-tab-content active">
                    <!-- แสดงข้อมูลผู้ใช้ที่รอเปลี่ยนโรล -->
                    <div id="changerole-pending-list"></div>
                </div>

                <div id="changedContent" class="changeRole-tab-content">
                    <!-- แสดงข้อมูลผู้ใช้ที่เป็น Organizer แล้ว -->
                    <div id="changerole-changed-list"></div>
                </div>

                <div id="revertedContent" class="changeRole-tab-content">
                    <!-- แสดงข้อมูลผู้ใช้ที่คืนค่าโรลแล้ว -->
                    <div id="changerole-reverted-list"></div>
                </div>

                <div class="changerole-overlay" id="changerole-overlay"></div>
                <div class="changerole-popup" id="changerole-popup">
                    <h3>เปลี่ยน Role</h3>
                    <select id="changerole-role-select">
                        <option value="EventOrganize">Event Organizer</option>
                    </select>
                    <button class="changerole-confirm-btn" onclick="confirmChange()">ยืนยัน</button>
                    <button class="changerole-cancel-btn" onclick="closePopup()">ยกเลิก</button>
                </div>


                <div id="changedContent" class="changeRole-tab-content">

                </div>
                <div id="revertedContent" class="changeRole-tab-content">

                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/meyawo.js"></script>
    <script>
        const users = <?php echo json_encode($registered_users); ?>;

        function populatePendingList() {
            const pendingList = document.getElementById("changerole-pending-list");
            const changedList = document.getElementById("changerole-changed-list");
            const revertedList = document.getElementById("changerole-reverted-list");

            pendingList.innerHTML = ""; // ล้างรายการในหน้า "รอเปลี่ยนโรล"
            changedList.innerHTML = ""; // ล้างรายการในหน้า "เปลี่ยนโรลแล้ว"
            revertedList.innerHTML = ""; // ล้างรายการในหน้า "คืนค่าโรลแล้ว"

            // กรองผู้ใช้ที่มี Role เป็น "organizer"
            const usersToChangeRole = users.filter(user => user.Audience_Role !== 'organizer');
            const usersAlreadyOrganizer = users.filter(user => user.Audience_Role === 'organizer');

            // แสดงผู้ใช้ที่รอเปลี่ยนโรล
            usersToChangeRole.forEach(user => {
                const formattedDate = formatThaiDate(user.request_event_date);
                const userDiv = document.createElement("div");
                userDiv.className = "followevent-tab-content followevent";
                userDiv.innerHTML = `
            <div class="followevent-setting-container-event d-flex align-items-center p-3">
                <div class="followevent-event-date text-center me-3">
                    <h3 class="mb-0 fw-bold">${formattedDate.day}</h3>
                    <p class="mb-0">${formattedDate.monthYear}</p>
                    <p class="mb-0">${user.request_event_time}</p>
                </div>
                <img src="${user.request_event_picture}" alt="Event Image" class="me-3 rounded" style="height: 150px; width: auto;">
                <div class="followevent-event-details">
                    <h2>${user.request_event_name}</h2>
                    <p><i class="bi bi-geo-alt-fill"></i> ${user.request_event_location}</p>
                    <div class="changerole-role-card" id="${user.Audience_email}" data-original-role="${user.Audience_Role}">
                        <img src="../assets/imgs/jusmine.png" alt="profile">
                        <div class="changerole-role-info">
                            <strong>${user.Audience_FirstName} ${user.Audience_LastName}</strong><br>
                            <span class="changerole-role-text">${user.Audience_Role}</span>
                            <p>Email: ${user.Audience_email}</p>
                        </div>
                        <button class="changerole-change-btn" onclick="openPopup('${user.Audience_email}')">เปลี่ยนโรล</button>
                    </div>
                </div>
            </div>
        `;
                pendingList.appendChild(userDiv);
            });

            // แสดงผู้ใช้ที่เป็น "organizer" และสร้างปุ่ม "คืนค่าโรล"
            usersAlreadyOrganizer.forEach(user => {
                const formattedDate = formatThaiDate(user.request_event_date);
                const userDiv = document.createElement("div");
                userDiv.className = "followevent-tab-content followevent";
                userDiv.innerHTML = `
                            <div class="followevent-tab-content followevent">
                    <div class="followevent-setting-container-event d-flex align-items-center p-3">
                        <!-- วันที่และเวลา -->
                        <div class="followevent-event-date text-center me-3">
                            <h3 class="mb-0 fw-bold">${formattedDate.day}</h3>
                            <p class="mb-0">${formattedDate.monthYear}</p>
                            <p class="mb-0">${user.request_event_time}</p>
                        </div>

                        <!-- รูปภาพ -->
                        <img src="${user.request_event_picture}" alt="Event Image" class="me-3 rounded" style="height: 150px; width: auto;">
                        
                        <!-- รายละเอียดกิจกรรม -->
                        <div class="followevent-event-details">
                            <h2>${user.request_event_name}</h2>
                            <p><i class="bi bi-geo-alt-fill"></i> ${user.request_event_location}</p>
                            <div class="changerole-role-card" id="${user.Audience_email}" data-original-role="${user.Audience_Role}">
                                <img src="../assets/imgs/jusmine.png" alt="profile">
                                <div class="changerole-role-info">
                                    <strong>${user.Audience_FirstName} ${user.Audience_LastName}</strong><br>
                                    <span class="changerole-role-text">${user.Audience_Role}</span>
                                    <p>Email: ${user.Audience_email}</p>
                                </div>
                                ${user.Audience_Role === "organizer" ? `
                                    <button class="changerole-revert-btn" onclick="revertRole('${user.Audience_email}', '${user.Audience_Role}')">คืนค่าโรล</button>
                                ` : `
                                    <button class="changerole-change-btn" onclick="openPopup('${user.Audience_email}')">เปลี่ยนโรล</button>
                                `}
                            </div>
                        </div>
                    </div>
                </div>
                `;
                changedList.appendChild(userDiv);
            });
        }

        // ฟังก์ชันสำหรับเปลี่ยนแท็บ
        function changeTab(tabName) {
            document.querySelectorAll('.changeRole-setting-navbar span').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');

            document.querySelectorAll('.changeRole-tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName + "Content").classList.add('active');
        }

        function formatThaiDate(dateTimeStr) {
            const monthNamesThai = [
                "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
            ];

            const dateObj = new Date(dateTimeStr);
            const day = dateObj.getDate().toString().padStart(2, '0'); // วัน
            const month = monthNamesThai[dateObj.getMonth()]; // เดือนภาษาไทย
            const year = dateObj.getFullYear() + 543; // ปี พ.ศ.

            return {
                day: day,
                monthYear: `${month} ${year}`
            };
        }

        let selectedUserId = "";

        function openPopup(userId) {
            selectedUserId = userId;
            document.getElementById("changerole-overlay").style.display = "block";
            document.getElementById("changerole-popup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("changerole-overlay").style.display = "none";
            document.getElementById("changerole-popup").style.display = "none";
        }

        function confirmChange() {
            let newRole = document.getElementById("changerole-role-select").value;

            // เปลี่ยนค่าของ EventOrganize เป็น "organizer"
            if (newRole === "EventOrganize") {
                newRole = "organizer";
            }

            let userCard = document.getElementById(selectedUserId);
            let roleText = userCard.querySelector(".changerole-role-text");
            let email = selectedUserId;

            // ส่งคำขอไปยัง PHP เพื่ออัปเดต Role
            fetch("update_role.php", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: "email=" + encodeURIComponent(email) + "&role=" + encodeURIComponent(newRole)
                })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        roleText.innerText = newRole; // อัปเดต Role ในหน้า UI

                        // ถ้า role คือ organizer, ย้ายไปแท็บ "เปลี่ยนโรลแล้ว"
                        if (newRole === "organizer") {
                            const eventContainer = userCard.closest('.followevent-setting-container-event');
                            document.getElementById("changedContent").appendChild(eventContainer); // ย้ายไปที่หน้า "เปลี่ยนโรลแล้ว"
                        } else {
                            // ย้ายไปที่แท็บ "คืนค่าโรลแล้ว"
                            const eventContainer = userCard.closest('.followevent-setting-container-event');
                            document.getElementById("revertedContent").appendChild(eventContainer); // ย้ายไปที่หน้า "คืนค่าโรลแล้ว"
                        }

                        // ลบปุ่ม "เปลี่ยนโรล"
                        userCard.querySelector(".changerole-change-btn").remove();

                        // สร้างปุ่ม "คืนค่าโรล"
                        const revertBtn = document.createElement("button");
                        revertBtn.className = "changerole-revert-btn";
                        revertBtn.innerText = "คืนค่าโรล";
                        revertBtn.onclick = () => revertRole(selectedUserId, roleText.getAttribute("data-original-role"));
                        userCard.appendChild(revertBtn);

                        closePopup(); // ปิด Popup
                    } else {
                        alert("เกิดข้อผิดพลาดในการอัปเดต role");
                    }
                });
        }


        function revertRole(userId) {
            let userCard = document.getElementById(userId);
            let roleText = userCard.querySelector(".changerole-role-text");

            // ส่งคำขอไปยัง revert_role.php เพื่อเปลี่ยน Role กลับเป็น Previous_Role
            fetch("revert_role.php", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: "email=" + encodeURIComponent(userId)
                })
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "success") {
                        // ตั้งค่า role เป็นค่าที่ได้จาก Previous_Role
                        // (คุณสามารถเลือกให้ดึงจากฝั่ง server ก็ได้ หรือใส่ roleText เป็น placeholder)
                        roleText.innerText = userCard.getAttribute("data-original-role");

                        // ค้นหาทั้ง Event Container ที่ผู้ใช้อยู่
                        let eventContainer = userCard.closest('.followevent-setting-container-event');

                        // ย้ายกลับไปที่ "คืนค่าโรลแล้ว"
                        document.getElementById("revertedContent").appendChild(eventContainer);

                        // ลบปุ่ม "คืนค่าโรล"
                        let revertBtn = userCard.querySelector(".changerole-revert-btn");
                        if (revertBtn) {
                            revertBtn.remove();
                        }

                        // เพิ่มปุ่ม "เปลี่ยนโรล" กลับมา
                        //let changeBtn = document.createElement("button");
                        //changeBtn.className = "changerole-change-btn";
                        //changeBtn.innerText = "เปลี่ยนโรล";
                        //changeBtn.onclick = function() {S
                        //    openPopup(userId);
                        //};
                        //userCard.appendChild(changeBtn);
                    } else {
                        alert("เกิดข้อผิดพลาดในการคืนค่า role");
                    }
                });
        }


        function changeTab(tabName) {
            document.querySelectorAll('.changeRole-setting-navbar span').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');

            document.querySelectorAll('.changeRole-tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName + "Content").classList.add('active');
        }

        window.onload = populatePendingList;
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