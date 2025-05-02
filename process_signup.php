<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'database.php';

// Проверка, что форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = $_POST['name'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Проверка на пустые значения
    if (empty($name) || empty($login) || empty($password) || empty($password_confirm)) {
        $_SESSION['error'] = 'Заполните все поля!';
        header('Location: signup.php');
        exit();
    }

    // Проверка на совпадение паролей
    if ($password !== $password_confirm) {
        $_SESSION['error'] = 'Пароли не совпадают!';
        header('Location: signup.php');
        exit();
    }

    // Проверка уникальности логина
    $query = $conn->prepare("SELECT * FROM users WHERE login = ?");
    $query->bind_param("s", $login); // Привязка параметра для безопасного запроса
    $query->execute();
    $query->store_result(); // Хранение результата, чтобы можно было использовать num_rows
    if ($query->num_rows > 0) {
        $_SESSION['error'] = 'Этот логин уже занят!';
        header('Location: signup.php');
        exit();
    }



    // Запрос на добавление нового пользователя
    $insert_query = $conn->prepare("INSERT INTO users (name, login, password, role_id) VALUES (?, ?, ?, ?)");
    $insert_query->execute([$name, $login, $password, 3]); // Роль 3 - Клиент

    // Перенаправление на страницу входа
    $_SESSION['success'] = 'Регистрация прошла успешно! Теперь войдите в систему.';
    header('Location: login.php');
    exit();
} else {
    // Если доступ не с формы, перенаправляем на страницу регистрации
    header('Location: signup.php');
    exit();
}
?>
