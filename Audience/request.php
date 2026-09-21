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
    $faculty = $row['FacultyID'];  // หรือเพิ่มข้อมูลอื่นๆ ตามต้องการ
    $role = $row['Audience_Role'];
    $image = $row['Audience_Image'];
    $phone = $row['Audience_Phone'];
} else {
    echo "User not found!";
    exit(); // หยุดการทำงานหากไม่พบผู้ใช้
}

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
    <link rel="stylesheet" href="assets/vendors/themify-icons/css/themify-icons.css">
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

<body class="request-body">
    <div class="request-container">
        <div class="request-header">แบบฟอร์มขอจัดEvent</div>

        <div class="request-profile-section">
            <img src="<?php echo $image; ?>" alt="Profile Picture" class="request-profile-picture">
            <div class="request-profile-info">
                <div class="request-username"><?php echo $first_name; ?></div>
                <div class="request-role"><?php echo $role; ?></div>
            </div>
        </div>

        <!-- เพิ่มส่วน "ยื่น, ถึงกิจกรรม, อธิการ, อนุมัติ" -->
        <div class="request-action-section">
            <div class="request-step request-completed" id="step1" onclick="showSection(1)">
                <div class="request-circle2 "><i class="request-bi bi-file-earmark-text"></i></div>
                <span>section 1</span>
            </div>
            <div class="request-line request-pending"></div>
            <div class="request-step" id="step2" onclick="showSection(2)">
                <div class="request-circle2 request-pending"><i class="request-bi bi-file-earmark-text"></i></div>
                <span>section 2</span>
            </div>
            <div class="request-line request-pending"></div>
            <div class="request-step" id="step3" onclick="showSection(3)">
                <div class="request-circle2 request-pending"><i class="request-bi bi-file-earmark-text"></i></div>
                <span>section 3</span>
            </div>
        </div>
        <hr>

        <form id="eventForm">
            <!-- Section 1 -->
            <div class="request-section" id="section1">
                <div class="request-section-title">
                    <span class="request-circle">1</span> เอกสารรายละเอียดการขอจัด Event
                </div>
                <div class="request-form-group2">
                    <label for="eventFile">เอกสารการยื่นขอจัดอีเวนท์ :</label>
                    <span class="request-document-info"
                        onclick="downloadFile('../assets/doc/requestevent.docx', 'eventrequest.docx')">
                        <i class="request-bi bi-file-earmark-text download-icon"></i> <span
                            class="request-download-text">ดาวน์โหลด</span>
                    </span>
                </div>
                <div class="request-form-group2">
                    <label for="budgetFile">เอกสารงบประมาณการจัดอีเวนท์ :</label>
                    <span class="request-document-info"
                        onclick="downloadFile('../assets/doc/price.pdf', 'budgetreport.pdf')">
                        <i class="request-bi bi-file-earmark-text download-icon"></i> <span
                            class="request-download-text">ดาวน์โหลด</span>
                    </span>
                </div>
                <button type="button" onclick="nextSection(2)">Next</button>
            </div>
        </form>

        <!-- Section 2 -->
        <form action="complete_event.php" method="post" enctype="multipart/form-data">
            <div class="request-section" id="section2">
                <div class="request-section-title"><span class="request-circle">2</span> ข้อมูลพื้นฐาน</div>

                <div class="request-form-group">
                    <label for="eventName">ชื่ออีเวนท์ :</label>
                    <input type="text" id="eventName" name="eventName" required>
                </div>

                <div class="request-form-group">
                    <label for="eventImage">รูปภาพอีเวนท์ :</label>
                    <input type="file" id="eventImage" name="eventImage" accept="image/*" required>
                </div>

                <div class="request-form-group">
                    <label for="datesale">วันขายบัตร :</label>
                    <input type="date" id="datesale" name="datesale">
                </div>

                <div class="request-form-group">
                    <label for="timesale">เวลาขายบัตร :</label>
                    <input type="time" id="timesale" name="timesale">
                </div>

                <div class="request-form-group">
                    <label for="dateevent">วันเริ่มอีเว้นท์ :</label>
                    <input type="date" id="dateevent" name="dateevent" required>
                </div>

                <div class="request-form-group">
                    <label for="timeevent">เวลาเริ่มอีเว้นท์ :</label>
                    <input type="time" id="timeevent" name="timeevent" required>
                </div>

                <div class="request-form-group">
                    <label for="prictticketevent">ราคาบัตร :</label>
                    <input type="text" id="prictticketevent" name="prictticketevent" placeholder="อีเว้นท์ฟรีกรุณากรอก 0 " required>
                </div>

                <div class="request-form-group">
                    <label for="locationevent">สถานที่จัด :</label>
                    <input type="text" id="locationevent" name="locationevent" placeholder="คณะวิทยาศาสตร์" required>
                </div>

                <div class="request-form-group">
                    <label for="mapevent">แผนที่ :</label>
                    <input type="text" id="mapevent" name="mapevent" placeholder="กรุณากรอก URL แผนที่" required>
                </div>

                <div class="request-form-group">
                    <label for="category">หมวดหมู่ของอีเว้นท์ :</label>
                    <select id="category" name="category" required>
                        <option value="">- กรุณาเลือก -</option>
                        <?php
                        include "../db.php";
                        $sql = "SELECT * FROM categories";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['Category_ID'] . "'>" . $row['Category_Name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>


                <div class="request-form-group">
                    <label for="eventDetails">รายละเอียด :</label>
                    <textarea id="eventDetails" name="eventDetails" required></textarea>
                </div>

                <div class="request-form-group">
                    <label for="registerType">ลงทะเบียน :</label>
                    <select id="registerType" name="registerType" required>
                        <option value="">- กรุณาเลือก -</option>
                        <option value="yes">ลงทะเบียน</option>
                        <option value="no">ไม่ลงทะเบียน</option>
                    </select>
                </div>

                <input type="hidden" name="email" value="<?php echo $email; ?>">

                <button type="button" onclick="nextSection(3)">Next</button>
                <button type="button" onclick="prevSection(1)">Back</button>
            </div>


            <!-- Section 3 -->
            <div class="request-section" id="section3">
                <div class="request-section-title"><span class="request-circle">3</span> ข้อมูลผู้รับผิดชอบ</div>
                <div class="request-form-group">
                    <label for="organizer">ชื่อผู้จัด : </label>
                    <input type="text" id="organizer" disabled value=" <?php echo $first_name . " " . $last_name; ?> ">
                </div>

                <div class="request-form-group">
                    <label for="Info">ข้อมูลผู้ติดต่อ :</label>
                </div>

                <div class="request-form-group2">
                    <label for="email">อีเมล์ : </label>
                    <input type="email" id="email" disabled value="<?php echo $email; ?>">
                </div>

                <div class="request-form-group2">
                    <label for="phone">เบอร์โทร : </label>
                    <input type="tel" id="phone" disabled value="<?php echo $phone; ?> ">
                </div>

                <div class="request-form-group">
                    <label for="budgetTotal">งบประมาณทั้งหมด:</label>
                    <input type="number" id="budgetTotal" name="budgetTotal" required> บาท
                </div>

                <button type="submit">Submit</button>
                <button type="button" onclick="prevSection(2)">Back</button>
            </div>
        </form>



        <script>
            //ขอจัดอีเว้นท์หน้าที่4
            function addExpense() {
                const expenseList = document.getElementById("expenseList");

                const newExpense = document.createElement("div");
                newExpense.classList.add("request-expense-item");

                const expenseName = document.createElement("input");
                expenseName.type = "text";
                expenseName.name = "expenseName[]";
                expenseName.placeholder = "รายการ";
                expenseName.required = true;

                const expensePrice = document.createElement("input");
                expensePrice.type = "number";
                expensePrice.name = "expensePrice[]";
                expensePrice.placeholder = "ราคา (บาท)";
                expensePrice.required = true;

                const removeButton = document.createElement("button");
                removeButton.type = "button";
                removeButton.classList.add("request-remove-expense");
                removeButton.textContent = "✖";
                removeButton.onclick = function() {
                    removeExpense(this);
                };

                newExpense.appendChild(expenseName);
                newExpense.appendChild(expensePrice);
                newExpense.appendChild(removeButton);

                expenseList.appendChild(newExpense);
            }

            function removeExpense(button) {
                button.parentElement.remove();
            }


            //ไฟล์ขอจัดอีเว้นท์
            function downloadFile(filePath, fileName) {
                const link = document.createElement('a');
                link.href = filePath;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            let currentSection = 1;

            function showSection(sectionNumber) {
                const sections = document.querySelectorAll('.request-section');
                sections.forEach((section, index) => {
                    if (index + 1 === sectionNumber) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
            }

            window.onload = () => {
                showSection(1);
            };

            function nextSection(sectionNumber) {
                const currentStep = document.getElementById('step' + currentSection);
                const nextStep = document.getElementById('step' + sectionNumber);
                const currentLine = document.querySelectorAll('.request-line')[currentSection - 1];

                currentStep.classList.add('request-completed');
                currentStep.querySelector('.request-circle2').classList.remove('request-pending');
                currentStep.querySelector('.request-circle2').classList.add('request-completed');

                if (currentLine) {
                    currentLine.classList.add('active');
                    currentLine.classList.remove('request-pending'); // เปลี่ยนจาก pending เป็น active
                }

                nextStep.classList.add('request-active'); // ให้ไฮไลท์ step ปัจจุบัน
                nextStep.querySelector('.request-circle2').classList.remove('request-pending');

                showSection(sectionNumber);
                currentSection = sectionNumber;
            }

            function prevSection(sectionNumber) {
                const sections = document.querySelectorAll('.request-section');
                const lines = document.querySelectorAll('.request-line');

                sections.forEach((section) => {
                    section.classList.remove('active');
                });

                document.getElementById(`section${sectionNumber}`).classList.add('active');

                for (let i = sectionNumber; i < currentSection; i++) {
                    const prevLine = lines[i - 1];
                    if (prevLine) {
                        prevLine.classList.remove('active');
                        prevLine.classList.add('request-pending');
                    }

                    const prevStep = document.getElementById('step' + i);
                    if (prevStep) {
                        prevStep.classList.remove('request-completed', 'request-active');
                        prevStep.querySelector('.request-circle2').classList.remove('request-completed');
                        prevStep.querySelector('.request-circle2').classList.add('request-pending');
                    }
                }

                currentSection = sectionNumber;
            }
        </script>

</body>

</html>