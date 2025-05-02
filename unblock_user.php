<?php
session_start();
require_once 'database.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора.';
    header('Location: login.php');
    exit();
}

// Проверка наличия ID пользователя
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Пользователь не указан.';
    header('Location: admin_nav.php');
    exit();
}

$user_id = intval($_GET['id']);

// Проверка, что пользователь существует
$check_query = "SELECT user_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Пользователь не найден.';
    header('Location: admin_nav.php');
    exit();
}

// Разблокировка пользователя
$update_query = "UPDATE users SET active = 1 WHERE user_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Пользователь успешно разблокирован.';
} else {
    $_SESSION['error'] = 'Ошибка при разблокировке пользователя.';
}

$stmt->close();
header('Location: admin_nav.php');
exit();
?>