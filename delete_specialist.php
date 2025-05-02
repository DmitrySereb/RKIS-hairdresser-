<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$specialist_id = intval($_GET['id']);
$query = "DELETE FROM specialists WHERE specialist_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $specialist_id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = 'Специалист успешно удален';
header('Location: admin_nav.php');
exit();
?>