<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_GET['id']);
$query = "UPDATE users SET active = 0 WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = 'Пользователь заблокирован';
header('Location: admin_nav.php');
exit();
?>