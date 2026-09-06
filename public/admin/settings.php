<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../config/db_connect.php';

// ============ GET CURRENT SETTINGS ============
$settings_query = $conn->prepare("
    SELECT * FROM admin_settings LIMIT 1
");
$settings_query->execute();
$settings_result = $settings_query->get_result();
$settings = $settings_result->fetch_assoc() ?? [];
$settings_query->close();

// ============ GET ASSET CATEGORIES ============
$categories_query = $conn->prepare("SELECT * FROM asset_categories ORDER BY category_name");
$categories_query->execute();
$categories = $categories_query->get_result()->fetch_all(MYSQLI_ASSOC);
$categories_query->close();

// ============ GET DEPARTMENTS ============
$departments_query = $conn->prepare("
    SELECT DISTINCT department_name FROM users WHERE role = 'user' ORDER BY department_name
");
$departments_query->execute();
$departments = $departments_query->get_result()->fetch_all(MYSQLI_ASSOC);
$departments_query->close();

// ============ LOAD FRONTEND ============
require 'settings.page.php';
?>
