<?php
session_start();
require_once 'database.php'; // Подключение к базе данных

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Пожалуйста, войдите в систему.';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Проверка, была ли передана запись для отмены
if (!isset($_GET['appointment_id'])) {
    $_SESSION['error'] = 'Запись не найдена.';
    header('Location: dashboard.php');
    exit();
}

$appointment_id = intval($_GET['appointment_id']);

// Удаление записи
$query = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND user_id = ?");
$query->bind_param("ii", $appointment_id, $user_id);

if ($query->execute()) {
    $_SESSION['success'] = 'Запись успешно отменена.';
} else {
    $_SESSION['error'] = 'Ошибка при отмене записи.';
}

$query->close();
header('Location: user_dashboard.php');
exit();
