<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php'; // Подключение к базе данных

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Пожалуйста, войдите в систему.';
    header('Location: login.php');
    exit();
}

// Получение информации о пользователе
$user_id = $_SESSION['user_id'];

// Запрос для получения всех доступных услуг
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);

// Запрос для получения всех специалистов
$specialists_query = "SELECT * FROM specialists";
$specialists_result = $conn->query($specialists_query);

// Обработка формы записи на прием
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'];
    $specialist_id = $_POST['specialist_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Проверка на заполненность всех полей
    if (empty($service_id) || empty($specialist_id) || empty($date) || empty($time)) {
        $_SESSION['error'] = 'Пожалуйста, заполните все поля!';
        header('Location: appointment.php');
        exit();
    }

    // Вставка записи в базу данных
    $status_id = 1; // Пример: "1" может соответствовать статусу "новый" или "в ожидании"

    // Вставка записи в базу данных
    $appointment_query = "
        INSERT INTO appointments (user_id, service_id, specialist_id, date, time, status_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($appointment_query);
    $stmt->bind_param("iiissi", $user_id, $service_id, $specialist_id, $date, $time, $status_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Вы успешно записались на прием!';
        header('Location: user_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = 'Произошла ошибка при записи на прием. Попробуйте снова.';
    }
    
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Записаться на прием</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-decoration-none text-white">
                <h1 class="fs-4 mb-0">Парикмахерская "Стиль"</h1>
            </a>
            <div>
                <span>Добро пожаловать, <?= htmlspecialchars($_SESSION['name']); ?>!</span>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </div>
        </div>
    </header>

    <div class="container mt-5">
        <h1 class="text-center">Записаться на прием</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="service_id">Выберите услугу</label>
                <select class="form-control" id="service_id" name="service_id">
                    <option value="">Выберите услугу</option>
                    <?php while ($service = $services_result->fetch_assoc()): ?>
                        <option value="<?= $service['service_id']; ?>"><?= htmlspecialchars($service['service_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="specialist_id">Выберите специалиста</label>
                <select class="form-control" id="specialist_id" name="specialist_id">
                    <option value="">Выберите специалиста</option>
                    <?php while ($specialist = $specialists_result->fetch_assoc()): ?>
                        <option value="<?= $specialist['specialist_id']; ?>"><?= htmlspecialchars($specialist['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date">Дата</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>

            <div class="form-group">
                <label for="time">Время</label>
                <input type="time" class="form-control" id="time" name="time" required>
            </div>

            <button type="submit" class="btn btn-success">Записаться</button>
        </form>
    </div>

</body>
</html>
