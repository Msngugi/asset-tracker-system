<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

require '../../config/db_connect.php';

$user_id = $_SESSION['user_id'];

// ============ GET PENALTY DATA ============

$status_filter = $_GET['status'] ?? 'all';
$where_clause = " WHERE p.user_id = ?";

if ($status_filter === 'pending') {
    // ✅ FIXED: Changed to lowercase 'pending'
    $where_clause .= " AND p.status = 'pending'";
} elseif ($status_filter === 'paid') {
    // ✅ FIXED: Changed to lowercase 'paid'
    $where_clause .= " AND p.status = 'paid'";
}

// Get penalties
$penalties_query = $conn->prepare("
    SELECT p.*, a.asset_name, ra.return_date
    FROM penalties p
    LEFT JOIN return_asset ra ON p.return_id = ra.return_id
    LEFT JOIN assets a ON ra.asset_id = a.asset_id
    $where_clause
    ORDER BY p.created_at DESC
");

$penalties_query->bind_param("i", $user_id);
$penalties_query->execute();
$penalties_result = $penalties_query->get_result();
$penalties_query->close();

// Get statistics
// ✅ FIXED: Changed to lowercase 'pending'
$pending_query = $conn->prepare("SELECT COUNT(*) as count, SUM(penalty_amount) as total FROM penalties WHERE user_id = ? AND status = 'pending'");
$pending_query->bind_param("i", $user_id);
$pending_query->execute();
$pending_data = $pending_query->get_result()->fetch_assoc();
$pending_count = $pending_data['count'];
$pending_total = $pending_data['total'] ?? 0;
$pending_query->close();

// ✅ FIXED: Changed to lowercase 'paid'
$paid_query = $conn->prepare("SELECT COUNT(*) as count, SUM(penalty_amount) as total FROM penalties WHERE user_id = ? AND status = 'paid'");
$paid_query->bind_param("i", $user_id);
$paid_query->execute();
$paid_data = $paid_query->get_result()->fetch_assoc();
$paid_count = $paid_data['count'];
$paid_total = $paid_data['total'] ?? 0;
$paid_query->close();

// ============ LOAD FRONTEND ============
require 'penalties_page.php';
?>