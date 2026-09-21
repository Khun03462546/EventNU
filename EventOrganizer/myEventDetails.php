<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

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

$events = [];
$sql = "SELECT * FROM event WHERE EventOrganizer_email = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);  // สมมติว่าคุณมีตัวแปร $email มาก่อนหน้านี้
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

    $stmt->close();
} else {
    echo "เกิดข้อผิดพลาดในการ prepare SQL";
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

    <div class="eventAll-setting-container">
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
                <div id="editModal" class="eventAll-modal">
                    <div class="eventAll-modal-content">
                        <span class="eventAll-close" onclick="closeEditModal()">&times;</span>
                        <h3>แก้ไขอีเว้นท์</h3>
                        <form id="editForm">
                            <label for="editImage">รูปภาพ:</label>
                            <input type="file" id="editImage" accept="image/*" onchange="previewImage(event)">
                            <img id="previewImage" src="" alt="Preview"
                                style="width: 100%; margin-top: 10px; display: none;">

                            <label for="editTitle">ชื่ออีเว้นท์:</label>
                            <input type="text" id="editTitle" required>

                            <!-- เวลา (ใช้ input type="time") -->
                            <label for="editTime">เวลา:</label>
                            <input type="time" id="editTime" required>

                            <!-- วันที่ (ใช้ input type="date") -->
                            <label for="editDate">วันที่:</label>
                            <input type="date" id="editDate" required>

                            <label for="editLocation">สถานที่:</label>
                            <input type="text" id="editLocation" required>

                            <label for="editDetails">รายละเอียด:</label>
                            <textarea id="editDetails" required></textarea>

                            <!-- ประเภทบัตร (ใช้ dropdown) -->
                            <label for="editTicket">ประเภทบัตร:</label>
                            <select id="editTicket" required>
                                <option value="Free">Free</option>
                                <option value="Buy">Buy</option>
                            </select>

                            <button type="submit">บันทึก</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal สำหรับแก้ไขข้อมูล -->
        <div id="editModal" class="eventAll-modal" style="display: none;">
            <div class="eventAll-modal-content">
                <span class="eventAll-close" onclick="closeEditModal()">&times;</span>
                <h3>แก้ไขอีเว้นท์</h3>
                <form id="editForm">
                    <label for="editTitle">ชื่ออีเว้นท์:</label>
                    <input type="text" id="editTitle" required>
                    <label for="editTime">เวลา:</label>
                    <input type="time" id="editTime" required>
                    <label for="editDate">วันที่:</label>
                    <input type="date" id="editDate" required>
                    <label for="editLocation">สถานที่:</label>
                    <input type="text" id="editLocation" required>
                    <label for="editDetails">รายละเอียด:</label>
                    <textarea id="editDetails" required></textarea>
                    <label for="editTicketRelease">วันวางจำหน่ายตั๋ว:</label>
                    <input type="date" id="editTicketRelease">
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
                    const ticketReleaseDate = event.public_sale_date === "0000-00-00" ? "N/A" : formatDateForDisplay(event.public_sale_date);
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
                                eventLink = `eventDashboardFR.php?event_id=${event.EventID}`;
                            } else {
                                eventLink = `eventDashboardF.php?event_id=${event.EventID}`;
                            }
                        } else {
                            eventLink = `eventDashboard.php?event_id=${event.EventID}`;
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