<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php"); // ถ้าไม่ได้ล็อกอินให้กลับไปที่หน้า login
    exit();
}

include "../db.php"; // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการเข้าสู่ระบบ
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $action = $_POST['action']; // "approve" หรือ "reject"

    // สถานะการอนุมัติ
    $approval_status = ($action === "approve") ? 'อนุมัติ' : 'ไม่อนุมัติ';

    // สถานะในตาราง request_event ที่จะอัปเดตเป็น 'completed' เมื่ออนุมัติ
    $statuschange = ($action === "approve") ? 'completed' : 'not completed';

    // อัปเดตสถานะในตาราง request_event
    $sql = "UPDATE request_event SET request_event_status = ? WHERE request_event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $statuschange, $event_id);

    if ($stmt->execute()) {
        if ($action === "approve") {
            // บันทึกข้อมูลลงในตาราง event_approvals
            $approved_date = date("Y-m-d H:i:s");

            // เช็คว่าเคยบันทึกใน event_approvals แล้วหรือไม่
            $checkSql = "SELECT * FROM event_approvals WHERE request_event_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $event_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows == 0) { // ถ้ายังไม่มีข้อมูลใน event_approvals
                $sql = "INSERT INTO event_approvals (request_event_id, President_email, approved_date, approval_status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isss", $event_id, $email, $approved_date, $approval_status);
                $stmt->execute();
            }

            echo "อัปเดตสถานะเรียบร้อย!";
        } else {
            // ถ้าไม่อนุมัติ ให้ลบข้อมูลที่เกี่ยวข้องในตารางที่ต้องการ
            // ลบข้อมูลในตาราง event_approvals
            $deleteSql = "DELETE FROM event_approvals WHERE request_event_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $event_id);
            $deleteStmt->execute();

            // ลบข้อมูลในตาราง request_event
            $deleteEventSql = "DELETE FROM request_event WHERE request_event_id = ?";
            $deleteEventStmt = $conn->prepare($deleteEventSql);
            $deleteEventStmt->bind_param("i", $event_id);
            $deleteEventStmt->execute();

            echo "อีเว้นท์ไม่ถูกอนุมัติและข้อมูลถูกลบออก!";
        }
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }

    $stmt->close();
    exit();
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

// ดึงข้อมูลอีเว้นท์ที่ยังไม่ได้อนุมัติ
$sql = "SELECT request_event_id, Audience_email AS audience_email, request_event_name, request_event_detail,request_event_picture, request_event_date, request_event_price, request_event_time, request_event_location FROM request_event WHERE request_event_status = 'president'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <!-- font icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet"
        href="../assets/vendors/themify-icons/css/themify-icons.css">
    <!-- meyao -->
    <link rel="stylesheet" href="../assets/css/meyawo.css">
    <!-- font-awesome icons-->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap icons-->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Material icons-->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/icon?family=Material+Icons">
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
                <button class="navbar-close-popup" id="closePopupNavbar"><i
                        class="bi bi-x"></i></button>
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
                    placeholder="ค้นหางาน, ศิลปิน, จังหวัด หรือสถานที่จัดงาน"
                    id="searchInput" readonly>
            </div>

            <ul class="navbar-nav navbar-menu-items">
                <li class="navbar-nav-item">
                    <a class="nav-link" href="home.php"
                        data-target="home">หน้าแรก</a>
                </li>
                <li class="navbar-nav-item">
                    <a class="nav-link" href="approveEvent.php"
                        data-target="home">อนุมัติขอจัดอีเว้นท์</a>
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
                        <i class="bi bi-envelope"
                            style="font-size: 50px; color: #666;"></i>
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

    <div class="approveEvent-container">
        <div class="approveEvent-datail">
            <h2>อนุมัติขอจัดอีเว้นท์</h2>

            <!-- วนลูปแสดงอีเวนต์ -->
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="approveEvent-container-event" data-id="<?php echo htmlspecialchars($row["request_event_id"]); ?>">
                    <a href="approveEventdetail.php?request_event_id=<?php echo htmlspecialchars($row["request_event_id"]); ?>" class="approveEvent-detail"><i class="bi bi-pencil-square"></i> ดูรายละเอียด</a>

                    <!-- แสดงวันที่แบบ d, M Y, H:i -->
                    <div class="approveEvent-event-date text-center me-3">
                        <h3><?php echo date("d", strtotime($row["request_event_date"])); ?></h3>
                        <p class="mb-0"><?php echo date("M Y", strtotime($row["request_event_date"])); ?></p>
                        <p class="mb-0"><?php echo date("H:i", strtotime($row["request_event_time"])); ?></p>
                    </div>

                    <!-- รูปภาพกิจกรรม (ใส่รูปดีฟอลต์ไปก่อน) -->
                    <div class="approveEvent-container-event-Image">
                        <img src="../EventPicture/<?php echo htmlspecialchars($row["request_event_picture"]); ?>" alt="Event Image" class="me-3 rounded">
                    </div>

                    <!-- ข้อมูลเกี่ยวกับอีเวนต์ -->
                    <div class="approveEvent-event-details">
                        <h2>ผู้ขอจัดอีเว้นท์ : <?php echo htmlspecialchars($row["audience_email"]); ?></h2>
                        <div class="approveEvent-details-container">
                            <p>ชื่ออีเว้นท์: <?php echo htmlspecialchars($row["request_event_name"]); ?></p>
                            <p>ลงทะเบียน:
                                <?php
                                if ($row["request_event_price"] == 0) {
                                    echo '<span class="registration-status-freeregister">ฟรี</span>';
                                } else {
                                    echo '<span class="registration-status-freeregister">เสียเงิน</span>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <!-- ปุ่มต่าง ๆ เช่น "ผลการประเมิน" "อนุมัติ" -->
                    <div class="approveEvent-buttons">
                        <button class="button-detail-estimateEvent">ผลการประเมิน</button>

                        <!-- ป๊อปอัพผลการประเมิน -->
                        <div id="approveEvent-popup-detail-overlay" class="approveEvent-popup-detail-overlay">
                            <div class="approveEvent-popup-detail-box">
                                <h2>รายชื่อผู้ลงคะแนน</h2>
                                <span class="approveEvent-popup-detail-close">&times;</span>
                                <ul>
                                    <?php
                                    // ดึงรายชื่อคณะกรรมการจากตาราง vote, committee, request_event
                                    $event_id = $row["request_event_id"];  // รหัสอีเว้นท์
                                    $voteSql = "
                            SELECT c.committee_firstname, c.committee_lastname, v.vote_status 
                            FROM vote v
                            JOIN committee c ON v.committee_email = c.committee_email
                            JOIN request_event re ON v.request_event_id = re.request_event_id
                            WHERE v.request_event_id = ?";
                                    $voteStmt = $conn->prepare($voteSql);
                                    $voteStmt->bind_param("i", $event_id); // bind parameter
                                    $voteStmt->execute();
                                    $voteResult = $voteStmt->get_result();

                                    // แสดงชื่อคณะกรรมการ
                                    while ($voteRow = $voteResult->fetch_assoc()) {
                                        $committeeFullName = htmlspecialchars($voteRow["committee_firstname"]) . " " . htmlspecialchars($voteRow["committee_lastname"]);
                                        $status = htmlspecialchars($voteRow["vote_status"]);
                                        echo "<li>{$committeeFullName} <button class='approveEvent-popup-detail-approve'>{$status}</button></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <!-- ปุ่มอนุมัติ -->
                        <button class="button-detail-approveEvent-green">อนุมัติ</button>
                        <button class="button-detail-approveEvent-red">ไม่อนุมัติ</button>

                        <!-- ป๊อปอัพที่โผล่มาเมื่อกดอนุมัติ -->
                        <div class="approveEvent-popup-approve">
                            <div class="approveEvent-popup-approve-content">
                                <span class="approveEvent-popup-approve-close">&times;</span>
                                <h2>อนุมัติ</h2>
                                <button class="approveEvent-popup-approve-confirm">ยืนยันการอนุมัติ</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>

    <script src="../assets/js/navbar.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. เปิดป็อปอัพ "ผลการประเมิน"
            document.querySelectorAll(".button-detail-estimateEvent").forEach(button => {
                button.addEventListener("click", function() {
                    let popup = this.closest(".approveEvent-container-event").querySelector(".approveEvent-popup-detail-overlay");
                    if (popup) popup.style.display = "flex";
                });
            });

            // 2. ปิดป็อปอัพ "ผลการประเมิน"
            document.querySelectorAll(".approveEvent-popup-detail-close").forEach(button => {
                button.addEventListener("click", function() {
                    this.closest(".approveEvent-popup-detail-overlay").style.display = "none";
                });
            });

            // 3. เปิดป็อปอัพ "อนุมัติ"
            document.querySelectorAll(".button-detail-approveEvent-green").forEach(button => {
                button.addEventListener("click", function() {
                    let popup = this.closest(".approveEvent-container-event").querySelector(".approveEvent-popup-approve");
                    if (popup) popup.style.display = "flex";
                });
            });

            // 4. ปิดป็อปอัพ "อนุมัติ"
            document.querySelectorAll(".approveEvent-popup-approve-close").forEach(button => {
                button.addEventListener("click", function() {
                    this.closest(".approveEvent-popup-approve").style.display = "none";
                });
            });

            // 5. อัปเดตชื่อไฟล์เมื่อเลือกไฟล์
            document.querySelectorAll(".approveEvent-file-input").forEach(input => {
                input.addEventListener("change", function() {
                    let fileNameDisplay = this.closest(".approveEvent-popup-approve-content").querySelector(".approveEvent-file-name");
                    fileNameDisplay.textContent = this.files.length > 0 ? this.files[0].name : "ยังไม่ได้เลือกไฟล์";
                });
            });

            // 6. ปิดป็อปอัพเมื่อกดพื้นที่ดำ
            document.addEventListener("click", function(event) {
                if (event.target.classList.contains("approveEvent-popup-detail-overlay")) {
                    event.target.style.display = "none";
                }
                if (event.target.classList.contains("approveEvent-popup-approve")) {
                    event.target.style.display = "none";
                }
            });

            // 7. ตรวจสอบการแนบไฟล์ก่อนยืนยันการอนุมัติ
            document.querySelectorAll(".approveEvent-popup-approve-confirm").forEach(button => {
                button.addEventListener("click", function() {
                    let fileInput = this.closest(".approveEvent-popup-approve-content").querySelector(".approveEvent-file-input");
                    if (fileInput.files.length === 0) {
                        //alert("กรุณาแนบไฟล์ก่อนยืนยันการอนุมัติ");
                    } else {
                        // Submit the form or perform the approval action
                        // You can add your form submission logic here
                        alert("ไฟล์ถูกแนบแล้ว ยืนยันการอนุมัติ");
                    }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // ...existing code...

            // 8. Handle "ไม่อนุมัติ" button click
            document.querySelectorAll(".button-detail-approveEvent-red").forEach(button => {
                button.addEventListener("click", function() {
                    let eventId = this.closest(".approveEvent-container-event").dataset.id;
                    if (confirm("คุณแน่ใจหรือไม่ว่าต้องการไม่อนุมัติอีเว้นท์นี้?")) {
                        // Perform the rejection action
                        let formData = new FormData();
                        formData.append("event_id", eventId);
                        formData.append("action", "reject");

                        fetch("approveEvent.php", {
                                method: "POST",
                                body: formData
                            })
                            .then(response => response.text())
                            .then(data => {
                                alert(data);
                                location.reload();
                            })
                            .catch(error => console.error("Error:", error));
                    }
                });
            });

            // Handle "ยืนยันการอนุมัติ" button click
            document.querySelectorAll(".approveEvent-popup-approve-confirm").forEach(button => {
                button.addEventListener("click", function() {
                    let popupContent = this.closest(".approveEvent-popup-approve-content");
                    let eventId = this.closest(".approveEvent-container-event").dataset.id;

                    let formData = new FormData();
                    formData.append("event_id", eventId);
                    formData.append("action", "approve");

                    fetch("approveEvent.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                            location.reload();
                        })
                        .catch(error => console.error("Error:", error));
                });
            });
        });

        function openPopup(button, action) {
            let popup = button.closest(".approveEvent-container-event").querySelector(".approveEvent-popup-approve");
            if (popup) {
                document.getElementById('popupAction').value = action;
                popup.style.display = "flex";
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