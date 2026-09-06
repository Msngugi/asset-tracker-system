<?php
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../config/db_connect.php';

// ============ GET ALL DATA ============

// Total users
$users_query = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$users_query->execute();
$users_result = $users_query->get_result();
$users_data = $users_result->fetch_assoc();
$total_users = $users_data['total_users'];
$users_query->close();

// Total assets
$assets_query = $conn->prepare("SELECT COUNT(*) as total_assets FROM assets");
$assets_query->execute();
$assets_result = $assets_query->get_result();
$assets_data = $assets_result->fetch_assoc();
$total_assets = $assets_data['total_assets'];
$assets_query->close();

// Assets currently borrowed
$borrowed_query = $conn->prepare("SELECT COUNT(*) as borrowed FROM borrow_asset WHERE status = 'in-use'");
$borrowed_query->execute();
$borrowed_result = $borrowed_query->get_result();
$borrowed_data = $borrowed_result->fetch_assoc();
$borrowed_assets = $borrowed_data['borrowed'];
$borrowed_query->close();

// Available assets
$available_query = $conn->prepare("SELECT COUNT(*) as available FROM assets WHERE status = 'available'");
$available_query->execute();
$available_result = $available_query->get_result();
$available_data = $available_result->fetch_assoc();
$available_assets = $available_data['available'];
$available_query->close();

// Overdue borrows
$overdue_query = $conn->prepare("
    SELECT COUNT(*) as overdue 
    FROM borrow_asset
    WHERE status = 'in-use' AND expected_return_date < CURDATE()
");
$overdue_query->execute();
$overdue_result = $overdue_query->get_result();
$overdue_data = $overdue_result->fetch_assoc();
$overdue_assets = $overdue_data['overdue'];
$overdue_query->close();

// Pending penalties
$penalties_query = $conn->prepare("
    SELECT COUNT(*) as pending_penalties, SUM(penalty_amount) as pending_amount
    FROM penalties
    WHERE status = 'pending'
");
$penalties_query->execute();
$penalties_result = $penalties_query->get_result();
$penalties_data = $penalties_result->fetch_assoc();
$pending_penalties = $penalties_data['pending_penalties'];
$pending_amount = $penalties_data['pending_amount'] ?? 0;
$penalties_query->close();

// Get assets by category
$category_query = $conn->prepare("
    SELECT ac.category_name, COUNT(a.asset_id) as count 
    FROM assets a
    JOIN asset_categories ac ON a.category_id = ac.category_id
    GROUP BY a.category_id, ac.category_name
    ORDER BY count DESC
");
$category_query->execute();
$category_result = $category_query->get_result();
$category_data = [];
while ($row = $category_result->fetch_assoc()) {
    $category_data[] = $row;
}
$category_query->close();

// Get assets by condition
$condition_query = $conn->prepare("
    SELECT asset_condition, COUNT(*) as count 
    FROM assets 
    GROUP BY asset_condition
");
$condition_query->execute();
$condition_result = $condition_query->get_result();
$condition_data = [];
while ($row = $condition_result->fetch_assoc()) {
    $condition_data[] = $row;
}
$condition_query->close();

// Get recent borrow/return activities
$activity_query = $conn->prepare("
    SELECT 'Borrow' as activity_type, b.borrow_id as record_id, b.borrow_date as activity_date, b.borrow_time as activity_time, 
           a.asset_name, u.user_name, b.status as activity_status
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN users u ON b.user_id = u.user_id
    UNION ALL
    SELECT 'Return' as activity_type, r.return_id as record_id, r.return_date as activity_date, r.return_time as activity_time,
           a.asset_name, u.user_name, (CASE WHEN r.is_overdue THEN 'Overdue' ELSE 'On-Time' END) as activity_status
    FROM return_asset r
    JOIN assets a ON r.asset_id = a.asset_id
    JOIN users u ON r.user_id = u.user_id
    ORDER BY activity_date DESC
    LIMIT 10
");
$activity_query->execute();
$activity_result = $activity_query->get_result();
$activity_query->close();

// Get recent borrowed assets
$recent_borrow_query = $conn->prepare("
    SELECT b.*, a.asset_name, a.asset_model , u.user_name, ac.category_name, ai.image_path,
    DATEDIFF(b.expected_return_date, CURDATE()) as days_until_due
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN users u ON b.user_id = u.user_id
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    WHERE b.status = 'in-use'
    ORDER BY b.expected_return_date ASC
    LIMIT 8
");
$recent_borrow_query->execute();
$recent_borrow_result = $recent_borrow_query->get_result();
$recent_borrow_query->close();

// ============ LOAD FRONTEND ============
require 'dashboard.page.php';
?>