<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}
require '../../config/db_connect.php';

// ============ GET ALL DATA ============

$users_query = $conn->prepare("
    SELECT user_id, user_name, email, role, department_name, created_at 
    FROM users 
    WHERE role = 'user'
    ORDER BY created_at DESC
");
$users_query->execute();
$users_result = $users_query->get_result();
$users_query->close();

// Get total users
$total_query = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_query->execute();
$total_data = $total_query->get_result()->fetch_assoc();
$total_users = $total_data['total'];
$total_query->close();

// Get users by department
$dept_query = $conn->prepare("
    SELECT department_name, COUNT(*) as count 
    FROM users 
    WHERE role = 'user'
    GROUP BY department_name
");
$dept_query->execute();
$dept_result = $dept_query->get_result();
$dept_data = [];
while ($row = $dept_result->fetch_assoc()) {
    $dept_data[] = $row;
}
$dept_query->close();

// Get all departments for dropdown
$all_depts_query = $conn->prepare("SELECT DISTINCT department_name FROM users WHERE department_name IS NOT NULL ORDER BY department_name");
$all_depts_query->execute();
$all_depts_result = $all_depts_query->get_result();
$all_depts = [];
while ($row = $all_depts_result->fetch_assoc()) {
    $all_depts[] = $row['department_name'];
}
$all_depts_query->close();

// ============ LOAD FRONTEND ============
require 'users.page.php';
?>
