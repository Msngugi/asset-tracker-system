<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../config/db_connect.php';

// ============ GET REPORT FILTERS ============
$report_type = $_GET['type'] ?? 'summary';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// ============ ASSET SUMMARY REPORT ============
$asset_summary = [];
$stmt = $conn->prepare("
    SELECT 
        status, 
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM assets), 2) as percentage
    FROM assets
    GROUP BY status
");
$stmt->execute();
$asset_summary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ============ ASSETS BY CATEGORY REPORT ============
$assets_by_category = [];
$stmt = $conn->prepare("
    SELECT 
        ac.category_name,
        COUNT(a.asset_id) as total,
        SUM(CASE WHEN a.status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN a.status = 'in-use' THEN 1 ELSE 0 END) as in_use,
        SUM(CASE WHEN a.status = 'damaged' THEN 1 ELSE 0 END) as damaged
    FROM asset_categories ac
    LEFT JOIN assets a ON ac.category_id = a.category_id
    GROUP BY ac.category_id, ac.category_name
");
$stmt->execute();
$assets_by_category = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ============ USER ACTIVITY REPORT ============
$user_activity = [];
$stmt = $conn->prepare("
    SELECT 
        u.user_name,
        u.department_name,
        COUNT(al.log_id) as total_actions,
        MAX(al.log_time) as last_action
    FROM users u
    LEFT JOIN asset_logs al ON u.user_id = al.user_id
    WHERE u.role = 'user'
    GROUP BY u.user_id, u.user_name, u.department_name
    ORDER BY total_actions DESC
");
$stmt->execute();
$user_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ============ ASSET LOGS REPORT ============
$asset_logs = [];
$stmt = $conn->prepare("
    SELECT 
        al.log_id,
        al.action,
        al.description,
        a.asset_name,
        u.user_name,
        al.log_time
    FROM asset_logs al
    LEFT JOIN assets a ON al.asset_id = a.asset_id
    LEFT JOIN users u ON al.user_id = u.user_id
    WHERE DATE(al.log_time) BETWEEN ? AND ?
    ORDER BY al.log_time DESC
    LIMIT 100
");
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$asset_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ============ ASSET VALUE REPORT ============
$asset_value = [];
$stmt = $conn->prepare("
    SELECT 
        ac.category_name,
        COUNT(a.asset_id) as total_assets,
        SUM(a.cost) as total_value,
        AVG(a.cost) as average_value,
        MAX(a.cost) as highest_value,
        MIN(a.cost) as lowest_value
    FROM asset_categories ac
    LEFT JOIN assets a ON ac.category_id = a.category_id
    WHERE a.cost IS NOT NULL
    GROUP BY ac.category_id, ac.category_name
");
$stmt->execute();
$asset_value = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ============ ASSETS BY CONDITION REPORT ============
$assets_by_condition = [];
$stmt = $conn->prepare("
    SELECT 
        asset_condition,
        COUNT(*) as count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM assets), 2) as percentage
    FROM assets
    GROUP BY asset_condition
");
$stmt->execute();
$assets_by_condition = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ============ LOAD FRONTEND ============
require 'reports.page.php';
?>
