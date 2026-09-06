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

if ($status_filter === 'pending') {
    $where_clause = " WHERE p.status = 'Pending'";
} elseif ($status_filter === 'paid') {
    $where_clause = " WHERE p.status = 'Paid'";
}

$penalties_query = $conn->prepare("
    SELECT p.*, u.user_name, u.email, u.department_name, a.asset_name
    FROM penalties p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN return_asset rr ON p.return_id = rr.return_id
    LEFT JOIN assets a ON rr.asset_id = a.asset_id
    $where_clause
    ORDER BY p.created_at DESC
");

$penalties_query->execute();
$penalties_result = $penalties_query->get_result();
$penalties_query->close();

// Get statistics
$pending_query = $conn->prepare("SELECT COUNT(*) as count, SUM(penalty_amount) as total FROM penalties WHERE status = 'Pending'");
$pending_query->execute();
$pending_data = $pending_query->get_result()->fetch_assoc();
$pending_count = $pending_data['count'];
$pending_total = $pending_data['total'] ?? 0;
$pending_query->close();

$paid_query = $conn->prepare("SELECT COUNT(*) as count, SUM(penalty_amount) as total FROM penalties WHERE status = 'Paid'");
$paid_query->execute();
$paid_data = $paid_query->get_result()->fetch_assoc();
$paid_count = $paid_data['count'];
$paid_total = $paid_data['total'] ?? 0;
$paid_query->close();

// ============ LOAD FRONTEND ============
require 'penalties.page.php';
?>