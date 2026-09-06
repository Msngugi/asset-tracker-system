<?php

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../config/EmailNotification.php';

/**
 * Centralized Email Service
 * All email sending happens here - ONE PLACE
 */
class EmailService {
    
    private ?EmailNotification $email_notif;
    private mysqli $conn;
    
    public function __construct(mysqli $database_connection) {
        $this->conn = $database_connection;
        
        // Initialize EmailNotification class
        if (class_exists('EmailNotification')) {
            $this->email_notif = new EmailNotification($this->conn);
        } else {
            error_log("❌ [EMAIL SERVICE] EmailNotification class not found");
            throw new Exception("EmailNotification class not found");
        }
    }
    
    /**
     * Send borrow confirmation email
     * Called when asset is successfully borrowed (regardless of who borrowed it)
     */
    public function sendBorrowConfirmation(string $user_email, string $user_name,int $user_id, array $asset_data,  string $borrow_date, string  $expected_return_date, int $borrow_days) {
        error_log("📧 [EMAIL SERVICE] Sending borrow confirmation to: $user_email");
        
        try {
            if (!method_exists($this->email_notif, 'sendBorrowConfirmation')) {
                error_log("❌ [EMAIL SERVICE] Method 'sendBorrowConfirmation' not found");
                return false;
            }
            
            $result = $this->email_notif->sendBorrowConfirmation(
                $user_email,
                $user_name,
                $user_id,
                $asset_data['asset_name'],
                $asset_data['asset_model'] ?? 'N/A',
                $asset_data['serial_number'] ?? 'N/A',
                $borrow_date,
                $expected_return_date,
                $borrow_days
            );
            
            if ($result) {
                error_log("✅ [EMAIL SERVICE] Borrow confirmation sent successfully");
            } else {
                error_log("⚠️ [EMAIL SERVICE] Borrow confirmation failed to send");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [EMAIL SERVICE] Exception in sendBorrowConfirmation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send return confirmation email
     * Called when asset is successfully returned (regardless of who returned it)
     */
    public function sendReturnConfirmation( string $user_email, string $user_name,int $user_id, string $asset_name, string $return_date, int $days_borrowed, float $penalty_amount, int $overdue_days) {
        error_log("📧 [EMAIL SERVICE] Sending return confirmation to: $user_email");
        
        try {
            if (!method_exists($this->email_notif, 'sendReturnConfirmation')) {
                error_log("❌ [EMAIL SERVICE] Method 'sendReturnConfirmation' not found");
                return false;
            }
            
            $result = $this->email_notif->sendReturnConfirmation(
                $user_email,
                $user_name,
                $user_id,
                $asset_name,
                $return_date,
                $days_borrowed,
                $penalty_amount,
                $overdue_days
            );
            
            if ($result) {
                error_log("✅ [EMAIL SERVICE] Return confirmation sent successfully");
            } else {
                error_log("⚠️ [EMAIL SERVICE] Return confirmation failed to send");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [EMAIL SERVICE] Exception in sendReturnConfirmation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send penalty notification email
     * Called when an asset is returned overdue
     */
    public function sendPenaltyNotification(string $user_email, string $user_name, int $user_id, string $asset_name, float $penalty_amount, int $overdue_days, string $reason) {
        error_log("⚠️ [EMAIL SERVICE] Sending penalty notification to: $user_email");
        
        try {
            if (!method_exists($this->email_notif, 'sendPenaltyNotification')) {
                error_log("❌ [EMAIL SERVICE] Method 'sendPenaltyNotification' not found");
                return false;
            }
            
            $result = $this->email_notif->sendPenaltyNotification(
                $user_email,
                $user_name,
                $user_id,
                $asset_name,
                $penalty_amount,
                $overdue_days,
                $reason
            );
            
            if ($result) {
                error_log("✅ [EMAIL SERVICE] Penalty notification sent successfully");
            } else {
                error_log("⚠️ [EMAIL SERVICE] Penalty notification failed to send");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [EMAIL SERVICE] Exception in sendPenaltyNotification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send overdue notification email
     * Called when an asset becomes overdue
     */
    public function sendOverdueNotification( string $user_email, string $user_name, int $user_id,string  $asset_name,string  $expected_return_date, int $overdue_days, float $penalty_amount) {
        error_log("🚨 [EMAIL SERVICE] Sending overdue notification to: $user_email");
        
        try {
            if (!method_exists($this->email_notif, 'sendOverdueNotification')) {
                error_log("❌ [EMAIL SERVICE] Method 'sendOverdueNotification' not found");
                return false;
            }
            
            $result = $this->email_notif->sendOverdueNotification(
                $user_email,
                $user_name,
                $user_id,
                $asset_name,
                $expected_return_date,
                $overdue_days,
                $penalty_amount
            );
            
            if ($result) {
                error_log("✅ [EMAIL SERVICE] Overdue notification sent successfully");
            } else {
                error_log("⚠️ [EMAIL SERVICE] Overdue notification failed to send");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ [EMAIL SERVICE] Exception in sendOverdueNotification: " . $e->getMessage());
            return false;
        }
    }
}

?>