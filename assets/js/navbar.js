
document.addEventListener("DOMContentLoaded", function () {
    const searchBar = document.getElementById("searchInput");
    const menuItems = document.querySelector(".navbar-menu-items");

    function toggleSearchBar() {
        console.log("Window width:", window.innerWidth); // Debug ตรวจสอบขนาดจอ

        if (window.innerWidth >= 1099) {
            searchBar.style.display = "block"; // แสดงตลอดเวลา

        } else {
            searchBar.style.display = "none";
            menuItems.style.marginRight = "20px"; // ลดระยะเมื่อหน้าจอเล็ก
        }
    }

    // ตรวจจับเมื่อเลื่อนหน้าจอและเปลี่ยนขนาดหน้าต่าง
    window.addEventListener("scroll", toggleSearchBar);
    window.addEventListener("resize", toggleSearchBar);

    // รีเซ็ตค่าทันทีเมื่อโหลดหน้าใหม่
    toggleSearchBar();
});


const searchBar = document.querySelector(".navbar-search-bar");
const menuItems = document.querySelector(".navbar-menu-items");

function toggleSearchBar() {
    console.log("Window width:", window.innerWidth); // Debug ตรวจสอบขนาดจอ

    if (window.innerWidth >= 1099) {
        if (window.scrollY > 150) {
            searchBar.style.display = "flex";
            menuItems.classList.add("shifted"); // ดัน menu-items ไปขวา
        } else {
            searchBar.style.display = "none";
            menuItems.classList.remove("shifted"); // กลับตำแหน่งเดิม
        }
    } else {
        searchBar.style.display = "none";
        menuItems.classList.remove("shifted");
    }
}

// ตรวจจับเมื่อเลื่อนหน้าจอและเปลี่ยนขนาดหน้าต่าง
window.addEventListener("scroll", toggleSearchBar);
window.addEventListener("resize", toggleSearchBar);

// รีเซ็ตค่าทันทีเมื่อโหลดหน้าใหม่
toggleSearchBar();


document.addEventListener("DOMContentLoaded", function () {
    const userButton = document.getElementById("userButton");
    const userMenu = document.getElementById("userMenu");
    const notificationButton = document.getElementById("notificationButton");
    const notificationPanel = document.getElementById("notificationPanel");

    let activeMenu = null; // ตัวแปรเก็บเมนูที่เปิดอยู่

    function closeAllMenus() {
        userMenu.classList.remove("active");
        notificationPanel.classList.remove("active");
        activeMenu = null; // รีเซ็ตเมนูที่เปิดอยู่
    }

    function toggleMenu(button, menu) {
        if (activeMenu === menu) {
            // ถ้ากดปุ่มเดิม -> ปิดเมนู
            closeAllMenus();
        } else {
            // ถ้ากดปุ่มใหม่ -> ปิดเมนูเก่า แล้วเปิดเมนูใหม่
            closeAllMenus();
            menu.classList.add("active");
            activeMenu = menu;
        }
    }

    userButton.addEventListener("click", function (event) {
        event.stopPropagation();
        toggleMenu(userButton, userMenu);
    });

    notificationButton.addEventListener("click", function (event) {
        event.stopPropagation();
        toggleMenu(notificationButton, notificationPanel);
    });

    // เมื่อคลิกที่อื่นให้ปิดทุกเมนู
    document.addEventListener("click", function () {
        closeAllMenus();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const hamburgerMenu = document.getElementById("hamburgerMenu");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const closeSidebar = document.getElementById("closeSidebar");

    // เปิด Sidebar
    hamburgerMenu.addEventListener("click", function () {
        sidebarOverlay.classList.add("active");
    });

    // ปิด Sidebar
    closeSidebar.addEventListener("click", function () {
        sidebarOverlay.classList.remove("active");
    });

    // ปิด Sidebar เมื่อคลิกที่อื่น
    document.addEventListener("click", function (event) {
        if (!sidebarOverlay.contains(event.target) && !hamburgerMenu.contains(event.target)) {
            sidebarOverlay.classList.remove("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".hamburger-dropdown");

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector(".hamburger-dropdown-toggle");
        const menu = dropdown.querySelector(".hamburger-dropdown-menu");

        toggle.addEventListener("click", function (event) {
            event.preventDefault();

            // ปิด dropdown อื่นๆ ก่อนเปิดอันใหม่
            dropdowns.forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove("active");
                }
            });

            // เปิด/ปิด dropdown ที่ถูกกด
            dropdown.classList.toggle("active");
        });

        // ป้องกัน dropdown ปิดเองเมื่อคลิกข้างใน
        menu.addEventListener("click", function (event) {
            event.stopPropagation();
        });
    });

    // ปิด dropdown ถ้าคลิกข้างนอก
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".hamburger-dropdown")) {
            dropdowns.forEach((item) => {
                item.classList.remove("active");
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".navbar-nav-item.dropdown");
    const notificationButton = document.getElementById("notificationButton");
    const userButton = document.getElementById("userButton");
    const userMenu = document.getElementById("userMenu");
    const notificationPanel = document.getElementById("notificationPanel");

    let activeMenu = null; // ใช้เก็บเมนูที่เปิดอยู่
    let activeDropdown = null; // ใช้เก็บ dropdown ที่เปิดอยู่

    function closeAllDropdowns() {
        dropdowns.forEach((dropdown) => {
            dropdown.classList.remove("active");
            dropdown.querySelector(".dropdown-menu").style.display = "none";
        });
        activeDropdown = null; // รีเซ็ต dropdown ที่เปิดอยู่
    }

    function closeAllMenus() {
        if (activeMenu) {
            activeMenu.classList.remove("active");
            activeMenu = null;
        }
    }

    function toggleDropdown(dropdown) {
        if (activeDropdown === dropdown) {
            closeAllDropdowns(); // ถ้าคลิกที่เดิม ให้ปิด
        } else {
            closeAllMenus(); // ปิด notificationPanel และ userMenu ก่อน
            closeAllDropdowns(); // ปิด dropdown อื่นๆ ก่อนเปิดอันใหม่
            dropdown.classList.add("active");
            dropdown.querySelector(".dropdown-menu").style.display = "block";
            activeDropdown = dropdown;
        }
    }

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector(".dropdown-toggle");

        toggle.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation(); // ป้องกัน event bubbling
            toggleDropdown(dropdown);
        });
    });

    function toggleMenu(button, menu) {
        if (activeMenu === menu) {
            closeAllMenus(); // ถ้าคลิกที่เดิม ให้ปิดเมนู
        } else {
            closeAllDropdowns(); // ปิด dropdown เสมอเมื่อเปิดเมนูอื่น
            closeAllMenus(); // ปิดเมนูอื่นก่อนเปิดเมนูใหม่
            menu.classList.add("active");
            activeMenu = menu;
        }
    }

    notificationButton.addEventListener("click", function (event) {
        event.stopPropagation();
        toggleMenu(notificationButton, notificationPanel);
    });

    userButton.addEventListener("click", function (event) {
        event.stopPropagation();
        toggleMenu(userButton, userMenu);
    });

    // ปิด dropdown และเมนูทั้งหมดเมื่อคลิกที่อื่น
    document.addEventListener("click", function () {
        closeAllDropdowns();
        closeAllMenus();
    });
});





document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const searchPopup = document.getElementById("searchPopup");
    const popupOverlay = document.getElementById("popupOverlay");
    const closePopup = document.getElementById("closePopup");

    // เมื่อคลิกที่แถบค้นหา ให้แสดง popup
    searchInput.addEventListener("click", function () {
        searchPopup.style.display = "block";
        popupOverlay.style.display = "block";
    });

    // เมื่อคลิกที่ overlay ให้ปิด popup
    popupOverlay.addEventListener("click", function () {
        searchPopup.style.display = "none";
        popupOverlay.style.display = "none";
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const searchPopup = document.getElementById("searchPopup");
    const popupNavbar = document.getElementById("popupNavbar");
    const closePopupNavbar = document.getElementById("closePopupNavbar");
    const searchInput = document.getElementById("searchInput");

    searchInput.addEventListener("click", function () {
        popupNavbar.classList.add("active");
        searchPopup.classList.add("active");
    });

    closePopupNavbar.addEventListener("click", function () {
        popupNavbar.classList.remove("active");
        searchPopup.classList.remove("active");
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const searchPopup = document.getElementById("searchPopup");
    const popupOverlay = document.getElementById("popupOverlay");
    const popupNavbar = document.getElementById("popupNavbar");
    const navCenter = document.querySelector(".nav-center"); // เลือก nav-center

    // เมื่อคลิกที่ overlay ให้ปิด popup และซ่อน nav-center
    popupOverlay.addEventListener("click", function () {
        searchPopup.style.display = "none";
        popupOverlay.style.display = "none";
        popupNavbar.classList.remove("active");
        navCenter.classList.remove("active"); // ซ่อน nav-center
    });

    closePopupNavbar.addEventListener("click", function () {
        searchPopup.style.display = "none";
        popupOverlay.style.display = "none";
        popupNavbar.classList.remove("active");
        navCenter.classList.remove("active"); // ซ่อน nav-center
    });
});
// เพิ่มลบกิจกรรมที่สนใจ
document.addEventListener("DOMContentLoaded", function () {
    function checkEmptyEvents() {
        const containerExtra = document.querySelector(".setting-container-extra");
        const eventContainers = containerExtra.querySelectorAll(".setting-container-event");

        if (eventContainers.length === 0) {
            const emptyState = document.createElement("div");
            emptyState.classList.add("empty-events");
            emptyState.innerHTML = `
                <div class="empty-icon">
                    <i class="bi bi-star-fill" style="font-size: 50px; color: #fff;"></i>
                </div>
                <h4>ไม่มีการติดตาม</h4>
                <p>กดติดตามเพื่อให้ไม่พลาดทุกการอัพเดท</p>
                <button class="btn btn-primary">ดูอีเว้นท์อื่นๆ</button>
            `;

            containerExtra.appendChild(emptyState);
        }
    }

    document.querySelectorAll(".star-icon").forEach(function (icon) {
        icon.addEventListener("click", function () {
            let eventContainer = this.closest(".setting-container-event");
            if (eventContainer) {
                eventContainer.remove();
                checkEmptyEvents(); 
            }
        });
    });

    checkEmptyEvents(); 
});

// หน้าแก้ไขข้อมูลส่วนตัว
editButton.addEventListener("click", function (e) {
    e.preventDefault();
    personalInfo.style.display = "none";  // ซ่อนข้อมูลเดิม
    editForm.style.display = "block";  // แสดงฟอร์มแก้ไข
});
cancelButton.addEventListener("click", function () {
    editForm.style.display = "none";  // ซ่อนฟอร์มแก้ไข
    personalInfo.style.display = "block";  // แสดงข้อมูลเดิม
});
saveButton.addEventListener("click", function () {
    const firstName = document.getElementById("firstName").value;
    const lastName = document.getElementById("lastName").value;
    const studentId = document.getElementById("studentId").value;
    const birthDateValue = document.getElementById("birthDate").value;
    const gender = document.getElementById("gender").value;
    const phoneNumber = document.getElementById("phoneNumber").value;

    // แปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น DD/MM/YYYY
    let formattedBirthDate = "";
    if (birthDateValue) {
        const dateParts = birthDateValue.split("-");
        formattedBirthDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
    }

    // อัปเดตข้อมูลที่แสดง
    personalInfo.innerHTML = `
        <div class="info-header">
            <h5>ข้อมูลส่วนตัว</h5>
            <a href="#" class="edit-button-personal-info" id="editButton"><i class="bi bi-pencil-square"></i> แก้ไข</a>
        </div>
        <p>ชื่อ-นามสกุล: ${firstName} ${lastName}</p>
        <p>รหัสนิสิต: ${studentId}</p>
        <p>วันเกิด: ${formattedBirthDate}</p>
        <p>เพศ: ${gender}</p>
        <p>เบอร์โทรศัพท์มือถือ: ${phoneNumber}</p>
    `;

    // ซ่อนฟอร์มและแสดงข้อมูลที่อัปเดต
    editForm.style.display = "none";
    personalInfo.style.display = "block";

    // ต้องเพิ่ม event listener ใหม่ให้ปุ่มแก้ไขที่ถูกสร้างขึ้นใหม่
    document.getElementById("editButton").addEventListener("click", function (e) {
        e.preventDefault();
        personalInfo.style.display = "none";
        editForm.style.display = "block";
    });
});
saveButton.addEventListener("click", function () {
    const firstName = document.getElementById("firstName").value;
    const lastName = document.getElementById("lastName").value;
    const studentId = document.getElementById("studentId").value;
    const birthDateValue = document.getElementById("birthDate").value;
    const gender = document.getElementById("gender").value;
    const phoneNumber = document.getElementById("phoneNumber").value;

    // แปลงรูปแบบวันที่จาก YYYY-MM-DD เป็น DD/MM/YYYY
    let formattedBirthDate = "";
    if (birthDateValue) {
        const dateParts = birthDateValue.split("-");
        formattedBirthDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
    }

    // อัปเดตข้อมูลที่แสดง
    personalInfo.innerHTML = `
        <div class="info-header">
            <h5>ข้อมูลส่วนตัว</h5>
            <a href="#" class="edit-button-personal-info" id="editButton"><i class="bi bi-pencil-square"></i> แก้ไข</a>
        </div>
        <p>ชื่อ-นามสกุล: ${firstName} ${lastName}</p>
        <p>รหัสนิสิต: ${studentId}</p>
        <p>วันเกิด: ${formattedBirthDate}</p>
        <p>เพศ: ${gender}</p>
        <p>เบอร์โทรศัพท์มือถือ: ${phoneNumber}</p>
    `;

    // ซ่อนฟอร์มและแสดงข้อมูลที่อัปเดต
    editForm.style.display = "none";
    personalInfo.style.display = "block";

    // ต้องเพิ่ม event listener ใหม่ให้ปุ่มแก้ไขที่ถูกสร้างขึ้นใหม่
    document.getElementById("editButton").addEventListener("click", function (e) {
        e.preventDefault();
        personalInfo.style.display = "none";
        editForm.style.display = "block";
    });
});
