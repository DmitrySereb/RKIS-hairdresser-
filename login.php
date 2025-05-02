<?php
session_start();

// Подключение к базе данных
$mysqli = require 'database.php';

if (isset($_POST['login'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Используем подготовленные выражения для безопасности
    $sql = "SELECT * FROM users WHERE login = ? AND password = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $login, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $role = $row['role_id'];

        if ($row["active"] == 0) {
            die("Ты забанен урод");
        }

        session_regenerate_id();
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["role"] = $role;

        // Перенаправление на панели в зависимости от роли
        if ($role == 3) {
            header('Location: user_dashboard.php');
            exit();
        } elseif ($role == 2) {
            header('Location: specialist_dashboard.php');
            exit();
        } else {
            header('Location: admin_nav.php');
            exit();
        }
    } else {
        echo "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex-grow: 1;
        }
        footer {
            position: relative;
            bottom: 0;
            width: 100%;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-decoration-none text-white">
                <h1 class="fs-4 mb-0">Парикмахерская "Стиль"</h1>
            </a>
            <div>
                <a href="login.php" class="btn btn-outline-light me-2">Войти</a>
                <a href="signup.php" class="btn btn-outline-light">Зарегистрироваться</a>
            </div>
        </div>
    </header>

    <!-- Login Form -->
    <main class="container my-4">
        <div class="form-container">
            <h2 class="text-center">Вход в систему</h2>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" class="form-control" id="login" name="login" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Войти</button>
                <p class="mt-3 text-center">Нет аккаунта? <a href="signup.php" class="text-primary">Зарегистрироваться</a></p>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">© 2024 Парикмахерская "Стиль". Все права защищены.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>