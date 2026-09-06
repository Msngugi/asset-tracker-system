// ========== SEARCH FUNCTIONALITY ==========
const searchBox = document.querySelector('.search-box input');

if (searchBox) {
    searchBox.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        console.log('Searching for:', query);
        // Add AJAX search functionality here
    });
}

// ========== NOTIFICATION BELL ==========
const notificationBtn = document.querySelectorAll('.icon-btn')[0];

if (notificationBtn) {
    notificationBtn.addEventListener('click', function() {
        console.log('Notifications clicked');
        // Add notification dropdown here
    });
}

// ========== USER PROFILE DROPDOWN ==========
const userProfile = document.querySelector('.user-profile');

if (userProfile) {
    userProfile.addEventListener('click', function() {
        console.log('User profile clicked');
        // Add dropdown menu here
    });
}

// ========== SIDEBAR ACTIVE STATE ==========
function updateSidebarActive() {
    const currentPage = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');

    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').includes(currentPage)) {
            link.classList.add('active');
        }
    });
}

document.addEventListener('DOMContentLoaded', updateSidebarActive);

// ========== SMOOTH ANIMATIONS ==========
const cards = document.querySelectorAll('.stat-card, .chart-card, .activity-card');

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

cards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.6s ease';
    observer.observe(card);
});

// ========== AUTO-REFRESH DATA ==========
function refreshDashboardData() {
    console.log('Dashboard data refreshed at ' + new Date().toLocaleTimeString());
    // Add AJAX call to refresh stats here
}

// Refresh every 5 minutes
setInterval(refreshDashboardData, 5 * 60 * 1000);