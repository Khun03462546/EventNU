<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่ได้ล็อกอินให้กลับไปที่หน้า login
    exit();
}

include "../db.php"; // เชื่อมต่อฐานข้อมูล

$email = $_SESSION['email']; // ดึงอีเมลจาก Session
$firstname = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : "User"; // ถ้าไม่มีชื่อให้ใช้ 'User'

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
                    <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                    <span><?php echo htmlspecialchars($firstname); ?></span>
                </button>

                <div class="user-menu" id="userMenu">
                    <div class="d-flex align-items-center px-3 py-2">
                        <img src="../assets/imgs/jusmine.png" alt="Profile" class="profile-img">
                        <div>
                            <p class="m-0"><?php echo htmlspecialchars($firstname); ?><br>
                                <small><?php echo htmlspecialchars($email); ?></small></p>
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
     
    <div class="eventAllpre-setting-container">
        <div class="eventAllpre-setting-container-data">
            <div class="eventAllpre-setting-profile">
                <div class="d-flex align-items-center px-3 py-2"><img src="../assets/imgs/jusmine.png" alt="Profile"
                        class="eventAllpre-profile-img"></div>
                <h2>หนูตาแบ๊ว นะคะ</h2>
                <p>sugusguss112233@gmail.com</p>
            </div>

            <div class="eventAllpre-setting-menu">
                <a href="eventAllpre.html" class="eventAllpre-setting-menu-item"><i class="bi bi-ticket-perforated"></i>
                    อีเว้นท์ทั้งหมด</a>
                <a href="checkBack.html" class="eventAllpre-setting-menu-item"><i class="bi bi-clock-history"></i>
                    คำสั่งซื้อทั้งหมด</a>
                <a href="followSum.html" class="eventAllpre-setting-menu-item  active">
                    <i class="bi bi-calendar-fill"></i> ติดตามอีเว้นท์
                </a>
                <a href="setting.html" class="eventAllpre-setting-menu-item "><i class="bi bi-gear"></i>
                    ข้อมูลส่วนตัว</a>
                <a href="#" class="eventAllpre-setting-menu-item eventAllpre-setting-logout"><i
                        class="bi bi-box-arrow-right"></i>
                    ออกจากระบบ</a>
            </div>
        </div>
        <div class="followSum-container">
            <div class="followSum-event-year-selector">
                <h2>เลือกปี</h2>
                <select id="yearSelect" class="followSum-year-select">
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>

            <div class="followSum-event-list-container">
                <h2>อีเว้นท์ที่จัดในปี <span id="selectedYear">2025</span></h2>
                <div class="followSum-event-category-list" id="eventCategoryList">
                </div>
                <div class="followSum-event-list" id="eventList">
                </div>

                <!-- ช่องค้นหาสำหรับรหัสนิสิต -->
                <div class="followSum-student-search">
                    <h2>ค้นหาด้วยรหัสนิสิต</h2>
                    <input type="text" id="studentIdInput" placeholder="กรอกรหัสนิสิต" />
                    <button id="followSum-searchStudentBtn">ค้นหา</button>
                </div>

                <!-- แสดงข้อมูลนิสิตในรูปแบบตาราง -->
                <div id="studentInfo" style="display: none;">
                    <h3>ข้อมูลนิสิต</h3>
                    <table id="studentTable" border="1">
                        <thead>
                            <tr>
                                <th>รหัสนิสิต</th>
                                <th>ชื่อ</th>
                                <th>อีเมล</th>
                                <th>คณะ</th>
                                <th>สาขา</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                    <div id="studentEventsList"></div>
                </div>
            </div>
        </div>


        <script src="../assets/js/navbar.js"></script>
        <script>
            // ตัวอย่างข้อมูลนิสิตและการเข้าร่วมอีเว้นท์
            const studentData = {
                "12345": {
                    name: "นิสิต 1",
                    email: "student1@email.com",
                    faculty: "คณะวิศวกรรมศาสตร์",  // คณะ
                    department: "สาขาวิศวกรรมคอมพิวเตอร์",  // สาขา
                    events: ["Theater", "Concert"]
                },
                "67890": {
                    name: "นิสิต 2",
                    email: "student2@email.com",
                    faculty: "คณะวิทยาศาสตร์",  // คณะ
                    department: "สาขาวิทยาการคอมพิวเตอร์",  // สาขา
                    events: ["Charity", "Theater"]
                }
            };

            // ฟังก์ชันค้นหาข้อมูลนิสิต
            document.getElementById('followSum-searchStudentBtn').addEventListener('click', function () {
                const studentId = document.getElementById('studentIdInput').value;
                const studentInfo = studentData[studentId];

                const studentInfoDiv = document.getElementById('studentInfo');
                const studentEventsList = document.getElementById('studentEventsList');
                const studentTableBody = document.getElementById('studentTable').getElementsByTagName('tbody')[0];

                // รีเซ็ตข้อมูลที่แสดง
                studentEventsList.innerHTML = '';
                studentTableBody.innerHTML = '';

                if (studentInfo) {
                    studentInfoDiv.style.display = 'block'; // แสดงข้อมูลนิสิต

                    // เพิ่มข้อมูลนิสิตลงในตาราง
                    const row = studentTableBody.insertRow();
                    row.insertCell(0).innerText = studentId;
                    row.insertCell(1).innerText = studentInfo.name;
                    row.insertCell(2).innerText = studentInfo.email;
                    row.insertCell(3).innerText = studentInfo.faculty;  // แสดงคณะ
                    row.insertCell(4).innerText = studentInfo.department;  // แสดงสาขา

                    // แสดงหมวดหมู่และอีเว้นท์ที่นิสิตเข้าร่วม
                    studentInfo.events.forEach(eventCategory => {
                        const categoryDiv = document.createElement('div');
                        categoryDiv.className = 'category-container';
                        categoryDiv.innerHTML = `<strong>หมวดหมู่:</strong> ${eventCategory}`;

                        const eventList = document.createElement('div');
                        eventList.className = 'event-list-container';

                        const eventsInCategory = eventData[2025][eventCategory]?.events || [];

                        eventsInCategory.forEach(event => {
                            const eventCard = document.createElement('div');
                            eventCard.className = 'favorite-setting-container-event';

                            eventCard.innerHTML = `
            <div class="favorite-ticket-body" style="height: 90px;">
                <img src="${event.img}" alt="Event Image" class="favorite-ticket-image">
                <div class="favorite-ticket-details">
                    <p>${event.date} | ${event.time}</p>
                    <h4>${event.name}</h4>
                    <p><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                </div>
            </div>
        `;

                            eventList.appendChild(eventCard);
                        });

                        categoryDiv.appendChild(eventList);
                        studentEventsList.appendChild(categoryDiv);
                    });

                    // หากไม่พบข้อมูล
                    if (studentInfo.events.length === 0) {
                        studentInfoDiv.style.display = 'none'; // ซ่อนข้อมูลถ้าไม่พบรหัสนิสิต
                        alert("ไม่พบข้อมูลนิสิตนี้");
                    }

                };
            });


            const eventData = {
                2025: {
                    "Theater": {
                        interested: 1500,
                        events: [
                            { name: "Rock Festival", date: "10 ม.ค. 2025", time: "18:00", location: "Bangkok Arena", img: "../assets/imgs/jusmine.png" },
                            { name: "EDM Night", date: "22 ก.พ. 2025", time: "20:00", location: "Impact Arena", img: "../assets/imgs/jusmine.png" }
                        ]
                    },
                    "Charity": {
                        interested: 900,
                        events: [
                            { name: "AI Conference 2025", date: "5 มี.ค. 2025", time: "09:00", location: "True Digital Park", img: "../assets/imgs/jusmine.png" }
                        ]
                    },
                    "Concert": {
                        interested: 1200,
                        events: [
                            { name: "Book Fair 2025", date: "14 เม.ย. 2025", time: "10:00", location: "Queen Sirikit Center", img: "../assets/imgs/jusmine.png" },
                            { name: "Food Expo", date: "30 พ.ค. 2025", time: "12:00", location: "BITEC Bangna", img: "foodexpo.jpg" }
                        ]
                    }
                }
            };

            function updateEventSummary() {
                const year = document.getElementById('yearSelect').value;

                // ถ้าไม่ได้เลือกปี, หยุดการทำงาน และซ่อนทั้งหมวดหมู่และอีเว้นท์
                if (!year) {
                    document.getElementById('eventCategoryList').innerHTML = '';
                    document.getElementById('eventList').innerHTML = '';
                    document.getElementById('eventList').style.display = 'none';  // ซ่อน eventList
                    return;
                }

                // หากเลือกปีแล้ว
                document.getElementById('eventList').style.display = 'none';  // ซ่อน eventList ก่อนเลือกหมวดหมู่
                const categories = eventData[year];

                document.getElementById('selectedYear').textContent = year;

                const categoryList = document.getElementById('eventCategoryList');
                categoryList.innerHTML = '';

                const eventList = document.getElementById('eventList');
                eventList.innerHTML = '';

                // แสดงหมวดหมู่ทั้งหมด
                for (const [category, details] of Object.entries(categories)) {
                    const categoryCard = document.createElement('div');
                    categoryCard.className = 'followSum-event-category-card';

                    // เลือกไอคอนตามหมวดหมู่
                    let iconClass = 'fas fa-book';
                    if (category === 'Theater') {
                        iconClass = 'fas fa-theater-masks';
                    } else if (category === 'Charity') {
                        iconClass = 'fas fa-gift';
                    } else if (category === 'Concert') {
                        iconClass = 'fas fa-music';
                    }

                    // สร้างโค้ด HTML สำหรับหมวดหมู่ พร้อมไอคอน
                    categoryCard.innerHTML = `
                        <div class="followSum-icon-container">
                            <i class="${iconClass}"></i>
                        </div>
                        <h3>${category}</h3>
                        <p>${details.interested} คนสนใจ</p>
                    `;

                    categoryCard.onclick = () => {
                        toggleEventList(category, details.events);
                    };
                    categoryList.appendChild(categoryCard);
                }
            }

            // ฟังก์ชันแสดงอีเว้นท์เมื่อเลือกหมวดหมู่
            function toggleEventList(category, events) {
                const eventList = document.getElementById('eventList');
                eventList.innerHTML = '';  // เคลียร์รายการอีเว้นท์

                // แสดงหัวข้อหมวดหมู่
                const categoryHeader = document.createElement('h3');
                categoryHeader.textContent = `อีเว้นท์ในหมวดหมู่: ${category}`;
                eventList.appendChild(categoryHeader);

                // แสดงอีเว้นท์ในหมวดหมู่
                events.forEach(event => {
                    const eventCard = document.createElement('div');
                    eventCard.className = 'favorite-setting-container-event';
                    eventCard.innerHTML = `
                        <div class="favorite-ticket-body">
                            <img src="${event.img}" alt="Event Image" class="favorite-ticket-image">
                            <div class="favorite-ticket-details">
                                <p>${event.date} | ${event.time}</p>
                                <h4>${event.name}</h4>
                                <p><i class="bi bi-geo-alt-fill"></i> ${event.location}</p>
                            </div>
                        </div>
                    `;
                    eventList.appendChild(eventCard);
                });

                // แสดง eventList เมื่อมีการเลือกหมวดหมู่
                eventList.style.display = 'block';  // แสดง eventList
            }


            // ฟังก์ชันสลับการแจ้งเตือน
            function toggleNotification(icon) {
                icon.classList.toggle('active');
            }

            // อัปเดตข้อมูลเมื่อเปลี่ยนปี
            document.getElementById('yearSelect').addEventListener('change', updateEventSummary);

            // โหลดข้อมูลเริ่มต้น
            window.onload = updateEventSummary;


        </script>
</body>

</html>