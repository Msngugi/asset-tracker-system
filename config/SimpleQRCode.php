<?php

class SimpleQRCode {
    
    private  string $qr_codes_dir;
    private  string $phpqrcode_path;
    
    public function __construct() {
        // Use document root for absolute server path
        $this->qr_codes_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/qr_codes/';
        
        // Try multiple possible paths for phpqrcode
        $possible_paths = [
            $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpqrcode/',
            __DIR__ . '/../vendor/phpqrcode/',
            __DIR__ . '/../../vendor/phpqrcode/',
            __DIR__ . '/../../../vendor/phpqrcode/',
        ];
        
        $this->phpqrcode_path = null;
        foreach ($possible_paths as $path) {
            if (file_exists($path . 'qrlib.php')) {
                $this->phpqrcode_path = $path;
                error_log("PHPQRCode found at: " . $path);
                break;
            }
        }
        
        if (!$this->phpqrcode_path) {
            error_log("PHPQRCode library not found in any of the expected locations");
            // Log all checked paths
            foreach ($possible_paths as $path) {
                error_log("Checked: " . $path);
            }
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($this->qr_codes_dir)) {
            mkdir($this->qr_codes_dir, 0755, true);
            error_log("Created QR codes directory: " . $this->qr_codes_dir);
        }
    }
    
    /**
     * Generate QR code image using phpqrcode
     * 
     * @param int $asset_id - Asset ID
     * @param string $asset_name - Asset name
     * @return array - ['success' => bool, 'qr_code' => code, 'image_path' => path]
     */
    public function generateQRCode($asset_id, $asset_name) {
        try {
            // Check if phpqrcode library was found
            if (!$this->phpqrcode_path) {
                error_log("QR Code generation failed: PHPQRCode library not found");
                return [
                    'success' => false,
                    'message' => 'QR Code library not found. Please ensure phpqrcode is installed in vendor/phpqrcode/'
                ];
            }
            
            // Attempt to include phpqrcode library
            $qrlib_path = $this->phpqrcode_path . 'qrlib.php';
            
            if (!file_exists($qrlib_path)) {
                error_log("QR Code generation failed: qrlib.php not found at " . $qrlib_path);
                return [
                    'success' => false,
                    'message' => 'qrlib.php not found at ' . $qrlib_path
                ];
            }
            
            require_once $qrlib_path;
            error_log("QRCode library included successfully from: " . $qrlib_path);
            
            // Create unique QR code value
            $qr_code_value = 'ASSET_' . $asset_id . '_' . time();
            
            // Create filename
            $filename = 'qr_asset_' . $asset_id . '_' . time() . '.png';
            $filepath = $this->qr_codes_dir . $filename;
            $web_path = '/uploads/qr_codes/' . $filename;
            
            error_log("Generating QR code to: " . $filepath);
            
            // Generate QR code
            if (class_exists('QRcode')) {
                \QRcode::png($qr_code_value, $filepath, 'L', 4, 2);
                error_log("QRcode::png() called successfully");
            } else {
                error_log("QRcode class not found after requiring qrlib.php");
                return [
                    'success' => false,
                    'message' => 'QRcode class not available'
                ];
            }
            
            // Verify file was created
            if (!file_exists($filepath)) {
                error_log("QR code file not created at: " . $filepath);
                return [
                    'success' => false,
                    'message' => 'QR code file was not created at ' . $filepath
                ];
            }
            
            error_log("QR code generated successfully: " . $filepath . " (" . filesize($filepath) . " bytes)");
            
            return [
                'success' => true,
                'qr_code' => $qr_code_value,
                'filename' => $filename,
                'image_path' => $web_path,  // WEB PATH for serving
                'full_path' => $filepath,   // SERVER PATH for file operations
                'message' => 'QR code generated successfully'
            ];
            
        } catch (Exception $e) {
            error_log("QR Code generation exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Display QR code image as HTML
     */
    public function displayQRCode(string $image_path, string $size = '250px'): string {
        if (!empty($image_path)) {
            $full_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
            
            if (file_exists($full_path)) {
                error_log("QR code file found at: " . $full_path);
                return '
                    <div style="text-align: center;">
                        <img src="' . htmlspecialchars($image_path) . '" 
                             alt="Asset QR Code" 
                             style="width: ' . $size . '; height: ' . $size . '; border: 2px solid #ddd; padding: 10px; border-radius: 8px; background: white;">
                        <p style="font-size: 12px; color: #6b7280; margin-top: 10px;">Scan this code</p>
                    </div>
                ';
            }
            
            error_log("QR code file not found at: " . $full_path);
        }
        
        return '<p style="color: #dc2626;">QR Code not found</p>';
    }
    
    /**
     * Delete QR code file
     */
    public function deleteQRCode(string $image_path) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
        
        if (file_exists($full_path)) {
            if (unlink($full_path)) {
                error_log("QR code deleted: " . $full_path);
                return ['success' => true, 'message' => 'QR code deleted'];
            }
        }
        
        error_log("QR code file not found: " . $full_path);
        return ['success' => false, 'message' => 'QR code file not found'];
    }
}
?>