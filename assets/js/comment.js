
// 
// 
// 
// Start feedback.html //

// สุ่ม comment หน้าdetail
let currentRating = 0;  

function rateComment(rating) {
    currentRating = rating; 
    const stars = document.querySelectorAll('.aFeedback-star');

    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.color = 'gold';  
        } else {
            star.style.color = 'gray';  
        }
    });
}

function getRandomComment() {
    const comments = [
        { text: "ความคิดเห็นที่ 1: เหมือนจะดีนะ", rating: 5 },
        { text: "ความคิดเห็นที่ 2: น่าสนใจมากๆ เลย", rating: 4 },
        { text: "ความคิดเห็นที่ 3: อยากเห็นความคืบหน้าเร็วๆ", rating: 3 },
        { text: "ความคิดเห็นที่ 4: แนะนำให้ปรับปรุงส่วนนี้หน่อย", rating: 2 },
        { text: "ความคิดเห็นที่ 5: ปรับปรุงมากๆ", rating: 1 }
    ];

    const randomIndex = Math.floor(Math.random() * comments.length);
    const comment = comments[randomIndex];
    
    document.getElementById('mainComment').value = comment.text;
    currentRating = comment.rating;

    const stars = document.querySelectorAll('.aFeedback-star');
    stars.forEach((star, index) => {
        if (index < currentRating) {
            star.style.color = 'gold';  // ดาวที่เลือก
        } else {
            star.style.color = 'gray';  // ดาวที่ยังไม่เลือก
        }
    });
}

// เรียกฟังก์ชั่นสุ่มเมื่อโหลดหน้า
window.onload = getRandomComment;

function changeTab(tab) {
    const tabs = document.querySelectorAll('.aFeedback-navbar span');
    tabs.forEach(tab => tab.classList.remove('active', 'rating-active', 'comment-active'));

    const activeTab = document.getElementById(tab + 'Tab');
    activeTab.classList.add('active');

    if (tab === 'rating') {
        activeTab.classList.add('rating-active');
    } else {
        activeTab.classList.add('comment-active');
    }

    const ratingContent = document.getElementById('ratingContent');
    const commentContent = document.getElementById('commentContent');

    if (tab === 'rating') {
        ratingContent.classList.add('active');
        commentContent.classList.remove('active');

        document.getElementById("allContent").style.display = "none";
        document.getElementById("starsContent").style.display = "none";
        document.getElementById("selectContent").style.display = "none";
    } else {
        ratingContent.classList.remove('active');
        commentContent.classList.add('active');
        
        document.getElementById("allContent").style.display = "none";
        document.getElementById("starsContent").style.display = "none";
        document.getElementById("selectContent").style.display = "none";
    }
}


//RATING TYPE
document.querySelectorAll('.aFeedback-rating-in-event .rating-option').forEach(option => {
  option.addEventListener('click', function() {
      document.querySelectorAll('.aFeedback-rating-in-event .rating-option').forEach(item => {
          item.classList.remove('selected');
      });

      this.classList.add('selected');
  });
});

//RATING EVENT
function showContent(type) {
    document.getElementById("allContent").style.display = "none";
    document.getElementById("starsContent").style.display = "none";
    document.getElementById("selectContent").style.display = "none";

    document.getElementById("option1Content").style.display = "none";
    document.getElementById("option2Content").style.display = "none";
    document.getElementById("option3Content").style.display = "none";

    const labels = document.querySelectorAll('.aFeedback-rating-option');
    labels.forEach(label => {
        label.classList.remove('selected-label');  
    });

    if (type === "all") {
        document.getElementById("allContent").style.display = "block";
        document.querySelector('label[onclick="showContent(\'all\')"]').classList.add('selected-label');
    } else if (type === "stars") {
        document.getElementById("starsContent").style.display = "block";
        document.querySelector('label[onclick="showContent(\'stars\')"]').classList.add('selected-label');
    } else if (type === "select") {
        document.getElementById("selectContent").style.display = "block";
        document.querySelector('label[onclick="showContent(\'select\')"]').classList.add('selected-label');
    }
}

function showDropdownContent() {
    let selection = document.getElementById("dropdownSelection").value;
    
    document.getElementById("option1Content").style.display = "none";
    document.getElementById("option2Content").style.display = "none";
    document.getElementById("option3Content").style.display = "none";

    if (selection === "doption1") {
        document.getElementById("option1Content").style.display = "block";
    } else if (selection === "doption2") {
        document.getElementById("option2Content").style.display = "block";
    } else if (selection === "doption3") {
        document.getElementById("option3Content").style.display = "block";
    }
    
}
document.addEventListener('DOMContentLoaded', function() {
    showContent('all');
});



