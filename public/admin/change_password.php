<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../config/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['admin123'] ?? '';
    $new_password = $_POST['Admin@2026'] ?? '';
    $confirm_password = $_POST['Admin@2026'] ?? '';

    // ============ VALIDATION ============
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters long';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // ============ GET CURRENT ADMIN PASSWORD FROM DATABASE ============
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        
        if (!$stmt) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = 'Admin user not found';
            } else {
                $user_data = $result->fetch_assoc();
                $stored_password = $user_data['password'];
                
                error_log("=== PASSWORD VERIFICATION DEBUG ===");
                error_log("User ID: " . $_SESSION['user_id']);
                error_log("Input password length: " . strlen($current_password));
                error_log("Stored hash: " . substr($stored_password, 0, 20) . "...");
                error_log("Password verify result: " . (password_verify($current_password, $stored_password) ? 'TRUE' : 'FALSE'));
                
                // ============ VERIFY CURRENT PASSWORD ============
                if (!password_verify($current_password, $stored_password)) {
                    $error = 'Current password is incorrect';
                    error_log("ERROR: Current password verification failed");
                } else {
                    // ============ CHECK IF NEW PASSWORD IS SAME AS OLD ============
                    if (password_verify($new_password, $stored_password)) {
                        $error = 'New password cannot be the same as current password';
                    } else {
                        // ============ HASH NEW PASSWORD ============
                        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                        
                        // ============ UPDATE PASSWORD IN DATABASE ============
                        $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        
                        if (!$update) {
                            $error = 'Database error: ' . $conn->error;
                        } else {
                            $update->bind_param("si", $hashed_password, $_SESSION['user_id']);
                            
                            if ($update->execute()) {
                                $success = '✓ Password changed successfully!';
                                error_log("SUCCESS: Admin password changed for user ID: " . $_SESSION['user_id']);
                                
                                // Log the action
                                $log_stmt = $conn->prepare("
                                    INSERT INTO user_logs (user_id, admin_id, action, description)
                                    VALUES (?, ?, 'Password Changed', 'Admin changed their own password')
                                ");
                                if ($log_stmt) {
                                    $log_stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
                                    $log_stmt->execute();
                                    $log_stmt->close();
                                }
                            } else {
                                $error = 'Failed to update password: ' . $update->error;
                                error_log("ERROR: Update failed - " . $update->error);
                            }
                            $update->close();
                        }
                    }
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Admin Password</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <style>
        .password-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-field input {
            flex: 1;
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 18px;
            color: #666;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: #4f46e5;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 4px;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background-color 0.3s;
            background-color: #ef4444;
        }

        .strength-weak { background-color: #ef4444; }
        .strength-fair { background-color: #f59e0b; }
        .strength-good { background-color: #3b82f6; }
        .strength-strong { background-color: #10b981; }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1f2937;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .password-requirements {
            background: #f3f4f6;
            padding: 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #374151;
            margin-top: 10px;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 4px 0;
        }

        .requirement-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #d1d5db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        .requirement-icon.met {
            background: #10b981;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/admin_header.php'; ?>
            
            <main class="admin-content">
                <div class="dashboard-header">
                    <h1>Change Admin Password</h1>
                    <p>Update your admin account password to keep your account secure</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class='bx bx-check-circle'></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class='bx bx-x-circle'></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="chart-card" style="max-width: 600px;">
                    <form method="POST" id="passwordForm">
                        <div class="form-group">
                            <label for="current_password"><i class='bx bx-lock'></i> Current Password</label>
                            <div class="password-field">
                                <input 
                                    type="password" 
                                    id="current_password"
                                    name="current_password" 
                                    required 
                                    placeholder="Enter your current password"
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password"><i class='bx bx-lock-open'></i> New Password</label>
                            <div class="password-field">
                                <input 
                                    type="password" 
                                    id="new_password"
                                    name="new_password" 
                                    required 
                                    placeholder="Create a strong new password"
                                    oninput="checkPasswordStrength()"
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>
                            
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-bar-fill" id="strengthBar"></div>
                                </div>
                                <span id="strengthText">Password strength: -</span>
                            </div>

                            <div class="password-requirements">
                                <div class="requirement-item">
                                    <span class="requirement-icon" id="req-length">✓</span>
                                    <span>At least 8 characters</span>
                                </div>
                                <div class="requirement-item">
                                    <span class="requirement-icon" id="req-upper">✓</span>
                                    <span>Uppercase letter (A-Z)</span>
                                </div>
                                <div class="requirement-item">
                                    <span class="requirement-icon" id="req-lower">✓</span>
                                    <span>Lowercase letter (a-z)</span>
                                </div>
                                <div class="requirement-item">
                                    <span class="requirement-icon" id="req-number">✓</span>
                                    <span>Number (0-9)</span>
                                </div>
                                <div class="requirement-item">
                                    <span class="requirement-icon" id="req-special">✓</span>
                                    <span>Special character (!@#$%^&*)</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password"><i class='bx bx-lock'></i> Confirm New Password</label>
                            <div class="password-field">
                                <input 
                                    type="password" 
                                    id="confirm_password"
                                    name="confirm_password" 
                                    required 
                                    placeholder="Confirm your new password"
                                    oninput="checkPasswordMatch()"
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                    <i class='bx bx-show'></i>
                                </button>
                            </div>
                            <div id="matchMessage" style="margin-top: 8px; font-size: 12px;"></div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-check'></i> Change Password
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class='bx bx-refresh'></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const btn = event.target.closest('button');
            const icon = btn.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            } else {
                field.type = 'password';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            }
        }

        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            let strength = 0;
            const requirements = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };

            // Update requirement indicators
            document.getElementById('req-length').className = requirements.length ? 'requirement-icon met' : 'requirement-icon';
            document.getElementById('req-upper').className = requirements.upper ? 'requirement-icon met' : 'requirement-icon';
            document.getElementById('req-lower').className = requirements.lower ? 'requirement-icon met' : 'requirement-icon';
            document.getElementById('req-number').className = requirements.number ? 'requirement-icon met' : 'requirement-icon';
            document.getElementById('req-special').className = requirements.special ? 'requirement-icon met' : 'requirement-icon';

            // Calculate strength
            Object.values(requirements).forEach(req => {
                if (req) strength++;
            });

            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const barWidth = (strength / 5) * 100;
            let strengthLabel = '';
            let strengthClass = '';

            strengthBar.style.width = barWidth + '%';

            if (strength <= 1) {
                strengthLabel = 'Very Weak';
                strengthClass = 'strength-weak';
            } else if (strength === 2) {
                strengthLabel = 'Weak';
                strengthClass = 'strength-weak';
            } else if (strength === 3) {
                strengthLabel = 'Fair';
                strengthClass = 'strength-fair';
            } else if (strength === 4) {
                strengthLabel = 'Good';
                strengthClass = 'strength-good';
            } else if (strength === 5) {
                strengthLabel = 'Strong';
                strengthClass = 'strength-strong';
            }

            strengthBar.className = 'strength-bar-fill ' + strengthClass;
            strengthText.textContent = 'Password strength: ' + strengthLabel;

            checkPasswordMatch();
        }

        // Check if passwords match
        function checkPasswordMatch() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchMessage = document.getElementById('matchMessage');

            if (confirmPassword === '') {
                matchMessage.textContent = '';
                return;
            }

            if (newPassword === confirmPassword) {
                matchMessage.innerHTML = '<span style="color: #10b981;"><i class="bx bx-check-circle"></i> Passwords match</span>';
            } else {
                matchMessage.innerHTML = '<span style="color: #ef4444;"><i class="bx bx-x-circle"></i> Passwords do not match</span>';
            }
        }

        // Initialize on page load
        window.addEventListener('load', checkPasswordStrength);
    </script>
</body>
</html>