<?php
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// ============ LOAD .env FILE ============
$env_file = __DIR__ . '/../.env';

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
} else {
    error_log("⚠️ [MAIL CONFIG] .env file not found at: " . $env_file);
}

// ============ DEFINE SMTP CONSTANTS FROM .env ============
define('MAIL_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', intval($_ENV['SMTP_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? 'tls');
define('SENDER_EMAIL', $_ENV['SENDER_EMAIL'] ?? '');
define('SENDER_NAME', $_ENV['SENDER_NAME'] ?? 'Asset Tracker System');
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@gmail.com');

// ============ DEFINE PENALTY CONSTANTS ============
define('PENALTY_PER_DAY', intval($_ENV['PENALTY_PER_DAY'] ?? 50));
define('PENALTY_CURRENCY', $_ENV['PENALTY_CURRENCY'] ?? 'USD');

// ============ VALIDATE SMTP CREDENTIALS ============
if (empty(SENDER_EMAIL) || empty(MAIL_USERNAME)) {
    error_log("❌ [MAIL CONFIG] SMTP credentials not configured properly");
    error_log("   SENDER_EMAIL: " . (SENDER_EMAIL ?: 'NOT SET'));
    error_log("   MAIL_USERNAME: " . (MAIL_USERNAME ?: 'NOT SET'));
}

// ============ PHPMAILER FACTORY FUNCTION ============
/**
 * Create and configure PHPMailer instance
 */
function createMailer() {
    try {
        $mail = new PHPMailer(true);
        
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        
        // ============ FIX SSL CERTIFICATE VERIFICATION ISSUE ============
        // For local development, disable SSL certificate verification
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Set from address
        $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
        
        // Additional settings
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        error_log("✅ [MAIL CONFIG] PHPMailer instance created successfully");
        error_log("   SSL Verification: DISABLED (Development Mode)");
        
        return $mail;
    } catch (Exception $e) {
        error_log("❌ [MAIL CONFIG] Failed to create PHPMailer: " . $e->getMessage());
        return null;
    }
}

// ============ SEND EMAIL FUNCTION ============
/**
 * Send email via PHPMailer
 * @return bool
 */
function sendEmailViaMailer( string $recipient_email, string $recipient_name, string $subject, string $html_body) {
    error_log("📧 [SEND EMAIL] Starting email send process");
    error_log("   To: $recipient_email");
    error_log("   Subject: $subject");
    
    try {
        // Validate email
        if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
            error_log("❌ [SEND EMAIL] Invalid email format: $recipient_email");
            return false;
        }
        
        // Create mailer
        $mail = createMailer();
        if (!$mail) {
            error_log("❌ [SEND EMAIL] Failed to create mailer instance");
            return false;
        }
        
        // Set recipient
        $mail->addAddress($recipient_email, $recipient_name);
        
        // Set subject and body
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = strip_tags($html_body); // Plain text version
        
        // Send email
        if ($mail->send()) {
            error_log("✅ [SEND EMAIL] Email sent successfully to: $recipient_email");
            return true;
        } else {
            error_log("⚠️ [SEND EMAIL] PHPMailer send() returned false");
            error_log("   Error: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("❌ [SEND EMAIL] Exception: " . $e->getMessage());
        return false;
    }
}

?>