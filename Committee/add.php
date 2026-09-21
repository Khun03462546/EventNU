<?php
require "../db.php";

$message = ""; // สำหรับแสดงข้อความแจ้งเตือน

// ดึงข้อมูล Faculty
$faculty_sql = "SELECT FacultyID, Faculty_Name FROM Faculty";
$faculty_result = $conn->query($faculty_sql);

// ดึงข้อมูล Gender
$gender_sql = "SELECT GenderID, GenderType FROM Gender";
$gender_result = $conn->query($gender_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $committee_email = $_POST["committee_email"];
    $committee_firstname = $_POST["committee_firstname"];
    $committee_lastname = $_POST["committee_lastname"];
    $committee_password = $_POST["committee_password"];
    $committee_phone = $_POST["committee_phone"];
    $committee_id = $_POST["committee_id"];
    $faculty_id = $_POST["faculty_id"];
    $major_id = $_POST["major_id"];
    $gender_id = $_POST["gender_id"];

    // ตรวจสอบว่าอีเมลนี้มีในระบบหรือยัง
    $check_email = $conn->prepare("SELECT committee_email FROM committee WHERE committee_email = ?");
    $check_email->bind_param("s", $committee_email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $message = "❌ อีเมลนี้ถูกใช้งานแล้ว!";
    } else {
        // เข้ารหัสรหัสผ่านด้วย bcrypt
        $hashed_password = password_hash($committee_password, PASSWORD_BCRYPT);

        // คำสั่ง SQL สำหรับเพิ่มข้อมูล
        $sql = "INSERT INTO committee (committee_email, committee_firstname, committee_lastname, committee_password, committee_phone, committee_id, FacultyID, MajorID, GenderID) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiisi", $committee_email, $committee_firstname, $committee_lastname, $hashed_password, $committee_phone, $committee_id, $faculty_id, $major_id, $gender_id);

        if ($stmt->execute()) {
            $message = "✅ สมัครสมาชิกสำเร็จ!";
        } else {
            $message = "❌ เกิดข้อผิดพลาด: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_email->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิกคณะกรรมการ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
        }

        form {
            width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: left;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        button {
            margin-top: 15px;
            padding: 10px;
            background-color: blue;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: darkblue;
        }

        .message {
            margin-top: 10px;
            font-weight: bold;
            color: red;
        }
    </style>
</head>

<body>

    <h2>สมัครสมาชิกคณะกรรมการ</h2>

    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form action="" method="post">
        <label>อีเมล:</label>
        <input type="email" name="committee_email" required>

        <label>ชื่อ:</label>
        <input type="text" name="committee_firstname" required>

        <label>นามสกุล:</label>
        <input type="text" name="committee_lastname" required>

        <label>รหัสผ่าน:</label>
        <input type="password" name="committee_password" required>

        <label>เบอร์โทรศัพท์:</label>
        <input type="text" name="committee_phone" required>

        <label>หมายเลขคณะกรรมการ (ID):</label>
        <input type="number" name="committee_id" required>

        <select name="faculty_id" id="faculty" class="signup-input" required>
            <option value="">Select Faculty</option>
            <?php while ($row = $faculty_result->fetch_assoc()) { ?>
                <option value="<?php echo $row['FacultyID']; ?>"><?php echo $row['Faculty_Name']; ?></option>
            <?php } ?>
        </select>
        </select>
        <select name="major_id" id="major" class="signup-input" required>
            <option value="">Select Major</option>
        </select>

        <script>
            document.getElementById("faculty").addEventListener("change", function() {
                let facultyID = this.value;
                let majorSelect = document.getElementById("major");

                // ล้างข้อมูลเดิม
                majorSelect.innerHTML = '<option value="">Select Major</option>';

                if (facultyID) {
                    fetch("../get_majors.php?faculty_id=" + facultyID)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(major => {
                                let option = document.createElement("option");
                                option.value = major.MajorID;
                                option.textContent = major.Major_Name;
                                majorSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error("Error:", error));
                }
            });
        </script>

        <select name="gender_id" class="signup-input" required>
            <option value="">Select Gender</option>
            <?php while ($row = $gender_result->fetch_assoc()) { ?>
                <option value="<?php echo $row['GenderID']; ?>"><?php echo $row['GenderType']; ?></option>
            <?php } ?>
        </select>

        <button type="submit">สมัครสมาชิก</button>
    </form>

</body>

</html>