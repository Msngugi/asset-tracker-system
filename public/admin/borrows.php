<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}
require '../../config/db_connect.php';

// ============ GET ALL DATA ============

$status_filter = $_GET['status'] ?? 'all';
$where_clause = "";

if ($status_filter === 'active') {
    $where_clause = " WHERE b.status = 'in-use'";
} elseif ($status_filter === 'overdue') {
    $where_clause = " WHERE b.status = 'in-use' AND b.expected_return_date < CURDATE()";
} elseif ($status_filter === 'returned') {
    $where_clause = " WHERE b.status = 'returned'";
}

$borrows_query = $conn->prepare("
    SELECT b.*, a.asset_name, a.asset_model, u.user_name, u.email, u.department_name, ac.category_name, ai.image_path,
    DATEDIFF(b.expected_return_date, CURDATE()) as days_until_due
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN users u ON b.user_id = u.user_id
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    $where_clause
    ORDER BY b.expected_return_date ASC
");

$borrows_query->execute();
$borrows_result = $borrows_query->get_result();
$borrows_query->close();

// Get statistics
$active_query = $conn->prepare("SELECT COUNT(*) as count FROM borrow_asset WHERE status = 'in-use'");
$active_query->execute();
$active_data = $active_query->get_result()->fetch_assoc();
$active_count = $active_data['count'];
$active_query->close();

$returned_query = $conn->prepare("SELECT COUNT(*) as count FROM borrow_asset WHERE status = 'returned'");
$returned_query->execute();
$returned_data = $returned_query->get_result()->fetch_assoc();
$returned_count = $returned_data['count'];
$returned_query->close();

$overdue_query = $conn->prepare("SELECT COUNT(*) as count FROM borrow_asset WHERE status = 'in-use' AND expected_return_date < CURDATE()");
$overdue_query->execute();
$overdue_data = $overdue_query->get_result()->fetch_assoc();
$overdue_count = $overdue_data['count'];
$overdue_query->close();

// ============ LOAD FRONTEND ============
require 'borrows.page.php';
?>