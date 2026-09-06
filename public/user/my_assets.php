<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

require '../../config/db_connect.php';

// ============ GET USER DATA ============

$user_id = $_SESSION['user_id'];

// Get filter
$status_filter = $_GET['status'] ?? 'all';
$where_clause = " WHERE b.user_id = ?";

if ($status_filter === 'active') {
    
    $where_clause .= " AND b.status = 'in-use'";
} elseif ($status_filter === 'overdue') {

    $where_clause .= " AND b.status = 'in-use' AND b.expected_return_date < CURDATE()";
} elseif ($status_filter === 'returned') {
    $where_clause .= " AND b.status = 'returned'";
}

$assets_query = $conn->prepare("
    SELECT
        b.borrow_id,
        b.asset_id,
        b.user_id,
        b.borrow_date,
        b.expected_return_date,
        b.asset_condition_on_borrow,
        b.status,
        b.created_at,
        b.updated_at,
        a.asset_name,
        a.asset_model,
        a.serial_number,
        ac.category_name,
        ai.image_path,
        q.qr_code,
        q.image_path as qr_image_path,
        CASE 
            WHEN b.expected_return_date IS NULL OR b.expected_return_date = '0000-00-00' THEN 999999
            ELSE DATEDIFF(b.expected_return_date, CURDATE())
        END as days_until_due,
        CASE 
            WHEN b.status = 'returned' THEN 'Returned'
            WHEN b.status = 'in-use' AND (b.expected_return_date IS NULL OR b.expected_return_date = '0000-00-00') THEN 'Active'
            WHEN b.status = 'in-use' AND b.expected_return_date < CURDATE() THEN 'Overdue'
            WHEN b.status = 'in-use' AND DATEDIFF(b.expected_return_date, CURDATE()) <= 2 THEN 'Due Soon'
            WHEN b.status = 'in-use' THEN 'Active'
            ELSE b.status
        END as status_display
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    LEFT JOIN qr_codes q ON a.asset_id = q.asset_id
    $where_clause
    ORDER BY b.expected_return_date ASC
");

if (!$assets_query) {
    die("Query Error: " . $conn->error);
}

$assets_query->bind_param("i", $user_id);
$assets_query->execute();
$assets_result = $assets_query->get_result();
$assets_query->close();

// Get statistics

$active_query = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM borrow_asset 
    WHERE user_id = ? AND status = 'in-use'
");
$active_query->bind_param("i", $user_id);
$active_query->execute();
$active_data = $active_query->get_result()->fetch_assoc();
$active_count = $active_data['count'];
$active_query->close();


$returned_query = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM borrow_asset 
    WHERE user_id = ? AND status = 'returned'
");
$returned_query->bind_param("i", $user_id);
$returned_query->execute();
$returned_data = $returned_query->get_result()->fetch_assoc();
$returned_count = $returned_data['count'];
$returned_query->close();


$overdue_query = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM borrow_asset 
    WHERE user_id = ? AND status = 'in-use' AND expected_return_date IS NOT NULL 
    AND expected_return_date != '0000-00-00' AND expected_return_date < CURDATE()
");
$overdue_query->bind_param("i", $user_id);
$overdue_query->execute();
$overdue_data = $overdue_query->get_result()->fetch_assoc();
$overdue_count = $overdue_data['count'];
$overdue_query->close();

// ============ LOAD FRONTEND ============
require 'my_assets_page.php';
?>