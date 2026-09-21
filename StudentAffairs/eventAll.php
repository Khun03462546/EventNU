<?php
session_start();
include '../db.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

// 🔹 ถ้ายังไม่ได้ล็อกอิน ให้กลับไปที่หน้าล็อกอิน
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// 🔹 ตรวจสอบว่า `$email` ไม่เป็นค่าว่าง
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT first_name, studentaffairs_email FROM studentaffairs WHERE studentaffairs_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // 🔹 ถ้าไม่มีข้อมูลผู้ใช้ ให้หยุดทำงาน
    if (!$user) {
        echo "No user found with email: " . htmlspecialchars($email);
        exit();
    }
} else {
    echo "Invalid email!";
    exit();
}

// 🔹 ดึงข้อมูลอีเวนต์ทั้งหมดจากฐานข้อมูล
$events = [];
$sql = "SELECT * FROM event";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

foreach ($events as &$event) {
    $event['public_sale_date'] = ($event['public_sale_date'] == '0000-00-00') ? 'N/A' : $event['public_sale_date'];
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
                    <span><?php echo htmlspecialchars($user['first_name']); ?></span>
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
                    <a href="eventAll.php">อีเว้นท์ทั้งหมด</a>
                    <a href="allOrder.php">คำสั่งซื้อทั้งหมด</a>
                    <a href="addEvent.php">เพิ่มอีเว้นท์</a>
                    <a href="changeRole.php">คำขอจัดอีเว้นท์</a>
                    <a href="setting.php">ข้อมูลส่วนตัว</a>
                    <hr>
                    <a href="../logout.php" class="text-danger">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </nav>
    <!-- end navbar -->

    <div class="eventAll-setting-container">
        <div class="eventAll-setting-container-data">
            <div class="eventAll-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="eventAll-profile-img"></div>
                <h2><?php
                    if (isset($user)) {
                        echo htmlspecialchars($user['first_name']);
                    } else {
                        echo 'Guest';
                    }
                    ?></h2>
                <p><?php echo htmlspecialchars($user['studentaffairs_email']); ?></p>
            </div>

            <div class="eventAll-setting-menu">
                <a href="eventAll.php" class="eventAll-setting-menu-item active"><i
                        class="bi bi-ticket-perforated-fill"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="allOrder.php" class="eventAll-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="addEvent.php" class="eventAll-setting-menu-item"><i class="bi bi-clipboard2-plus"></i>
                    เพิ่มอีเว้นท์</a>
                <a href="changeRole.php" class="eventAll-setting-menu-item"><i class="bi bi-person-plus"></i>
                    คำขอจัดอีเว้นท์</a>
                <a href="setting.php" class="eventAll-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="../logout.php" class="eventAll-setting-menu-item eventAll-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="eventAll-container">
            <div class="eventAll-name">
                <h3>อีเว้นท์ทั้งหมด</h3>
                <div class="eventAll-filter-container">
                    <select id="eventFilter" class="eventAll-filter-dropdown">
                        <option value="all">เรียงตามวันที่ (ค่าเริ่มต้น)</option>
                        <option value="free">เฉพาะอีเว้นท์ที่ฟรี</option>
                        <option value="paid">เฉพาะอีเว้นท์ที่ต้องเสียเงิน</option>
                        <option value="ongoing">อีเว้นท์ที่กำลังดำเนินการ</option>
                        <option value="past">อีเว้นท์ที่จบไปแล้ว</option>
                    </select>
                </div>
            </div>
            <div class="eventAll-card-container">
                <!-- Edit Modal -->
            </div>
        </div>
        <!-- Modal สำหรับแก้ไขข้อมูล -->
        <div id="editModal" class="eventAll-modal" style="display: none;">
            <div class="eventAll-modal-content">
                <span class="eventAll-close" onclick="closeEditModal()">&times;</span>
                <h3>แก้ไขอีเว้นท์</h3>
                <form method="POST" id="editForm">
                    <input type="hidden" name="eventName" id="editEventName">
                    <label for="editImage">รูปภาพ:</label>
                    <input type="file" id="editImage" accept="image/*" onchange="previewImage(event)">
                    <img id="previewImage" src="" alt="Preview" style="width: 100%; margin-top: 10px; display: none;">

                    <label for="editTitle">ชื่ออีเว้นท์:</label>
                    <input type="text" id="editTitle" name="eventName" required>

                    <label for="editTime">เวลา:</label>
                    <input type="time" id="editTime" name="eventTime" required>

                    <label for="editDate">วันที่:</label>
                    <input type="date" id="editDate" name="eventDate" required>

                    <label for="editLocation">สถานที่:</label>
                    <input type="text" id="editLocation" name="eventLocation" required>

                    <label for="editDetails">รายละเอียด:</label>
                    <textarea id="editDetails" name="eventDetails" required></textarea>

                    <label for="editTicketRelease">วันวางจำหน่ายตั๋ว:</label>
                    <input type="date" id="editTicketRelease" name="ticketReleaseDate">

                    <button type="submit">บันทึก</button>
                </form>

            </div>
        </div>


    </div>
    <script src="../assets/js/navbar.js"></script>
    <script>
        // แปลงเวลาให้แสดงในรูปแบบที่ต้องการ (HH:MM)
        function formatTimeForDisplay(time) {
            return time; // เวลาในรูปแบบ HH:MM
        }

        // แปลงเวลาให้ตรงกับ input type="time" (HH:MM)
        function formatTimeForInput(timeText) {
            return timeText; // เวลาในรูปแบบ HH:MM ตรงกับ input type="time"
        }


        // แปลงวันที่ให้ตรงกับ input type="date" (YYYY-MM-DD)
        function formatDateForInput(dateText) {
            const [day, month, year] = dateText.split(' ');
            const monthMap = {
                'ม.ค.': '01',
                'ก.พ.': '02',
                'มี.ค.': '03',
                'เม.ย.': '04',
                'พ.ค.': '05',
                'มิ.ย.': '06',
                'ก.ค.': '07',
                'ส.ค.': '08',
                'ก.ย.': '09',
                'ต.ค.': '10',
                'พ.ย.': '11',
                'ธ.ค.': '12'
            };

            return `${year}-${monthMap[month]}-${day.padStart(2, '0')}`;
        }


        // แปลงวันที่ให้แสดงในรูปแบบที่ต้องการ (DD MMM YYYY)
        function formatDateForDisplay(date) {
            const [year, month, day] = date.split('-');
            const monthMap = {
                '01': 'ม.ค.',
                '02': 'ก.พ.',
                '03': 'มี.ค.',
                '04': 'เม.ย.',
                '05': 'พ.ค.',
                '06': 'มิ.ย.',
                '07': 'ก.ค.',
                '08': 'ส.ค.',
                '09': 'ก.ย.',
                '10': 'ต.ค.',
                '11': 'พ.ย.',
                '12': 'ธ.ค.'
            };
            return `${day} ${monthMap[month]} ${year}`;
        }

        const events = <?php echo json_encode($events); ?>;
        console.log(events);
        console.log(typeof events, events);


        document.addEventListener("DOMContentLoaded", function() {
            const eventContainer = document.querySelector(".eventAll-card-container");

            // ฟังก์ชันสำหรับแปลงวันที่ให้เป็นรูปแบบที่ต้องการ (DD MMM YYYY)
            function formatDateForDisplay(date) {
                const [year, month, day] = date.split('-');
                const monthMap = {
                    '01': 'ม.ค.',
                    '02': 'ก.พ.',
                    '03': 'มี.ค.',
                    '04': 'เม.ย.',
                    '05': 'พ.ค.',
                    '06': 'มิ.ย.',
                    '07': 'ก.ค.',
                    '08': 'ส.ค.',
                    '09': 'ก.ย.',
                    '10': 'ต.ค.',
                    '11': 'พ.ย.',
                    '12': 'ธ.ค.'
                };
                return `${day} ${monthMap[month]} ${year}`;
            }
            console.log("Events data:", events);
            console.log("eventContainer:", eventContainer);
            console.log("eventAll-card-container:", document.querySelector(".eventAll-card-container"));

            // ฟังก์ชันการโหลดอีเว้นท์
            function loadEvents(filter = "all") {
                console.log("🧐 loadEvents called with filter:", filter);

                eventContainer.innerHTML = ""; // ล้างข้อมูลเก่าก่อนโหลดใหม่
                let currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0); // ตัดเวลาส่วนชั่วโมงออกเพื่อเทียบเฉพาะวันที่

                console.log("📢 ข้อมูลทั้งหมด:", events);
                if (!Array.isArray(events) || events.length === 0) {
                    console.warn("⚠️ ไม่มีอีเว้นท์ที่โหลดได้!");
                    eventContainer.innerHTML = "<p>ไม่มีข้อมูลอีเว้นท์</p>";
                    return;
                }

                // เรียงอีเว้นท์จากวันที่เก่าก่อน → ใหม่ทีหลัง
                events.sort((a, b) => new Date(a.Event_Date) - new Date(b.Event_Date));

                let eventCount = 0; // นับจำนวนอีเว้นท์ที่ถูกแสดง
                events.forEach(event => {
                    let eventDateObj = new Date(event.Event_Date);
                    eventDateObj.setHours(0, 0, 0, 0); // ปรับเวลาให้ตรงกับ currentDate
                    const eventDate = formatDateForDisplay(event.Event_Date);
                    const ticketReleaseDate = event.public_sale_date === "N/A" ? "N/A" : formatDateForDisplay(event.public_sale_date);
                    const isFree = event.Event_Price == 0;
                    let showEvent = false;

                    switch (filter) {
                        case "all":
                            showEvent = true;
                            break;
                        case "free":
                            showEvent = isFree;
                            break;
                        case "paid":
                            showEvent = !isFree;
                            break;
                        case "ongoing":
                            showEvent = eventDateObj.getTime() >= currentDate.getTime();
                            break;
                        case "past":
                            showEvent = eventDateObj.getTime() < currentDate.getTime();
                            break;
                    }

                    if (showEvent) {
                        const eventCard = document.createElement("div");
                        eventCard.classList.add("eventAllpre-card");

                        const buttonLabel = eventDateObj.getTime() < currentDate.getTime() ? "Feedback" : "Detail";
                        let eventLink = "";

                        if (event.Event_Price == 0) {
                            if (event.TypeRegister.toLowerCase() === "yes") {
                                eventLink = `affairDashboardFR.php?event_id=${event.EventID}`;
                            } else {
                                eventLink = `affairDashboardF.php?event_id=${event.EventID}`;
                            }
                        } else {
                            eventLink = `affairDashboard.php?event_id=${event.EventID}`;
                        }

                        eventCard.innerHTML = `
    <div class="eventAll-card-actions">
        <button class="edit-btn" onclick="openEditModal(this)"><i class="bi bi-pencil-square"></i></button>
        <button class="delete-btn" onclick="deleteEvent(this)"><i class="bi bi-trash3"></i></button>
    </div>
    <div class="eventAll-card-image">
        <img src="${event.Event_Picture}" alt="Event Image">
    </div>
    <div class="eventAll-card-content">
        <h3 class="eventTitle">${event.Event_Name}</h3>
        <p class="eventTime"><i class="fas fa-clock"></i> เวลา: ${event.Event_Time}</p>
        <p class="eventDate"><i class="fas fa-calendar-alt"></i> วันที่: ${eventDate}</p>
        <p class="eventTicketRelease"><i class="fas fa-ticket"></i> วันวางจำหน่ายตั๋ว: ${ticketReleaseDate}</p>
        <p class="eventAll-eventLocation"><i class="fas fa-map-marker-alt"></i> สถานที่: ${event.Event_Location}</p>
        <p class="eventAll-location-details">${event.Event_Detail}</p>
        <div class="eventAllpre-card-footer">
            <span class="eventTicket"><i class="fas fa-ticket-alt"></i> ${isFree ? "Free" : event.Event_Price + " Baht"}</span>
            <a href="${eventLink}" class="eventAllpre-detail-btn">${buttonLabel}</a>
        </div>
    </div>
`;


                        eventContainer.appendChild(eventCard);
                        eventCount++;
                    }
                });

                console.log(`✅ โหลด ${eventCount} อีเว้นท์สำเร็จ`);
            }

            // ฟังการเปลี่ยนค่าของ dropdown
            const filterDropdown = document.getElementById("eventFilter");
            if (filterDropdown) {
                filterDropdown.addEventListener("change", function() {
                    console.log("✅ Filter changed to:", this.value);
                    loadEvents(this.value);
                });

                // โหลดอีเว้นท์ครั้งแรกตามค่าที่เลือก
                loadEvents(filterDropdown.value);
            } else {
                console.error("❌ ไม่พบ dropdown #eventFilter");
            }

            // ฟังก์ชันเปิด/ปิด Edit Modal
            function openEditModal(button) {
                const card = button.closest(".eventAll-card");
                document.getElementById("editModal").dataset.editingCard = card;
                document.getElementById("editTitle").value = card.querySelector(".eventTitle").innerText;
                document.getElementById("editTime").value = card.querySelector(".eventTime").innerText.replace("เวลา: ", "");
                document.getElementById("editDate").value = card.querySelector(".eventDate").innerText.replace("วันที่: ", "");
                document.getElementById("editLocation").value = card.querySelector(".eventAll-eventLocation").innerText.replace("สถานที่: ", "");
                document.getElementById("editDetails").value = card.querySelector(".eventAll-location-details").innerText;
                document.getElementById("editModal").style.display = "flex";
            }

            function closeEditModal() {
                document.getElementById("editModal").style.display = "none";
            }
        });




        function deleteEvent(button) {
            // หาพาเรนต์ของปุ่ม (ซึ่งก็คือ .eventAll-card)
            var card = button.closest('.eventAll-card');
            var eventTitle = card.querySelector(".eventTitle").innerText; // ดึงชื่ออีเว้นท์

            // แสดงข้อความยืนยันการลบ
            var confirmation = confirm('Are you sure you want to delete the event: ' + eventTitle + '?');
            if (confirmation) {
                // ส่งคำขอลบไปยังเซิร์ฟเวอร์
                fetch('delete_event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            title: eventTitle
                        })
                    })
                    .then(response => response.text()) // ใช้ .text() แทน .json() เพื่อดูก่อนว่าเซิร์ฟเวอร์ส่งอะไรกลับมา
                    .then(data => {
                        console.log("Server Response:", data); // ดูข้อมูลที่ได้รับจากเซิร์ฟเวอร์
                        try {
                            const jsonResponse = JSON.parse(data); // พยายามแปลงข้อมูลเป็น JSON
                            if (jsonResponse.success) {
                                card.remove();
                            } else {
                                alert('Failed to delete event: ' + jsonResponse.message);
                            }
                        } catch (e) {
                            console.error("Error parsing JSON:", e);
                            alert("Failed to parse response from server.");
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the event.');
                    });

            }
        }


        document.getElementById("editForm").addEventListener("submit", function() {
            const editingCard = document.getElementById("editModal").dataset.editingCard;
            saveEventFromForm(editingCard);
        });

        function openEditModal(button) {
            const card = button.closest(".eventAll-card");
            if (!card) return;

            document.getElementById("editModal").dataset.editingCard = card;
            document.getElementById("editTitle").value = card.querySelector(".eventTitle")?.innerText.trim() || "";
            document.getElementById("editTime").value = card.querySelector(".eventTime")?.innerText.replace("เวลา: ", "").trim() || "";
            document.getElementById("editDate").value = card.querySelector(".eventDate")?.innerText.replace("วันที่: ", "").trim() || "";
            document.getElementById("editLocation").value = card.querySelector(".eventLocation a")?.innerText.trim() || "";
            document.getElementById("editDetails").value = card.querySelector(".eventAll-location-details")?.innerText.trim() || "";
            document.getElementById("editTicketRelease").value = card.querySelector(".eventTicketRelease")?.innerText.replace("วันวางจำหน่ายตั๋ว: ", "").trim() || "";

            document.getElementById("editModal").style.display = "flex";
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }
    </script>

</body>

</html>