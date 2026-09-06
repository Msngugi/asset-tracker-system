<?php
/**
 * Debug script to check file uploads and permissions
 * Access: http://localhost/asset_tracker_system/public/check_uploads.php
 */

echo "<h2>Upload Directories Check</h2>";
echo "<hr>";

$directories = [
    'Assets Images' => $_SERVER['DOCUMENT_ROOT'] . '/uploads/assets/',
    'QR Codes' => $_SERVER['DOCUMENT_ROOT'] . '/uploads/qr_codes/',
];

foreach ($directories as $name => $path) {
    echo "<h3>$name</h3>";
    echo "<p><strong>Path:</strong> $path</p>";
    
    if (is_dir($path)) {
        echo "<p style='color: green;'><strong>✓ Directory exists</strong></p>";
        
        // Check permissions
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "<p><strong>Permissions:</strong> $perms</p>";
        
        // List files
        $files = scandir($path);
        $files = array_diff($files, ['.', '..']);
        
        if (count($files) > 0) {
            echo "<p><strong>Files:</strong></p>";
            echo "<ul>";
            foreach ($files as $file) {
                $file_path = $path . $file;
                $size = filesize($file_path);
                echo "<li>$file (" . number_format($size) . " bytes)</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>No files found</p>";
        }
    } else {
        echo "<p style='color: red;'><strong>✗ Directory does NOT exist</strong></p>";
        echo "<p>Creating directory...</p>";
        if (@mkdir($path, 0755, true)) {
            echo "<p style='color: green;'>✓ Directory created</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create directory</p>";
        }
    }
    
    echo "<hr>";
}
?>