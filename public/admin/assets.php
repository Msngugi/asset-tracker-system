<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../config/db_connect.php';

// ================= FILTERS =================
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$query = "
    SELECT a.*, ac.category_name, u.user_name, u.department_name,
           (SELECT image_path FROM asset_images WHERE asset_id = a.asset_id LIMIT 1) as image_path
    FROM assets a
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN users u ON a.assigned_to = u.user_id
    WHERE 1=1
";

$params = [];
$types = "";

if (!empty($category_filter)) {
    $query .= " AND a.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

if (!empty($status_filter)) {
    $query .= " AND a.status = ?";
    // ✅ Status is now lowercase in database
    $params[] = strtolower($status_filter);
    $types .= "s";
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$assets_result = $stmt->get_result();
$stmt->close();

// ================= CATEGORIES =================
$cat_result = $conn->query("SELECT * FROM asset_categories ORDER BY category_name");
$categories = $cat_result->fetch_all(MYSQLI_ASSOC);

// ================= USERS =================
$users_result = $conn->query("SELECT user_id, user_name, department_name FROM users WHERE role = 'user'");
$users_array = $users_result->fetch_all(MYSQLI_ASSOC);

// ================= STATS =================
function getCount($conn, $status = null) {
    if ($status) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM assets WHERE status = ?");
        // ✅ Lowercase status for database query
        $status_lower = strtolower($status);
        $stmt->bind_param("s", $status_lower);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM assets");
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return $result;
}

$total_assets = getCount($conn);
$available_assets = getCount($conn, 'available');
$in_use_assets = getCount($conn, 'in-use');
$damaged_assets = getCount($conn, 'damaged');
$under_maintenance_assets = getCount($conn, 'under-maintenance');

// ================= LOAD VIEW =================
require 'assets.page.php';
?>