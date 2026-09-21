<?php
session_start();
include "../db.php"; // เชื่อมต่อฐานข้อมูล

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT * FROM Audience WHERE Audience_email = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_name = $row['Audience_FirstName'];
    $last_name = $row['Audience_LastName'];
    $faculty = $row['FacultyID'];
    $role = $row['Audience_Role'];
    $image = $row['Audience_Image'];
} else {
    echo "User not found!";
    exit();
}

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    echo "Event not found!";
    exit();
}

$event_id = $_GET['event_id'];

$sql = "SELECT * FROM Event WHERE EventID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $event_picture = $row['Event_Picture'];
    $event_name = $row['Event_Name'];
    $event_date = $row['Event_Date'];
    $event_time = $row['Event_Time'];
    $event_location = $row['Event_Location'];
    $event_price = $row['Event_Price'];
    $event_detail = $row['Event_Detail'];
    $event_id = $row['EventID'];
    $eventTypeRe = $row['TypeRegister'];
} else {
    echo "No event found.";
}

$price_display = ($event_price == 0) ? "Free" : $event_price;
$event_date_formatted = date("d F Y", strtotime($event_date));
$event_time_formatted = date("H:i", strtotime($event_time));

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

$day_of_week = $days_th[date("l", strtotime($event_date))];
$day = date("d", strtotime($event_date));
$month = $months_th[date("m", strtotime($event_date))];
$year = date("Y", strtotime($event_date)) + 543;

$sql = "SELECT f.feedback_comment, f.feedback_point, f.feedback_option, a.Audience_FirstName, a.Audience_LastName 
        FROM feedback f
        JOIN Audience a ON f.Audience_email = a.Audience_email
        WHERE f.EventID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error in preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$feedback_result = $stmt->get_result();

$feedbacks = [];
if ($feedback_result->num_rows > 0) {
    while ($feedback_row = $feedback_result->fetch_assoc()) {
        $feedbacks[] = [
            "name" => $feedback_row['Audience_FirstName'] . " " . $feedback_row['Audience_LastName'],
            "comment" => $feedback_row['feedback_comment'],
            "point" => $feedback_row['feedback_point'],
            "option" => json_decode($feedback_row['feedback_option'], true),
        ];
    }
}
// แปลง array ของ feedbacks เป็น JSON และส่งไปยัง JavaScript
$feedbacks_json = json_encode($feedbacks, JSON_UNESCAPED_UNICODE);

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

<body>
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
                    <a href="ticketsoon.html">บัตรของฉัน</a>
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

    <div class="aFeedback-body">
        <div class="aFeedback-container">
            <div class="aFeedback-name">
                <h3>Review</h3>
                <div class="aFeedback-navbar">
                    <span id="ratingTab" class="active" onclick="changeTab('rating')">Rating Event</span>
                    <span id="commentTab" onclick="changeTab('comment')">Add Comment</span>
                </div>
            </div>
            <div class="aFeedback-content">
                <div id="ratingContent" class="aFeedback-tab-content active">
                    <div class="aFeedback-rating-in-event">
                        <label class="aFeedback-rating-option" onclick="showContent('all')">
                            ทั้งหมด <br>
                            all
                        </label>
                        <label class="aFeedback-rating-option" onclick="showContent('stars')">
                            ดาว ⭐<br>
                            all
                        </label>
                        <label class="aFeedback-rating-option" onclick="showContent('select')">
                            ตัวเลือก <br>
                            all
                        </label>
                    </div>

                    <div id="allContent" class="aFeedback-content-section active">
                        📜 แสดงความคิดเห็นทั้งหมด
                        <span id="totalCommentsAll">(0)</span>
                        <div id="comment-section"></div>
                    </div>

                    <div id="starsContent" class="aFeedback-content-section"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        ⭐ ตัวเลือกระดับดาว
                        <span id="totalCommentsStars">(0)</span>
                        <select class="aFeedback-star-level" id="starFilter" onchange="filterByStars()()">
                            <option value="5">5 ดาว</option>
                            <option value="4">4 ดาว</option>
                            <option value="3">3 ดาว</option>
                            <option value="2">2 ดาว</option>
                            <option value="1">1 ดาว</option>
                        </select>
                        <div id="comment-star-section"></div>
                    </div>

                    <div id="selectContent" class="aFeedback-content-section"
                        style="display: flex; flex-direction: column; gap: 10px;">
                        <label for="optionFilter">🏷️ เลือกตัวเลือก</label>
                        <span id="totalCommentsSelection">(0)</span>
                        <select class="aFeedback-dropdown" id="optionFilter" onchange="filterByOption()">
                            <option value="">-- กรุณาเลือก --</option>
                            <option value="สถานที่เหมาะสม">สถานที่เหมาะสม</option>
                            <option value="ความสะอาด">ความสะอาด</option>
                            <option value="ร้านค้า">ร้านค้า</option>
                        </select>
                        <div id="comment-option-section"></div>
                    </div>
                    <div id="option1Content" class="aFeedback-content-section" style="display: none;">
                    </div>
                    <div id="option2Content" class="aFeedback-content-section" style="display: none;">
                    </div>
                    <div id="option3Content" class="aFeedback-content-section" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
        <div id="commentContent" class="aFeedback-tab-content">
            <div class="aFeedback-comment-in-event">
                <div class="aFeedback-activity-info">
                    <div class="aFeedback-activity-image">
                        <img src="../assets/imgs/jusmine.png" alt="กิจกรรม">
                    </div>
                    <div class="aFeedback-activity-name"><?php echo $first_name; ?></div>
                </div>

                <div class="aFeedback-rating-stars">
                    <span>ให้คะแนนอีเว้นท์</span> <br>
                    <span class="aFeedback-star" onclick="rateActivity(1)"><i class="fas fa-star"></i></span>
                    <span class="aFeedback-star" onclick="rateActivity(2)"><i class="fas fa-star"></i></span>
                    <span class="aFeedback-star" onclick="rateActivity(3)"><i class="fas fa-star"></i></span>
                    <span class="aFeedback-star" onclick="rateActivity(4)"><i class="fas fa-star"></i></span>
                    <span class="aFeedback-star" onclick="rateActivity(5)"><i class="fas fa-star"></i></span>
                </div>

                <div class="aFeedback-rating-message" id="ratingMessage" style="display: none;">
                    <div class="aFeedback-dropdown-comment">
                        <div class="aFeedback-dropdown-item">
                            <label for="dropdown1">สถานที่เหมาะสม</label>
                            <select id="dropdown1" class="aFeedback-dropdown">
                                <option value="">เลือก</option>
                                <option value="option1">มาก</option>
                                <option value="option2">ปานกลาง</option>
                                <option value="option3">น้อย</option>
                            </select>
                        </div>
                        <div class="aFeedback-dropdown-item">
                            <label for="dropdown2">ความสะอาด</label>
                            <select id="dropdown2" class="aFeedback-dropdown">
                                <option value="">เลือก</option>
                                <option value="option1">มาก</option>
                                <option value="option2">ปานกลาง</option>
                                <option value="option3">น้อย</option>
                            </select>
                        </div>
                        <div class="aFeedback-dropdown-item">
                            <label for="dropdown3">ร้านค้า</label>
                            <select id="dropdown3" class="aFeedback-dropdown">
                                <option value="">เลือก</option>
                                <option value="option1">มาก</option>
                                <option value="option2">ปานกลาง</option>
                                <option value="option3">น้อย</option>
                            </select>
                        </div>
                    </div>
                    <textarea id="feedbackComment" placeholder="เพิ่มคอมเม้น"></textarea>
                </div>

                <div class="aFeedback-buttons">
                    <button type="button" class="btn btn-secondary" onclick="cancelComment()">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="submitComment()">ส่ง</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ดึงข้อมูล JSON ที่ถูกสร้างจาก PHP
            let feedbacks = <?php echo $feedbacks_json; ?>;
            let comments = [];

            feedbacks.forEach(fb => {
                // แปลงคะแนนเป็น rating ตามเงื่อนไข
                let rating = 1;
                if (fb.point >= 15) {
                    rating = 5;
                } else if (fb.point >= 12) {
                    rating = 4;
                } else if (fb.point >= 9) {
                    rating = 3;
                } else if (fb.point >= 6) {
                    rating = 2;
                }

                // สร้างข้อมูลแต่ละรายการ
                comments.push({
                    username: fb.name,
                    profilePic: "../assets/imgs/jusmine.png",
                    rating: rating,
                    options: fb.option, // ตัวเลือกที่ผู้ใช้เลือก
                    comment: fb.comment
                });
            });

            console.log(comments); // ทดสอบว่าข้อมูลถูกต้อง


            const commentStarSection = document.getElementById("comment-star-section");
            const commentContainer = document.getElementById("comment-section");
            const totalCommentsElementAll = document.getElementById("totalCommentsAll");
            const totalCommentsElementStars = document.getElementById("totalCommentsStars");
            const commentOptionSection = document.getElementById("comment-option-section");
            const totalCommentsElementSelection = document.getElementById("totalCommentsSelection");

            function displayComments(filteredComments, container, totalCommentsElement) {
                container.innerHTML = "";
                totalCommentsElement.textContent = `(${filteredComments.length})`;

                filteredComments.forEach(comment => {
                    const commentBox = document.createElement("div");
                    commentBox.classList.add("aFeedback-comment-box");

                    const optionsText = Object.entries(comment.options)
                        .map(([option, value]) => `${option}: ${value}`)
                        .join("<br>");

                    commentBox.innerHTML = `
                        <img src="${comment.profilePic}" alt="Profile Picture" class="aFeedback-profile-pic">
                        <div class="aFeedback-comment-content">
                            <span class="aFeedback-username">${comment.username}</span>
                            <div class="aFeedback-rating">
                                ${'<i class="fas fa-star"></i>'.repeat(comment.rating)}
                            </div>
                            <div class="aFeedback-comment-text-user">
                                ${optionsText} 
                                <div class="aFeedback-comment-bubble">${comment.comment}</div>
                            </div>
                        </div>
                    `;

                    container.appendChild(commentBox);
                });
            }

            // ฟังก์ชันกรองตามดาว
            window.filterByStars = function() {
                const selectedStars = document.getElementById("starFilter").value;

                if (selectedStars === "") {
                    displayComments(comments, commentStarSection, totalCommentsElementStars);
                } else {
                    const filteredComments = comments.filter(comment => comment.rating === parseInt(selectedStars));
                    displayComments(filteredComments, commentStarSection, totalCommentsElementStars);
                }
            }


            // ฟังก์ชันกรองตามตัวเลือก
            window.filterByOption = function() {
                const selectedOption = document.getElementById("optionFilter").value;

                commentOptionSection.innerHTML = "";

                if (selectedOption === "") {
                    displayComments(comments, commentOptionSection, totalCommentsElementSelection);
                    return;
                }

                // กรองความคิดเห็นตามตัวเลือกที่เลือก
                const filteredComments = comments.filter(comment =>
                    comment.options[selectedOption] !== undefined // ตรวจสอบว่ามีตัวเลือกนั้นใน options
                );

                displayComments(filteredComments, commentOptionSection, totalCommentsElementSelection);
            }

            // แสดงคอมเมนต์ทั้งหมดเมื่อโหลดหน้า
            displayComments(comments, commentContainer, totalCommentsElementAll);
        });


        //คำนวนดาว
        let selectedRating = 5;

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("ratingMessage").style.display = "block";
            rateActivity(selectedRating);
        });

        function rateActivity(rating) {
            let stars = document.querySelectorAll('.aFeedback-star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }

        // ฟังก์ชันคำนวณคะแนนรวมและแสดงผลที่ดาว
        function calculateTotalRating() {
            const dropdowns = document.querySelectorAll('.aFeedback-dropdown');
            let totalScore = 0;

            dropdowns.forEach(dropdown => {
                switch (dropdown.value) {
                    case 'option1':
                        totalScore += 5;
                        break;
                    case 'option2':
                        totalScore += 3;
                        break;
                    case 'option3':
                        totalScore += 1;
                        break;
                    default:
                        break;
                }
            });

            let finalRating;
            if (totalScore === 15) {
                finalRating = 5;
            } else if (totalScore >= 12) {
                finalRating = 4;
            } else if (totalScore >= 9) {
                finalRating = 3;
            } else if (totalScore >= 6) {
                finalRating = 2;
            } else {
                finalRating = 1;
            }

            rateActivity(finalRating);

        }

        document.querySelectorAll(".aFeedback-dropdown").forEach(dropdown => {
            dropdown.addEventListener("change", calculateTotalRating);
        });

        calculateTotalRating();


        //แสดงคอมเม้นท์5ดาว หากยังไม่เลือก
        window.filterComments = function() {
            const selectedStars = document.getElementById("starFilter").value;
            const totalCommentsElementStars = document.getElementById("totalCommentsStars");

            // ถ้าไม่ได้เลือกดาว จะให้แสดงเฉพาะความคิดเห็น 5 ดาว
            if (selectedStars === "5") {
                const filteredComments = comments.filter(comment => comment.rating === 5);
                displayComments(filteredComments, true);
                totalCommentsElementStars.textContent = `(${filteredComments.length})`;
            } else {
                const filteredComments = comments.filter(comment =>
                    selectedStars === "all" || comment.rating === parseInt(selectedStars)
                );
                displayComments(filteredComments, true);
                totalCommentsElementStars.textContent = `(${filteredComments.length})`;
            }
        }

        // เริ่มต้นแสดงคอมเมนต์ 5 ดาว เมื่อโหลดหน้า
        document.addEventListener("DOMContentLoaded", function() {
            filterComments(); // แสดงคอมเมนต์ระดับ 5 ดาวเป็นค่าเริ่มต้น
        });

        let eventId = <?php echo $event_id; ?>; // ตัวอย่าง event_id สามารถนำจาก PHP มาใช้งานได้

        // ฟังก์ชันสำหรับยกเลิกการส่งคำติชม
        function cancelComment() {
            document.getElementById('feedbackComment').value = ""; // ล้างข้อความคอมเม้น
            document.getElementById('dropdown1').value = "";
            document.getElementById('dropdown2').value = "";
            document.getElementById('dropdown3').value = "";
            document.getElementById("ratingMessage").style.display = "none"; // ซ่อนข้อความคำติชม
        }

        // ฟังก์ชันสำหรับคำนวณคะแนนจากการเลือก dropdown
        function getRatingScore(rating) {
            switch (rating) {
                case 'option1':
                    return 5; // มาก
                case 'option2':
                    return 3; // ปานกลาง
                case 'option3':
                    return 1; // น้อย
                default:
                    return 0;
            }
        }

        // ฟังก์ชันสำหรับส่งคำติชม
        function submitComment() {
            let comment = document.getElementById('feedbackComment').value; // ใช้ id ที่ตรงกับ textarea
            let locationRating = document.getElementById('dropdown1').value;
            let cleanlinessRating = document.getElementById('dropdown2').value;
            let shopRating = document.getElementById('dropdown3').value;

            if (!locationRating || !cleanlinessRating || !shopRating || !comment) {
                alert("กรุณากรอกข้อมูลให้ครบถ้วน!");
                return;
            }

            // แปลงค่าที่เลือกเป็นคำที่เข้าใจง่าย
            let ratingMap = {
                'option1': 'มาก',
                'option2': 'ปานกลาง',
                'option3': 'น้อย'
            };

            let feedbackOptions = {
                'สถานที่เหมาะสม': ratingMap[locationRating],
                'ความสะอาด': ratingMap[cleanlinessRating],
                'ร้านค้า': ratingMap[shopRating]
            };

            // คำนวณคะแนนจากการเลือกใน dropdown
            let totalScore = getRatingScore(locationRating) + getRatingScore(cleanlinessRating) + getRatingScore(shopRating);

            // สร้างฟอร์มเพื่อส่งข้อมูล
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'submit_feedback.php'; // กำหนด URL สำหรับรับข้อมูล

            // สร้างฟิลด์สำหรับข้อมูล
            let eventIdField = document.createElement('input');
            eventIdField.type = 'hidden';
            eventIdField.name = 'event_id';
            eventIdField.value = eventId;
            form.appendChild(eventIdField);

            let commentField = document.createElement('textarea');
            commentField.name = 'feedback_comment';
            commentField.value = comment;
            form.appendChild(commentField);

            let optionsField = document.createElement('input');
            optionsField.type = 'hidden';
            optionsField.name = 'feedback_option';
            optionsField.value = JSON.stringify(feedbackOptions);
            form.appendChild(optionsField);

            let scoreField = document.createElement('input');
            scoreField.type = 'hidden';
            scoreField.name = 'total_score';
            scoreField.value = totalScore;
            form.appendChild(scoreField);

            // ส่งฟอร์ม
            document.body.appendChild(form);
            form.submit(); // ส่งฟอร์ม
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