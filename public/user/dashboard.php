<?php
session_start(); 

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

require '../../config/db_connect.php';

// ============ GET USER DATA ============

$user_id = $_SESSION['user_id'];

// Get total borrowed assets 
$borrowed_query = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM borrow_asset
    WHERE user_id = ? AND status = 'in-use'
");
$borrowed_query->bind_param("i", $user_id);
$borrowed_query->execute();
$borrowed_data = $borrowed_query->get_result()->fetch_assoc();
$total_borrowed = $borrowed_data['total'];
$borrowed_query->close();

// Get available assets to borrow
$available_query = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM assets 
    WHERE status = 'available'
");
$available_query->execute();
$available_data = $available_query->get_result()->fetch_assoc();
$total_available = $available_data['total'];
$available_query->close();

// Get overdue borrows 
$overdue_query = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM borrow_asset
    WHERE user_id = ? AND status = 'in-use' AND expected_return_date < CURDATE()
");
$overdue_query->bind_param("i", $user_id);
$overdue_query->execute();
$overdue_data = $overdue_query->get_result()->fetch_assoc();
$total_overdue = $overdue_data['total'];
$overdue_query->close();

// Get pending penalties 
$penalties_query = $conn->prepare("
    SELECT COUNT(*) as total, SUM(penalty_amount) as penalty_amount
    FROM penalties 
    WHERE user_id = ? AND status = 'pending'
");
$penalties_query->bind_param("i", $user_id);
$penalties_query->execute();
$penalties_data = $penalties_query->get_result()->fetch_assoc();
$total_penalties = $penalties_data['total'];
$pending_amount = $penalties_data['penalty_amount'] ?? 0;
$penalties_query->close();


$current_borrows_query = $conn->prepare("
    SELECT 
        b.borrow_id,
        b.asset_id,
        b.borrow_date,
        b.expected_return_date,
        a.asset_name, 
        a.asset_model, 
        ac.category_name, 
        ai.image_path,
        DATEDIFF(b.expected_return_date, CURDATE()) as days_until_due
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    WHERE b.user_id = ? AND b.status = 'in-use'
    ORDER BY b.expected_return_date ASC
    LIMIT 6
");
$current_borrows_query->bind_param("i", $user_id);
$current_borrows_query->execute();
$current_borrows_result = $current_borrows_query->get_result();
$current_borrows_query->close();

// Get recent returns
$recent_returns_query = $conn->prepare("
    SELECT ra.*, a.asset_name, a.asset_model, ac.category_name
    FROM return_asset ra
    JOIN assets a ON ra.asset_id = a.asset_id
    JOIN asset_categories ac ON a.category_id = ac.category_id
    WHERE ra.user_id = ?
    ORDER BY ra.return_date DESC
    LIMIT 5
");
$recent_returns_query->bind_param("i", $user_id);
$recent_returns_query->execute();
$recent_returns_result = $recent_returns_query->get_result();
$recent_returns_query->close();

// ============ LOAD FRONTEND ============
require 'dashboard_page.php';
?>
