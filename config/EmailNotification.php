<?php

require_once __DIR__ . '/mail_config.php';

class EmailNotification {
    
    private mysqli$conn;
    private  string$template_dir;
    
    public function __construct(mysqli $database_connection) {
        $this->conn = $database_connection;
        $this->template_dir = __DIR__ . '/../templates/emails/';
        
        error_log("✅ [EMAIL NOTIF] EmailNotification instance created");
        error_log("   Template dir: " . $this->template_dir);
    }
    
    /**
     * Render email template with data
     */
    private function renderTemplate(string $template_name, array $data = []): bool|string {
        $template_path = $this->template_dir . $template_name . '.php';
        
        error_log("📄 [TEMPLATE] Attempting to render: " . $template_name);
        error_log("   Path: " . $template_path);
        
        if (!file_exists($template_path)) {
            error_log("❌ [TEMPLATE] Template file not found: " . $template_path);
            error_log("   Available files in " . $this->template_dir . ":");
            if (is_dir($this->template_dir)) {
                $files = scandir($this->template_dir);
                foreach ($files as $f) {
                    if ($f !== '.' && $f !== '..') {
                        error_log("      - $f");
                    }
                }
            }
            return false;
        }
        
        try {
            // Extract variables for template
            extract($data, EXTR_SKIP);
            
            // Start output buffering
            ob_start();
            
            // Include template file
            include $template_path;
            
            // Get rendered HTML
            $html = ob_get_clean();
            
            error_log("✅ [TEMPLATE] Template rendered successfully");
            
            return $html;
        } catch (Exception $e) {
            error_log("❌ [TEMPLATE] Error rendering template: " . $e->getMessage());
            ob_end_clean();
            return false;
        }
    }
    
    /**
     * Send borrow confirmation email
     */
    public function sendBorrowConfirmation(string $user_email, string $user_name, int $user_id, string $asset_name, ?string $asset_model, ?string $serial_number, string $borrow_date, string $expected_return_date, int $borrow_days) {
        
        error_log("📧 [BORROW CONFIRMATION] Processing...");
        
        try {
            $data = [
                'user_email' => $user_email,
                'user_name' => $user_name,
                'user_id' => $user_id,
                'asset_name' => $asset_name,
                'asset_model' => $asset_model ?? 'N/A',
                'serial_number' => $serial_number ?? 'N/A',
                'borrow_date' => $borrow_date,
                'expected_return_date' => $expected_return_date,
                'borrow_days' => $borrow_days
            ];
            
            $html_body = $this->renderTemplate('borrow_confirmation', $data);
            
            if (!$html_body) {
                error_log("❌ [BORROW CONFIRMATION] Template rendering failed");
                return false;
            }
            
            $subject = "✓ Asset Borrowed Successfully - " . $asset_name;
            
            error_log("📧 [BORROW CONFIRMATION] About to send email via PHPMailer");
            $result = sendEmailViaMailer($user_email, $user_name, $subject, $html_body);
            
            if ($result) {
                error_log("✅ [BORROW CONFIRMATION] Email sent successfully");
                
                // Log to database (optional)
                $this->logEmailSent('borrow_confirmation', $user_id, $user_email, $subject);
            } else {
                error_log("❌ [BORROW CONFIRMATION] Email sending failed");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [BORROW CONFIRMATION] Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send return reminder (DUE IN 1 DAY)
     */
    public function sendReturnDueReminder(string $user_email, string $user_name, int $user_id, string $asset_name, string $return_date, int $days_remaining) {
        
        error_log("📅 [RETURN REMINDER] Processing...");
        
        try {
            $data = [
                'user_name' => $user_name,
                'asset_name' => $asset_name,
                'return_date' => $return_date,
                'days_remaining' => $days_remaining
            ];
            
            $html_body = $this->renderTemplate('return_due_reminder', $data);
            
            if (!$html_body) {
                error_log("❌ [RETURN REMINDER] Template rendering failed");
                return false;
            }
            
            $subject = "📅 Reminder: Asset Due for Return - " . $asset_name;
            
            $result = sendEmailViaMailer($user_email, $user_name, $subject, $html_body);
            
            if ($result) {
                error_log("✅ [RETURN REMINDER] Email sent successfully");
                $this->logEmailSent('return_due_reminder', $user_id, $user_email, $subject);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [RETURN REMINDER] Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send return confirmation email
     */
    public function sendReturnConfirmation(string $user_email, string $user_name, int $user_id, string $asset_name, string $return_date, int $days_borrowed, float $penalty_amount = 0, int $overdue_days = 0) {
        
        error_log("📧 [RETURN CONFIRMATION] Processing...");
        
        try {
            $data = [
                'user_name' => $user_name,
                'asset_name' => $asset_name,
                'return_date' => $return_date,
                'days_borrowed' => $days_borrowed,
                'penalty_amount' => $penalty_amount,
                'overdue_days' => $overdue_days,
                'penalty_per_day' => PENALTY_PER_DAY,
                'currency' => PENALTY_CURRENCY
            ];
            
            $html_body = $this->renderTemplate('return_confirmation', $data);
            
            if (!$html_body) {
                error_log("❌ [RETURN CONFIRMATION] Template rendering failed");
                return false;
            }
            
            $subject = "✓ Asset Return Confirmed - " . $asset_name;
            
            $result = sendEmailViaMailer($user_email, $user_name, $subject, $html_body);
            
            if ($result) {
                error_log("✅ [RETURN CONFIRMATION] Email sent successfully");
                $this->logEmailSent('return_confirmation', $user_id, $user_email, $subject);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [RETURN CONFIRMATION] Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send penalty notification email
     */
    public function sendPenaltyNotification(string $user_email, string $user_name, int $user_id, string $asset_name, float $penalty_amount, int $overdue_days, string $reason) {
        
        error_log("⚠️ [PENALTY NOTIFICATION] Processing...");
        
        try {
            $data = [
            'user_email'       => $user_email,
            'user_name'        => $user_name,
            'user_id'          => $user_id,
            'asset_name'       => $asset_name,
            'penalty_amount'   => $penalty_amount,
            'overdue_days'     => $overdue_days,
            'reason'           => $reason,
            'penalty_per_day'  => PENALTY_PER_DAY,
            'currency'         => PENALTY_CURRENCY,
            'due_date'         => date('Y-m-d', strtotime('+7 days'))
            ];
            
            $html_body = $this->renderTemplate('penalty_notification', $data);
            
            if (!$html_body) {
                error_log("❌ [PENALTY NOTIFICATION] Template rendering failed");
                return false;
            }
            
            $subject = "⚠️ Penalty Notice - " . $asset_name;
            
            $result = sendEmailViaMailer($user_email, $user_name, $subject, $html_body);
            
            if ($result) {
                error_log("✅ [PENALTY NOTIFICATION] Email sent successfully");
                $this->logEmailSent('penalty_notification', $user_id, $user_email, $subject);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [PENALTY NOTIFICATION] Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send overdue notification
     */
    public function sendOverdueNotification(string $user_email, string $user_name, int $user_id, string $asset_name, string $expected_return_date, int $overdue_days, float $penalty_amount) {
        
        error_log("🚨 [OVERDUE NOTIFICATION] Processing...");
        
        try {
            $data = [
                'user_name' => $user_name,
                'asset_name' => $asset_name,
                'expected_return_date' => $expected_return_date,
                'overdue_days' => $overdue_days,
                'penalty_amount' => $penalty_amount,
                'penalty_per_day' => PENALTY_PER_DAY,
                'currency' => PENALTY_CURRENCY
            ];
            
            $html_body = $this->renderTemplate('overdue_notification', $data);
            
            if (!$html_body) {
                error_log("❌ [OVERDUE NOTIFICATION] Template rendering failed");
                return false;
            }
            
            $subject = "🚨 Overdue Asset - " . $asset_name;
            
            $result = sendEmailViaMailer($user_email, $user_name, $subject, $html_body);
            
            if ($result) {
                error_log("✅ [OVERDUE NOTIFICATION] Email sent successfully");
                $this->logEmailSent('overdue_notification', $user_id, $user_email, $subject);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [OVERDUE NOTIFICATION] Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email to new user (for admin user creation)
     */
    public function sendWelcomeEmail(string $user_email, string $user_name, int $user_id) {
        
        error_log("👋 [WELCOME EMAIL] Processing...");
        
        try {
            $data = [
                'user_name' => $user_name,
                'app_name' => 'Asset Tracker',
                'app_url' => $_ENV['APP_URL'] ?? 'http://localhost/asset_tracker_system'
            ];
            
            $html_body = $this->renderTemplate('welcome_email', $data);
            
            if (!$html_body) {
                error_log("⚠️ [WELCOME EMAIL] Template not found, sending generic welcome");
                
                // Send generic welcome if template doesn't exist
                $html_body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px;'>
                        <h2>Welcome to Asset Tracker, $user_name!</h2>
                        <p>Your account has been created by the administrator.</p>
                        <p>You can now log in to the Asset Tracker system.</p>
                        <p>If you have any questions, contact the admin.</p>
                    </div>
                ";
            }
            
            $subject = "👋 Welcome to Asset Tracker";
            
            $result = sendEmailViaMailer($user_email, $user_name, $subject, $html_body);
            
            if ($result) {
                error_log("✅ [WELCOME EMAIL] Email sent successfully");
                $this->logEmailSent('welcome_email', $user_id, $user_email, $subject);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [WELCOME EMAIL] Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log email sent to database (for audit trail)
     */
    private function logEmailSent(string $email_type, int $user_id, string $recipient_email, string $subject) {
        try {
            // Check if email_logs table exists
            $check_table = $this->conn->prepare("
                SELECT 1 FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'email_logs'
            ");
            
            if ($check_table && $check_table->execute()) {
                $stmt = $this->conn->prepare("
                    INSERT INTO email_logs (email_type, user_id, recipient_email, subject, sent_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                if ($stmt) {
                    $stmt->bind_param("siss", $email_type, $user_id, $recipient_email, $subject);
                    $stmt->execute();
                    $stmt->close();
                    error_log("📝 [EMAIL LOG] Email logged to database");
                }
            }
        } catch (Exception $e) {
            error_log("⚠️ [EMAIL LOG] Could not log email: " . $e->getMessage());
        }
    }
}   
      
?>
