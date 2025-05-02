<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    $_SESSION['error'] = 'Доступ запрещен.';
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    
    // Обновляем статус на "Завершена" (status_id = 3)
    $query = "UPDATE appointments SET status_id = 3 WHERE appointment_id = ? AND specialist_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Запись успешно завершена.';
    } else {
        $_SESSION['error'] = 'Ошибка при обновлении записи.';
    }
    
    $stmt->close();
}

header('Location: specialist_dashboard.php');
exit();
?>