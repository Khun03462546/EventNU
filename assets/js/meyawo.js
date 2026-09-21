/*!
=========================================================
* Meyawo Landing page
=========================================================

* Copyright: 2019 DevCRUD (https://devcrud.com)
* Licensed: (https://devcrud.com/licenses)
* Coded by www.devcrud.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*/

// smooth scroll
$(document).ready(function () {
    $(".navbar .nav-link").on('click', function (event) {

        if (this.hash !== "") {

            event.preventDefault();

            var hash = this.hash;

            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 700, function () {
                window.location.hash = hash;
            });
        }
    });
});

$('#nav-toggle').click(function () {
    $(this).toggleClass('is-active')
    $('ul.nav').toggleClass('show');
});





//
// 
//  
//Start home.html //
//  กดจากรูปภาพ Event หน้า home.html -> eventsoon

// เปลี่ยนรูปใน carousel หลัก
document.addEventListener("DOMContentLoaded", function () {
    const carouselImages = document.querySelectorAll('.home-carousel-image-wrapper');
    if (carouselImages.length === 0) return;

    let carouselIndex = 0;
    document.getElementById('prev-btn').addEventListener('click', () => {
        carouselIndex = (carouselIndex === 0) ? carouselImages.length - 1 : carouselIndex - 1;
        updateCarousel();
    });

    document.getElementById('next-btn').addEventListener('click', () => {
        carouselIndex = (carouselIndex === carouselImages.length - 1) ? 0 : carouselIndex + 1;
        updateCarousel();
    });

    function updateCarousel() {
        carouselImages.forEach((image) => image.style.display = 'none');
        carouselImages[carouselIndex].style.display = 'block';
    }

    updateCarousel();
});


// หมวดหมู่ของ อีเว้นท์ //
const categories = [
    { name: "Theater", icon: "fas fa-theater-masks" },
    { name: "Charity", icon: "fas fa-gift" },
    { name: "Concert", icon: "fas fa-music" },
    { name: "Market", icon: "fas fa-shopping-basket" },
    { name: "Sports", icon: "fas fa-futbol" },
    { name: "Education", icon: "fas fa-book" }
];



// ฟังก์ชันสำหรับการเลื่อนหมวดหมู่
function scrollFaculties(direction) {
    const container = document.getElementById("categoriesContainer");
    const scrollAmount = container.offsetWidth / 3;

    container.scrollBy({
        left: direction * scrollAmount,
        behavior: "smooth",
    });
}
// สร้างหมวดหมู่
function createCategoriesBlocks() {
    const container = document.getElementById("categoriesContainer");
    categories.forEach(category => {
        const block = document.createElement("div");
        block.classList.add("categories-block");
        block.setAttribute("onclick", "selectCategory(this)");

        block.innerHTML = `
        <i class="${category.icon}"></i>
        <p>${category.name}</p>
      `;

        container.appendChild(block);
    });
}


// ฟังก์ชันสำหรับการสุ่มอีเว้นท์ 4 รายการ
function getRandomEvents() {
    const randomEvents = [];
    const shuffledEvents = events.sort(() => 0.5 - Math.random());
    for (let i = 0; i < 4; i++) {
        randomEvents.push(shuffledEvents[i]);
    }
    return randomEvents;
}

// ฟังก์ชันเลือกหมวดหมู่
function selectCategory(selectedBlock) {
    const blocks = document.querySelectorAll(".categories-block");
    blocks.forEach(block => block.classList.remove("selected"));
    selectedBlock.classList.add("selected");

    const selectedCategory = selectedBlock.querySelector("p").textContent;
    filterEventsByCategory(selectedCategory);
}

// ฟังก์ชันสำหรับการกดดาว
function toggleStar(starElement) {
    const icon = starElement.querySelector("i");
    icon.classList.toggle("fas");
    icon.classList.toggle("far");
}

// เรียกฟังก์ชันเมื่อหน้าโหลดเสร็จ
window.onload = function () {
    createCategoriesBlocks();
    filterEventsByCategory("Random");
};


// เดือน //
document.addEventListener("DOMContentLoaded", () => {
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    const today = new Date();
    let currentYear = today.getFullYear();
    let currentMonth = today.getMonth();

    const monthNameElement = document.querySelector(".month-name");
    const yearNumberElement = document.querySelector(".year-number");
    const calendarGrid = document.querySelector(".calendar-grid");
    const eventContainer = document.getElementById("eventContainer");

    const events = [
        {
            name: "The First Stage Talent",
            category: "Theater",
            image: "../assets/imgs/firststage.jpg",
            time: "17.00",
            date: "2025-01-20",
            location: "ลาน NU Playground",
            details: "โชว์เล่นรูบิคสนุกๆ นั่งชิวๆ ฟังเพลง Folksong ร้านอาหารกว่า 80 ร้านค้า",
            ticketPrice: "Free",
        },
        {
            name: "NU Identity Festival X Freshmen Night 2024",
            category: "Theater",
            image: "../assets/imgs/identity.jpg",
            time: "08:00",
            date: "2025-02-20",
            location: "KNECC",
            details: "แสดงแสงสีเสียงพระราชประวัติฯ และเต้นประกอบเพลง",
            ticketPrice: "Free",
        },
    ];

    function updateCalendar() {
        monthNameElement.textContent = monthNames[currentMonth];
        yearNumberElement.textContent = currentYear;

        calendarGrid.innerHTML = `
            <div class="calendar-day">Sun</div>
            <div class="calendar-day">Mon</div>
            <div class="calendar-day">Tue</div>
            <div class="calendar-day">Wed</div>
            <div class="calendar-day">Thu</div>
            <div class="calendar-day">Fri</div>
            <div class="calendar-day">Sat</div>
        `;

        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        // เพิ่มช่องว่างก่อนวันที่ 1
        for (let i = 0; i < firstDay; i++) {
            const emptyDate = document.createElement("div");
            emptyDate.classList.add("calendar-date", "date-inactive");
            calendarGrid.appendChild(emptyDate);
        }

        // สร้างปุ่มวัน
        for (let day = 1; day <= daysInMonth; day++) {
            const dateButton = document.createElement("button");
            dateButton.classList.add("calendar-date");
            dateButton.textContent = day;

            const selectedDate = new Date(currentYear, currentMonth, day);
            const selectedDateString = selectedDate.toISOString().split("T")[0]; // 'yyyy-mm-dd'

            const eventsOnDate = events.filter(event => event.date === selectedDateString);

            if (eventsOnDate.length > 0) {
                const eventDot = document.createElement("span");
                eventDot.classList.add("event-dot");
                dateButton.appendChild(eventDot);
            }

            dateButton.addEventListener("click", () => {
                document.querySelectorAll(".calendar-date.selected").forEach(el => el.classList.remove("selected"));
                dateButton.classList.add("selected");

                if (eventsOnDate.length > 0) {
                    document.getElementById("eventName").textContent = eventsOnDate[0].name;
                    document.getElementById("eventTime").textContent = eventsOnDate[0].time;
                    document.getElementById("eventLocation").textContent = eventsOnDate[0].location;
                    document.getElementById("eventDate").textContent = eventsOnDate[0].date;
                    document.getElementById("eventDetails").textContent = eventsOnDate[0].details;
                    document.getElementById("eventImage").src = eventsOnDate[0].image;

                    document.getElementById("noEventMessage").style.display = "none";
                    document.getElementById("noEventImage").style.display = "none";

                    eventContainer.classList.add("show");
                } else {
                    document.getElementById("eventName").textContent = "ไม่มีข้อมูลอีเว้นท์";
                    document.getElementById("eventTime").textContent = "ไม่มีข้อมูลอีเว้นท์";
                    document.getElementById("eventLocation").textContent = "ไม่มีข้อมูลอีเว้นท์";
                    document.getElementById("eventDate").textContent = "ไม่มีข้อมูลอีเว้นท์";
                    document.getElementById("eventDetails").textContent = "ไม่มีข้อมูลอีเว้นท์";
                    document.getElementById("eventImage").url = "../imgs/artyoung.jpg";

                    eventContainer.classList.add("show");
                }
            });

            if (currentYear === today.getFullYear() && currentMonth === today.getMonth() && day === today.getDate()) {
                dateButton.classList.add("today");
            }

            calendarGrid.appendChild(dateButton);
        }
    }

    document.getElementById("prev-month").addEventListener("click", () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar();
    });

    document.getElementById("next-month").addEventListener("click", () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar();
    });

    updateCalendar();
});


//
// 
//  
//end home.html //



// 
// 
// 
// Start RegisterForFree.html //
function openModal() {
    document.getElementById('registerModal').classList.add('active');
}


function confirmRegister() {
    document.getElementById('registerModal').classList.remove('active');
    alert("Registration Confirmed!");
    window.location.href = 'history.html';
}


// หน้า aRegisterForPaid.html








// หน้า aRegisterForFree.html
function openModal() {
    document.getElementById('registerModal').classList.add('active');
}




// เช็คราคาตั๋ว




// กระดิ่ง
function toggleNotification(icon) {
    if (icon.classList.contains("bi-bell")) {
        icon.classList.remove("bi-bell");
        icon.classList.add("bi-bell-fill");
        alert("ทำการเปิดแจ้งเตือนอีเวนต์นี้เรียบร้อยแล้ว!");
    } else {
        icon.classList.remove("bi-bell-fill");
        icon.classList.add("bi-bell");
    }
}

// 
// 
// 
// end RegisterForFree.html //

