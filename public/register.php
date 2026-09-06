<?php
session_start();
require '../config/db_connect.php';

$form_message = null;

if(isset($_GET['error'])){
    $form_message = [
        'type' => 'error',
        'text' => "Registration failed. Please try again."
    ];
}
elseif(isset($_GET['success'])){
    $form_message = [
        'type' => 'success',
        'text' => "Successful Registration. Please login"
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Asset Tracker System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <div class="form-box register">
        <h2>Register</h2>
        <?php if ($form_message && $form_message['type'] === 'success'): ?>
            <div class="form-message success">
                <span class="form-icon success">✅</span>
                <?= $form_message['text'] ?>
            </div>
            <!-- Registration form is hidden after success -->
        <?php else: ?>
            <?php if ($form_message): ?>
                <div class="form-message <?= $form_message['type'] ?>">
                    <?php if ($form_message['type'] === 'error'): ?>
                        <span class="form-icon error">❌</span>
                    <?php endif; ?>
                    <?= $form_message['text'] ?>
                </div>
            <?php endif; ?>
            <form action="register_process.php" method="POST" autocomplete="off" novalidate>
                <div class="input-box">
                    <label for="username">Username</label>
                    <input type="text" id="user_name" name="user_name" required>
                </div>

                <div class="input-box">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Email">
                </div>

                <div class="input-box">
                    <label for="department">Department</label>
                    <select name="department" id="department" required>
                        <option value="">Select department</option>
                        <option value="HR">HR</option>
                        <option value="IT">IT</option>
                        <option value="Finance">Finance</option>
                        <option value="Procurement">Procurement</option>
                    </select>
                </div>

                <div class="input-box">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                    <i class="bx bx-hide eye-slash" data-target="loginPassword"></i>

                </div>
                <div class="input-box">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    <i class="bx bx-hide eye-slash" data-target="confirmPassword"></i>

                </div>

                <button class="btn" type="submit">Register</button>
            </form>
        <?php endif; ?>
    </div>
    <!-- TOGGLE SECTION -->
    <div class="toggle-panel toggle-right">
        <h1>Welcome Back!</h1>
        <p>Already have an account?</p>
        <button class="btn login-btn">Login</button>
      </div>
    </div>
</div>
<script>
document.querySelector("form").addEventListener("submit", function(e) {
    var pw = document.getElementById("password").value;
    var cpw = document.getElementById("confirm_password").value;
    if (pw !== cpw) {
        alert("Passwords must match.");
        e.preventDefault();
    }
});
</script>
</body>
</html>
