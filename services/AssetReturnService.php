<?php

/**
 * Asset Return Service
 * Handles ALL return logic regardless of who initiated it (admin/user)
 * NO EMAIL LOGIC HERE - uses EmailService
 */
class AssetReturnService {
    
    private mysqli$conn;
    
    public function __construct(mysqli $database_connection) {
        $this->conn = $database_connection;
    }
    
    /**
     * Process return request
     * @param int $borrow_id
     * @param string $qr_code
     * @param string $condition
     * @param string $notes
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function processReturn($borrow_id, $qr_code, $condition, $notes) {
        
        error_log("📦 [RETURN SERVICE] Processing return - borrow_id: $borrow_id");
        
        // ============ VALIDATION ============
        if ($borrow_id <= 0 || empty($qr_code)) {
            error_log("⚠️ [RETURN SERVICE] Invalid input");
            return [
                'success' => false,
                'message' => 'Invalid input data',
                'data' => null
            ];
        }
        
        // ============ GET BORROW DETAILS ============
        $borrow = $this->getBorrowDetails($borrow_id);
        if (!$borrow) {
            error_log("⚠️ [RETURN SERVICE] Borrow record not found");
            return [
                'success' => false,
                'message' => 'Borrow record not found or already returned',
                'data' => null
            ];
        }
        
        // ============ VERIFY QR CODE ============
        if ($qr_code !== $borrow['qr_code']) {
            error_log("⚠️ [RETURN SERVICE] QR code mismatch");
            return [
                'success' => false,
                'message' => 'QR code does not match',
                'data' => null
            ];
        }
        
        // ============ CALCULATE PENALTY ============
        $return_date = date('Y-m-d');
        $penalty_data = $this->calculatePenalty($borrow['borrow_date'], $borrow['expected_return_date'], $return_date);
        
        // ============ UPDATE BORROW RECORD ============
        $return_stmt = $this->conn->prepare("
            UPDATE borrow_asset 
            SET return_date = ?, 
                status = 'returned', 
                asset_condition_on_return = ?, 
                return_notes = ?,
                penalty = ?
            WHERE borrow_id = ?
        ");
        
        if (!$return_stmt) {
            error_log("❌ [RETURN SERVICE] Return update prepare failed: " . $this->conn->error);
            return [
                'success' => false,
                'message' => 'Database error',
                'data' => null
            ];
        }
        
        $penalty_float = floatval($penalty_data['penalty_amount']);
        $return_stmt->bind_param(
            "sssdi",
            $return_date,
            $condition,
            $notes,
            $penalty_float,
            $borrow_id
        );
        
        if (!$return_stmt->execute()) {
            error_log("❌ [RETURN SERVICE] Return update execute failed: " . $return_stmt->error);
            return [
                'success' => false,
                'message' => 'Failed to process return',
                'data' => null
            ];
        }
        
        $return_stmt->close();
        error_log("✅ [RETURN SERVICE] Return record updated - borrow_id: $borrow_id");
        
        // ============ UPDATE ASSET STATUS ============
        $update_asset = $this->conn->prepare("UPDATE assets SET status = 'available' WHERE asset_id = ?");
        if ($update_asset) {
            $update_asset->bind_param("i", $borrow['asset_id']);
            $update_asset->execute();
            $update_asset->close();
        }
        
        // ============ PREPARE RESPONSE DATA ============
        $response_data = [
            'borrow_id' => $borrow_id,
            'asset_name' => $borrow['asset_name'],
            'return_date' => $return_date,
            'days_borrowed' => $penalty_data['days_borrowed'],
            'overdue_days' => $penalty_data['overdue_days'],
            'penalty_amount' => $penalty_data['penalty_amount']
        ];
        
        return [
            'success' => true,
            'message' => 'Asset returned successfully',
            'data' => $response_data,
            'user_id' => $borrow['user_id'],           // For email service
            'user_email' => $borrow['email'],           // For email service
            'user_name' => $borrow['user_name'],        // For email service
            'penalty_data' => $penalty_data             // For email service
        ];
    }
    
    /**
     * Get borrow details with user and asset info
     */
    private function getBorrowDetails($borrow_id) {
        $query = $this->conn->prepare("
            SELECT b.borrow_id, b.user_id, b.asset_id, b.borrow_date, 
                   b.expected_return_date, a.asset_name, a.qr_code,
                   u.email, u.user_name
            FROM borrow_asset b
            JOIN assets a ON b.asset_id = a.asset_id
            JOIN users u ON b.user_id = u.user_id
            WHERE b.borrow_id = ? AND b.status = 'in_use'
        ");
        
        if (!$query) {
            return null;
        }
        
        $query->bind_param("i", $borrow_id);
        $query->execute();
        $result = $query->get_result();
        $borrow = $result->num_rows > 0 ? $result->fetch_assoc() : null;
        $query->close();
        
        return $borrow;
    }
    
    /**
     * Calculate penalty and days borrowed
     */
    private function calculatePenalty($borrow_date, $expected_return_date, $actual_return_date) {
        $expected = new DateTime($expected_return_date);
        $actual = new DateTime($actual_return_date);
        $start = new DateTime($borrow_date);
        
        $days_borrowed = $actual->diff($start)->days;
        $penalty_amount = 0;
        $overdue_days = 0;
        
        if ($actual > $expected) {
            $overdue_days = $actual->diff($expected)->days;
            $penalty_amount = $overdue_days * (defined('PENALTY_PER_DAY') ? PENALTY_PER_DAY : 0);
        }
        
        return [
            'days_borrowed' => $days_borrowed,
            'overdue_days' => $overdue_days,
            'penalty_amount' => $penalty_amount
        ];
    }
}

?>