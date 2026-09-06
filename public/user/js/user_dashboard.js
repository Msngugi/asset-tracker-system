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

// ========== VIEW ASSET DETAILS ==========
function viewAssetDetails(assetId) {
    console.log('View asset details:', assetId);
    // Can be expanded later for modal or detail page
}

// ========== SEARCH ==========
const searchBox = document.querySelector('.search-box input');
if (searchBox) {
    searchBox.addEventListener('input', function(e) {
        console.log('Searching:', e.target.value);
    });
}

// ========== SMOOTH ANIMATIONS ==========
const cards = document.querySelectorAll('.stat-card, .card, .asset-card');

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

// ========== NOTIFICATIONS ==========
const notificationBtn = document.querySelectorAll('.icon-btn')[0];
if (notificationBtn) {
    notificationBtn.addEventListener('click', function() {
        console.log('Notifications clicked');
    });
}

// ========== USER PROFILE ==========
const userProfile = document.querySelector('.user-profile');
if (userProfile) {
    userProfile.addEventListener('click', function() {
        console.log('User profile clicked');
    });
}