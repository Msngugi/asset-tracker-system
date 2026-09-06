function autoHideMessage(element) {
    if (!element) return;

    setTimeout(() => {
        element.style.transition = 'opacity 0.5s ease-out';
        element.style.opacity = '0';

        setTimeout(() => {
            element.style.display = 'none';
        }, 500);
    }, 3000); // ⏱️ 3 seconds
}

document.addEventListener('DOMContentLoaded', function () {
    console.log("JS is running...");
// ========== PANEL TOGGLE (Sign Up / Log In) ==========
const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');

if (registerBtn && loginBtn && container) {
    registerBtn.addEventListener('click', () => {
        container.classList.add('active');
    });
    loginBtn.addEventListener('click', () => {
        container.classList.remove('active');
    });
}

// ========== AVAILABILITY CHECK VARIABLES ==========
let usernameAvailable = true; // Assume available until proven otherwise
let emailAvailable = true;

// ========== REAL-TIME USERNAME & EMAIL AVAILABILITY CHECK ==========
const regUsername = document.getElementById('regUsername');
const regEmail = document.getElementById('regEmail');
const registerError = document.getElementById('registerError');

// Check username availability
if (regUsername) {
    regUsername.addEventListener('blur', function() {
        const username = this.value.trim();
        
        if (username.length >= 3) {
            fetch('check_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'type=username&value=' + encodeURIComponent(username)
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    regUsername.style.borderColor = '#4CAF50';
                    usernameAvailable = true;
                    registerError.textContent = '';
                    registerError.className = '';
                } else {
                    regUsername.style.borderColor = '#f44336';
                    registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Username already taken.";
                    registerError.className = "form-message error";
                    usernameAvailable = false;
                }
            })
            .catch(error => {
                console.error('Error checking username:', error);
                usernameAvailable = false;
            });
        } else if (username.length > 0 && username.length < 3) {
            regUsername.style.borderColor = '#ff9800';
        }
    });
}

// Check email availability
if (regEmail) {
    regEmail.addEventListener('blur', function() {
        const email = this.value.trim();
        
        if (email.length > 0 && email.includes('@')) {
            fetch('check_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'type=email&value=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    regEmail.style.borderColor = '#4CAF50';
                    emailAvailable = true;
                    // Don't clear error here if username error exists
                    if (usernameAvailable) {
                        registerError.textContent = '';
                        registerError.className = '';
                    }
                } else {
                    regEmail.style.borderColor = '#f44336';
                    registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Email already registered.";
                    registerError.className = "form-message error";
                    emailAvailable = false;
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
                emailAvailable = false;
            });
        }
    });
}

// ========== REGISTER FORM VALIDATION ==========
const registerForm = document.getElementById('registerForm');

if (registerForm && registerError) {
    registerForm.addEventListener('submit', function (e) {
        // Get field values
        const username = document.getElementById('regUsername').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const department = document.querySelector('select[name="department"]').value;
        const password = document.getElementById('regPassword').value;
        const confirmPassword = document.getElementById('regConfirmPassword').value;

        // Clear previous errors
        registerError.textContent = "";
        registerError.className = "";

        // CHECK EMPTY FIELDS FIRST
        if (!username || !email || !password || !confirmPassword || department === "") {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Please fill in all the fields.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
              e.preventDefault();
              return;
}
        // Check if availability check found duplicates

        if(!usernameAvailable && !emailAvailable){
       
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Username and email already registered. Please choose different ones.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        if (!usernameAvailable) {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Username already taken. Please choose a different one.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        if (!emailAvailable) {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Email already registered. Please use a different email.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        // Validation checks
        if (username.length < 4) {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Username must be at least 4 characters.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Please enter a valid email address.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        if (department === "") {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Please select a department.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        if (password.length < 8) {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Password must be at least 8 characters.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }

        if (password !== confirmPassword) {
            registerError.innerHTML = "<span class=\"form-icon error\">❌</span>Passwords do not match.";
            registerError.className = "form-message error";
            autoHideMessage(registerError);
            e.preventDefault();
            return;
        }
        
        // If all validations pass, form will submit
    });
}
 

// ========== LOGIN FORM VALIDATION ==========
const loginForm = document.getElementById('loginForm');
const loginError = document.getElementById('loginError');

if (loginForm && loginError) {
    loginForm.addEventListener('submit', function (e) {
        const username = document.getElementById('loginUsername').value.trim();
        const password = document.getElementById('loginPassword').value;

        loginError.textContent = "";
        loginError.className = "";

        //  CHECK EMPTY FIELDS FIRST
if (!username || !password) {
    loginError.innerHTML = "<span class=\"form-icon error\">❌</span>Please fill in your username and password.";
    loginError.className = "form-message error";
    autoHideMessage(loginError);
    e.preventDefault();
    return;
}

        // If all validations pass, form will submit
    });
}

// ========== DISPLAY ERROR FROM QUERY PARAMS FOR LOGIN ==========
const params = new URLSearchParams(window.location.search);
if (loginError && params.get("error") === "invalid_credentials") {
    loginError.innerHTML = "<span class=\"form-icon error\">❌</span>Invalid username or password.";
    loginError.className = "form-message error";
    autoHideMessage(loginError);
}

// ========== TOGGLE PASSWORD VISIBILITY ==========
document.querySelectorAll('.eye-slash').forEach(icon => {
    icon.addEventListener('click', function () {
        const input = document.getElementById(this.getAttribute('data-target'));

        if (!input) return; // prevent crash

        if (input.type === "password") {
            input.type = "text";
            this.classList.remove('bx-hide');
            this.classList.add('bx-show');

        } else {
            input.type = "password";
            this.classList.remove('bx-show');
            this.classList.add('bx-hide');
        }
    });
});
});

    
window.addEventListener('DOMContentLoaded', function () {
    const serverMessage = document.querySelector('.form-message');

    if (serverMessage) {
        autoHideMessage(serverMessage);
    }
});
      // Auto-hide server messages (login/register success/errors)
const serverMessage = document.querySelector('.form-message');
if (serverMessage) {
    autoHideMessage(serverMessage);
}