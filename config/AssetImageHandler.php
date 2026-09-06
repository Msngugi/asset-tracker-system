<?php

class AssetImageHandler {
    
    private string $upload_dir;
    private array $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private int $max_file_size = 5242880; // 5MB
    private int $max_width = 800;
    private int $max_height = 600;
    
    public function __construct() {
        // Use document root for absolute server path
        $this->upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/assets/';
        
        // Create directory if it doesn't exist
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * Upload asset image
     * 
     * @param array $file - $_FILES['file']
     * @param int $asset_id - Asset ID
     * @return array - ['success' => true/false, 'filename' => filename, 'path' => path, 'message' => message]
     */
    public function uploadImage(array $file, int $asset_id): array {
        // ============ VALIDATE FILE EXISTS ============
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $error_msg = isset($errors[$file['error']]) ? $errors[$file['error']] : 'Unknown upload error';
            
            return [
                'success' => false,
                'message' => $error_msg
            ];
        }
        
        // ============ VALIDATE FILE SIZE ============
        if ($file['size'] > $this->max_file_size) {
            return [
                'success' => false,
                'message' => 'File size exceeds maximum of 5MB. Your file is ' . number_format($file['size'] / 1024 / 1024, 2) . 'MB'
            ];
        }
        
        // ============ VALIDATE FILE EXTENSION ============
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_extensions)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowed_extensions)
            ];
        }
        
        // ============ VALIDATE MIME TYPE ============
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed_mimes)) {
            return [
                'success' => false,
                'message' => 'File is not a valid image. Detected MIME: ' . $mime
            ];
        }
        
        // ============ CREATE UNIQUE FILENAME ============
        // Use only asset_id and extension - no duplicate timestamps
        $filename = 'asset_' . $asset_id . '.' . $ext;
        $filepath = $this->upload_dir . $filename;
        
        // If file already exists, delete it (for updates)
        if (file_exists($filepath)) {
            unlink($filepath);
            error_log("Deleted existing image: " . $filepath);
        }
        
        // ============ MOVE UPLOADED FILE ============
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log("Failed to move uploaded file from " . $file['tmp_name'] . " to " . $filepath);
            return [
                'success' => false,
                'message' => 'Failed to save image file to server'
            ];
        }
        
        error_log("Image uploaded successfully: " . $filepath);
        
        // ============ OPTIMIZE IMAGE ============
        $this->optimizeImage($filepath);
        
        // Return WEB PATH (relative to document root) for serving via HTTP
        $web_path = '/uploads/assets/' . $filename;
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $web_path,  // This is the WEB PATH for displaying in HTML
            'full_path' => $filepath,  // This is the SERVER PATH for file operations
            'message' => 'Image uploaded and optimized successfully'
        ];
    }
    
    /**
     * Resize and optimize image
     * Reduces file size while maintaining quality
     */
    private function optimizeImage(string $filepath): void {
        try {
            // Check if GD library is available
            if (!extension_loaded('gd')) {
                error_log('GD library not available for image optimization');
                return;
            }
            
            // Create image from file
            $image = @imagecreatefromstring(file_get_contents($filepath));
            
            if (!$image) {
                error_log('Could not create image from file: ' . $filepath);
                return;
            }
            
            // Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Calculate new dimensions (max 800x600)
            if ($width > $this->max_width || $height > $this->max_height) {
                $ratio = min($this->max_width / $width, $this->max_height / $height);
                $new_width = (int)($width * $ratio);
                $new_height = (int)($height * $ratio);
                
                // Create new resized image
                $resized = imagecreatetruecolor($new_width, $new_height);
                
                // Preserve transparency for PNG and GIF
                if (imagealphablending($resized, false)) {
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                    imagefill($resized, 0, 0, $transparent);
                }
                
                // Resize image
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                // Determine output format
                $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
                
                if ($ext === 'png') {
                    imagepng($resized, $filepath, 8); // PNG quality 8
                } elseif ($ext === 'gif') {
                    imagegif($resized, $filepath);
                } else {
                    imagejpeg($resized, $filepath, 85); // JPEG quality 85
                }
                
                imagedestroy($resized);
                error_log("Image optimized: " . $filepath);
            }
            
            imagedestroy($image);
        } catch (Exception $e) {
            error_log("Image optimization error: " . $e->getMessage());
            // If optimization fails, keep original
        }
    }
    
    /**
     * Update asset image - delete old and upload new
     */
    public function updateImage(array $file, int $asset_id, ?string $old_image_path = null): array {
        // Delete old image if exists
        if ($old_image_path && !empty($old_image_path)) {
            $this->deleteImage($old_image_path);
        }
        
        // Upload new image
        return $this->uploadImage($file, $asset_id);
    }
    
    /**
     * Delete asset image
     */
    public function deleteImage(string $path): array {
        if (empty($path)) {
            return ['success' => false, 'message' => 'No path provided'];
        }
        
        // Convert web path to server path
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $path;
        
        if (file_exists($full_path)) {
            if (unlink($full_path)) {
                error_log("Image deleted: " . $full_path);
                return ['success' => true, 'message' => 'Image deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Could not delete image file'];
            }
        }
        
        error_log("Image file not found at: " . $full_path);
        return ['success' => false, 'message' => 'Image file not found'];
    }
    
    /**
     * Get image with fallback to placeholder
     */
    public function getImageHTML(string $path, string $alt = 'Asset Image', string $size = '200px'): string {
        if (!empty($path)) {
            // Check if file exists using the web path
            $full_path = $_SERVER['DOCUMENT_ROOT'] . $path;
            
            if (file_exists($full_path)) {
                error_log("Image found at: " . $full_path);
                return '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($alt) . '" style="width: ' . $size . '; height: ' . $size . '; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">';
            }
            
            error_log("Image not found at: " . $full_path);
        }
        
        // Fallback placeholder
        return '<div style="width: ' . $size . '; height: ' . $size . '; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #9ca3af; border: 1px dashed #e5e7eb;"><i class="bx bx-package"></i></div>';
    }
    
    /**
     * Validate image without uploading
     */
    public function validateImage(array $file): array {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'No file provided or upload error'];
        }
        
        if ($file['size'] > $this->max_file_size) {
            return ['valid' => false, 'message' => 'File too large'];
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_extensions)) {
            return ['valid' => false, 'message' => 'Invalid file type'];
        }
        
        return ['valid' => true, 'message' => 'File is valid'];
    }
    
    /**
     * Get upload directory path
     */
    public function getUploadDir(): string {
        return $this->upload_dir;
    }
    
    /**
     * Get allowed file extensions
     */
    public function getAllowedExtensions(): array {
        return $this->allowed_extensions;
    }
    
    /**
     * Get max file size in MB
     */
    public function getMaxFileSize(): float {
        return round($this->max_file_size / 1024 / 1024, 2);
    }
}
?>
