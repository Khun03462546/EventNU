<div align="center">

# 🎉 EventNU

**ระบบจัดการและติดตามอีเว้นท์ของมหาวิทยาลัยนเรศวร**
(Naresuan University Event Management System)

![PHP](https://img.shields.io/badge/Backend-PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/UI-Bootstrap%205-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![jQuery](https://img.shields.io/badge/JS-jQuery-0769AD?style=flat-square&logo=jquery&logoColor=white)
![Roles](https://img.shields.io/badge/Roles-5-EB8826?style=flat-square)

เว็บแอปพลิเคชันแบบหลายบทบาท (multi-role) สำหรับค้นหา ลงทะเบียน และจองบัตรเข้าร่วมกิจกรรม
พร้อมระบบจัดการ · อนุมัติ · ติดตามผลอีเว้นท์ตลอดวงจร ตั้งแต่ยื่นคำขอจัดงานจนถึงสรุปผลหลังงานจบ

</div>

---

## 📑 สารบัญ

- [👥 บทบาทผู้ใช้งาน](#-บทบาทผู้ใช้งาน-roles)
- [🧩 เทคโนโลยีที่ใช้](#-เทคโนโลยีที่ใช้-tech-stack)
- [🗃️ ฐานข้อมูล](#️-ฐานข้อมูล-eventnusql)
- [📁 โครงสร้างโฟลเดอร์](#-โครงสร้างโฟลเดอร์)
- [🚀 วิธีติดตั้งและรัน](#-วิธีติดตั้งและรันโปรเจกต์-local-xampp)
- [🖼️ ดูหน้ามอกอัป](#️-ดูหน้ามอกอัป-prototype)
- [📝 หมายเหตุ](#-หมายเหตุ)

---

## 👥 บทบาทผู้ใช้งาน (Roles)

| บทบาท | โฟลเดอร์ | หน้าที่หลัก |
|:---:|---|---|
| 🙋 **Audience**<br><sub>ผู้เข้าร่วมงาน</sub> | `Audience/` | ค้นหา/ดูรายละเอียดอีเว้นท์ · ลงทะเบียน-จองบัตร (ฟรี/เสียเงิน) · ชำระเงิน · ติดตามสถานะคำสั่งซื้อ · ให้ feedback |
| 🎪 **Event Organizer**<br><sub>ผู้จัดอีเว้นท์</sub> | `EventOrganizer/` | ยื่นคำขอจัดอีเว้นท์ · ดูอีเว้นท์ของตนเอง · บันทึกรายจ่าย · ดูสรุปผล |
| 🧑‍⚖️ **Committee**<br><sub>คณะกรรมการ</sub> | `Committee/` | ตรวจสอบข้อมูลคำขอจัดงาน · ประเมินผล · ดูงบประมาณ |
| 🏫 **Student Affairs**<br><sub>ฝ่ายกิจการนิสิต</sub> | `StudentAffairs/` | จัดการอีเว้นท์ทั้งหมด · คำสั่งซื้อ · เพิ่มอีเว้นท์ · เปลี่ยนบทบาทผู้ใช้ (role) |
| 🎓 **President**<br><sub>ผู้บริหาร</sub> | `President/` | อนุมัติ/ปฏิเสธคำขอจัดอีเว้นท์ · ติดตามคำสั่งซื้อและความคืบหน้าอีเว้นท์ทั้งหมด |

> 💡 การกำหนดบทบาทและสิทธิ์การเข้าถึงหน้าต่าง ๆ ทำผ่านตาราง `Audience_Role` ในฐานข้อมูล และ session หลัง login
> (ดูรายละเอียดใน `login.php`)

---

## 🧩 เทคโนโลยีที่ใช้ (Tech Stack)

| ส่วน | รายละเอียด |
|---|---|
| **Backend** | PHP (`mysqli`, prepared statements) — หน้าใช้งานจริงทั้งหมดเป็นไฟล์ `.php` ในแต่ละโฟลเดอร์บทบาท |
| **Database** | MySQL / MariaDB — schema + ข้อมูลตัวอย่างอยู่ใน `eventnu.sql` |
| **Frontend** | Bootstrap 5 · jQuery · Vanilla JS · Font Awesome / Bootstrap Icons / Themify Icons · Google Fonts (Itim) |
| **Auth** | Session-based login (`password_verify` / bcrypt) ตรวจสอบแยกตามตารางบทบาท (audience, committee, studentaffairs, president ฯลฯ) |

> ไฟล์ `.html` ในแต่ละโฟลเดอร์คือ **หน้ามอกอัป/ต้นแบบ UI แบบสแตติก** (คู่ขนานกับไฟล์ `.php` ชื่อเดียวกัน)
> ใช้ดูดีไซน์/หน้าตาได้โดยไม่ต้องต่อฐานข้อมูล ส่วนไฟล์ `.php` คือหน้าที่ใช้งานจริงและดึงข้อมูลจากฐานข้อมูล

---

## 🗃️ ฐานข้อมูล (`eventnu.sql`)

<details>
<summary>📋 ดูรายชื่อตารางทั้งหมด (20 ตาราง)</summary>

<br>

`audience` · `booking` · `categories` · `categoriestwo` · `committee` · `event` · `eventorganizer` ·
`event_approvals` · `faculty` · `favorite` · `feedback` · `gender` · `major` · `notification` ·
`president` · `register` · `request_event` · `studentaffairs` · `studentsdata` · `vote`

</details>

---

## 📁 โครงสร้างโฟลเดอร์

<details open>
<summary>คลิกเพื่อย่อ/ขยาย</summary>

```
EventNU/
├── 🙋 Audience/             หน้าเว็บฝั่งผู้เข้าร่วมงาน (.html มอกอัป + .php ใช้งานจริง)
├── 🧑‍⚖️ Committee/            หน้าเว็บฝั่งคณะกรรมการ
├── 🎪 EventOrganizer/       หน้าเว็บฝั่งผู้จัดอีเว้นท์
├── 🎓 President/            หน้าเว็บฝั่งผู้บริหาร
├── 🏫 StudentAffairs/       หน้าเว็บฝั่งฝ่ายกิจการนิสิต (มี subfolder HTML/ เก็บมอกอัปเพิ่มเติม)
├── 🎨 assets/               CSS, JS, ไลบรารีภายนอก (vendors), รูปภาพ, ไฟล์เอกสารตัวอย่าง
├── 🖼️ EventPicture/         รูปภาพอีเว้นท์ที่อัปโหลดจากระบบ
├── 🧾 receipt/              สลิป/หลักฐานการชำระเงินที่อัปโหลดจากระบบ
├── 🔌 db.php                การเชื่อมต่อฐานข้อมูล (mysqli)
├── 🏠 index.php             หน้าแรกของเว็บไซต์ (ก่อน login)
├── 🔐 login.php / signup.php / logout.php     ระบบสมาชิก
├── 📝 eventform.php / get_majors.php / g.php  ฟอร์ม/endpoint เสริม
├── 💾 eventnu.sql           ไฟล์ schema + ข้อมูลตัวอย่างของฐานข้อมูล
├── 🖇️ mockup.html           หน้ารวมลิงก์ (Prototype Hub) ไปยังหน้า .html มอกอัปทุกบทบาท
├── ℹ️ about.html / contact.html / terms.html / privacy.html   หน้าข้อมูลทั่วไป (ลิงก์จาก footer)
└── 📄 README.md
```

</details>

---

## 🚀 วิธีติดตั้งและรันโปรเจกต์ (Local, XAMPP)

1. ติดตั้ง [XAMPP](https://www.apachefriends.org/) (หรือ stack ที่มี Apache + PHP + MySQL)
2. วางโฟลเดอร์โปรเจกต์นี้ไว้ใน `htdocs/EventNU`
3. เปิด phpMyAdmin → สร้างฐานข้อมูลชื่อ `EventNU` → import ไฟล์ `eventnu.sql`
4. ตรวจสอบค่าเชื่อมต่อฐานข้อมูลใน `db.php`
   ```php
   host: localhost | user: root | password: (ว่าง) | database: EventNU
   ```
5. เปิด **Apache** และ **MySQL** ใน XAMPP Control Panel
6. เข้าใช้งานผ่าน 👉 `http://localhost/EventNU/index.php`

---

## 🖼️ ดูหน้ามอกอัป (Prototype)

ไม่ต้องตั้งเซิร์ฟเวอร์หรือฐานข้อมูลก็ดูได้ทันที — เปิดไฟล์ **`mockup.html`** ด้วยเบราว์เซอร์โดยตรง
เพื่อไล่ดูหน้า `.html` มอกอัปของทุกบทบาทผ่านหน้ารวมลิงก์เดียว (Prototype Hub)

---

## 📝 หมายเหตุ

- 🚫 **ห้ามแก้ไขไฟล์ `.php`** โดยไม่จำเป็น (ตามข้อตกลงของโปรเจกต์) — งานปรับปรุงที่ผ่านมาเน้นแก้เฉพาะฝั่งไฟล์ `.html`
  (แก้ลิงก์/พาธที่เสีย, เพิ่มหน้าที่ขาดหาย เช่น `about.html`, `openlayout.html`) โดยไม่แตะ logic ฝั่ง PHP
- 🖼️ ไฟล์รูปภาพ/เอกสารตัวอย่างบางส่วนใน `assets/imgs`, `assets/doc` เป็นข้อมูลตัวอย่างสำหรับการดีไซน์ ไม่ใช่ข้อมูลจริง

<div align="center">
<sub>Made with 🧡 for Naresuan University</sub>
</div>
