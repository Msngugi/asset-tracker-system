<?php

/**
 * Asset Borrow Service
 * Handles ALL borrow logic
 */

class AssetBorrowService {

    private mysqli$conn;

    public function __construct(mysqli $database_connection) {
        $this->conn = $database_connection;
    }

    /**
     * Process Borrow Request
     */
    public function processBorrow(
        int $asset_id,
        int $user_id,
       string  $qr_code,
        int $borrow_days,
        string $condition,
        string $remarks = null
    ) {

        error_log("📦 [BORROW SERVICE] Starting borrow process");

        // =========================================
        // VALIDATION
        // =========================================

        if (
            empty($asset_id) ||
            empty($user_id) ||
            empty($borrow_days)
        ) {

            return [
                'success' => false,
                'message' => 'Invalid input data'
            ];
        }

        // =========================================
        // GET ASSET
        // =========================================

        $asset = $this->getAsset($asset_id);

        if (!$asset) {

            error_log("❌ Asset unavailable");

            return [
                'success' => false,
                'message' => 'Asset not found or not available'
            ];
        }

        // =========================================
        // GET USER
        // =========================================

        $user = $this->getUser($user_id);

        if (!$user) {

            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // =========================================
        // DATES
        // =========================================

        $borrow_date = date('Y-m-d');
        $borrow_time = date('H:i:s');

        $expected_return_date = date(
            'Y-m-d',
            strtotime("+{$borrow_days} days")
        );

        // =========================================
        // TRANSACTION START
        // =========================================

        $this->conn->begin_transaction();

        try {

            // =========================================
            // CREATE BORROW RECORD
            // =========================================

            $insert = $this->conn->prepare("
                INSERT INTO borrow_asset (
                    asset_id,
                    user_id,
                    borrow_date,
                    borrow_time,
                    expected_return_date,
                    asset_condition_on_borrow,
                    qr_code_scanned,
                    status,
                    reminder_sent
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, ?, 'in-use', 0
                )
            ");

            if (!$insert) {
                throw new Exception(
                    "Prepare failed: " . $this->conn->error
                );
            }

            $insert->bind_param(
                "iisssss",
                $asset_id,
                $user_id,
                $borrow_date,
                $borrow_time,
                $expected_return_date,
                $condition,
                $qr_code
            );

            if (!$insert->execute()) {

                throw new Exception(
                    "Borrow insert failed: " . $insert->error
                );
            }

            $borrow_id = $this->conn->insert_id;

            $insert->close();

            error_log("✅ Borrow created: ID {$borrow_id}");

            // =========================================
            // UPDATE ASSET STATUS
            // =========================================

            $update = $this->conn->prepare("
                UPDATE assets
                SET status = 'in-use', assigned_to = ?
                WHERE asset_id = ?
            ");

            if (!$update) {
                throw new Exception(
                    "Asset update prepare failed"
                );
            }

            $update->bind_param("ii", $user_id, $asset_id);

            if (!$update->execute()) {

                throw new Exception(
                    "Asset update failed: " . $update->error
                );
            }

            $update->close();

            // =========================================
            // COMMIT
            // =========================================

            $this->conn->commit();

            // =========================================
            // RESPONSE
            // =========================================

            return [
                'success' => true,
                'message' => 'Asset borrowed successfully',

                'borrow_id' => $borrow_id,

                'user_email' => $user['email'],

                'user_name' => $user['user_name'],

                'asset_data' => $asset,

                'borrow_date' => $borrow_date,

                'expected_return_date' => $expected_return_date,

                'borrow_days' => $borrow_days,

                'data' => [
                    'borrow_id' => $borrow_id,
                    'asset_name' => $asset['asset_name'],
                    'asset_code' => $asset['asset_code'],
                    'user_name' => $user['user_name']
                ]
            ];

        } catch (Exception $e) {

            $this->conn->rollback();

            error_log("❌ BORROW ERROR: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Available Asset
     */
    private function getAsset(int $asset_id) {

        $query = $this->conn->prepare("
            SELECT
                asset_id,
                asset_name,
                asset_code,
                asset_model,
                serial_number,
                status
            FROM assets
            WHERE asset_id = ?
            AND LOWER(status) = 'available'
            LIMIT 1
        ");

        if (!$query) {
            return null;
        }

        $query->bind_param("i", $asset_id);

        $query->execute();

        $result = $query->get_result();

        $asset = $result->num_rows > 0
            ? $result->fetch_assoc()
            : null;

        $query->close();

        return $asset;
    }

    /**
     * Get User
     */
    private function getUser(int $user_id) {

        $query = $this->conn->prepare("
            SELECT
                user_id,
                user_name,
                email
            FROM users
            WHERE user_id = ?
            LIMIT 1
        ");

        if (!$query) {
            return null;
        }

        $query->bind_param("i", $user_id);

        $query->execute();

        $result = $query->get_result();

        $user = $result->num_rows > 0
            ? $result->fetch_assoc()
            : null;

        $query->close();

        return $user;
    }
}

?>
