<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

$form_message = null;

// Check for messages from query parameters
if (isset($_GET['success']) && $_GET['success'] === 'registered') {
    $form_message = [
        'type' => 'success',
        'text' => "Registration successful! Please login."
    ];
}

if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials') {
    $form_message = [
        'type' => 'error',
        'text' => "Invalid username or password."
    ];
}

// Check for messages from session (set by login_process.php)
if (isset($_SESSION['login_message'])) {
    $form_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AssetFlow - Login & Registration</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">

    <!-- LOGIN FORM -->
    <div class="form-box login">
      <!-- LOGO HEADER -->
      <div class="logo-header">
        <div class="logo-lines">
          <div class="logo-line"></div>
          <div class="logo-line"></div>
          <div class="logo-line"></div>
        </div>
        <div class="logo-dots">
          <div class="logo-dot"></div>
          <div class="logo-dot"></div>
          <div class="logo-dot"></div>
        </div>
        <div class="logo-text">AssetFlow</div>
      </div>

      <form action="login_process.php" method="POST" id="loginForm" novalidate>
        <h2>Login</h2>

        <!-- Display login message if exists -->
        <?php if ($form_message): ?>
            <div class="form-message <?= $form_message['type'] ?>">
                <?php if ($form_message['type'] === 'success'): ?>
                    <span class="form-icon">✅</span>
                <?php else: ?>
                    <span class="form-icon">❌</span>
                <?php endif; ?>
                <?= $form_message['text'] ?>
            </div>
        <?php endif; ?>

        <div class="input-box">
          <input type="text" id="loginUsername" name="username" placeholder="Username" required>
          <i class='bx bx-user'></i>
        </div>

        <div class="input-box">
          <input type="password" id="loginPassword" name="password" placeholder="Password" required>
          <i class="bx bx-eye-slash eye-slash" data-target="loginPassword"></i>
        </div>

        <div class="login-options">
          <label class="remember-me">
              <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="#" class="forgot-password">Forgot Password?</a>
        </div>

        <div id="loginError"></div>

        <button type="submit" class="btn">Login</button>
      </form>
    </div>

    <!-- REGISTER FORM -->
    <div class="form-box register">
      <!-- LOGO HEADER -->
      <div class="logo-header">
        <div class="logo-lines">
          <div class="logo-line"></div>
          <div class="logo-line"></div>
          <div class="logo-line"></div>
        </div>
        <div class="logo-dots">
          <div class="logo-dot"></div>
          <div class="logo-dot"></div>
          <div class="logo-dot"></div>
        </div>
        <div class="logo-text">AssetFlow</div>
      </div>

      <form action="register_process.php" method="POST" id="registerForm" novalidate>
        <h2>Register</h2>

        <div class="input-box">
          <input type="text" name="user_name" id="regUsername" placeholder="Username" required>
          <i class='bx bx-user'></i>
        </div>

        <div class="input-box">
          <input type="email" name="email" id="regEmail" placeholder="Email" required>
          <i class='bx bx-envelope'></i>
        </div>

        <div class="input-box">
          <input type="password" name="password" id="regPassword" placeholder="Password" required>
          <i class="bx bx-eye-slash eye-slash" data-target="regPassword"></i>
        </div>

        <div class="input-box">
          <input type="password" name="confirm_password" id="regConfirmPassword" placeholder="Confirm Password" required>
          <i class="bx bx-eye-slash eye-slash" data-target="regConfirmPassword"></i>
        </div>

        <div class="input-box">
          <select name="department" id="department" required>
            <option value="">Select department</option>
            <option value="HR">HR</option>
            <option value="IT">IT</option>
            <option value="Finance">Finance</option>
            <option value="Procurement">Procurement</option>
          </select>
          <i class="bx bx-building"></i>
        </div>

        <div id="registerError"></div>

        <button type="submit" class="btn">Register</button>
      </form>
    </div>

    <!-- TOGGLE SECTION -->
    <div class="toggle-box">
      <div class="toggle-panel toggle-left">
        <h1>Hello, Welcome!</h1>
        <p>Don't have an account?</p>
        <button type="button" class="btn register-btn">Register</button>
      </div>

      <div class="toggle-panel toggle-right">
        <h1>Welcome Back!</h1>
        <p>Already have an account?</p>
        <button type="button" class="btn login-btn">Login</button>
      </div>
    </div>

  </div>

  <script src="./script.js"></script>
</body>
</html>