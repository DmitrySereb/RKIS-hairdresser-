<?php
session_start();
require_once 'database.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Перенаправление в зависимости от роли
switch ($_SESSION['role']) {
    case 1: // Администратор
        header('Location: admin_nav.php');
        break;
    case 2: // Специалист
        header('Location: specialist_dashboard.php');
        break;
    case 3: // Клиент
        header('Location: user_dashboard.php');
        break;
    default:
        header('Location: login.php');
        break;
}
exit();
?>