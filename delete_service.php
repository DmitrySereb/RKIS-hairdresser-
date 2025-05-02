<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$service_id = intval($_GET['id']);
$query = "DELETE FROM services WHERE service_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = 'Услуга успешно удалена';
header('Location: admin_nav.php');
exit();
?>