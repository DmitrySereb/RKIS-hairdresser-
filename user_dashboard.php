<?php

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

// Запрос для получения имени пользователя из базы данных
$query = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$query->store_result();
$query->bind_result($user_name);
$query->fetch();
$query->close();
// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$_SESSION['name'] = $user_name;;

// Запрос для получения записей пользователя
$query = "
    SELECT 
        appointments.appointment_id, 
        appointments.date, 
        appointments.time, 
        specialists.name AS specialist_name, 
        services.service_name AS service_name
    FROM appointments
    JOIN specialists ON appointments.specialist_id = specialists.specialist_id
    JOIN services ON appointments.service_id = services.service_id
    WHERE appointments.user_id = ?
";

// Подготовка запроса
$stmt = $conn->prepare($query);

// Проверка на успешную подготовку
if ($stmt === false) {
    die('Ошибка при подготовке запроса: ' . $conn->error);
}

// Привязка параметра
$stmt->bind_param("i", $user_id);

// Выполнение запроса
$stmt->execute();

// Получение результата
$result = $stmt->get_result();

// Получение записей пользователя
$appointments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Закрытие запроса
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            background-color: #343a40;
            color: white;
            padding: 1rem 0;
            position: relative;
            bottom: 0;
            width: 100%;
            text-align: center;
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
                <span>Добро пожаловать, <?= htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mt-5">
        <p class="text-center">Здесь вы можете просмотреть свои записи.</p>

        <?php if (count($appointments) > 0): ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Услуга</th>
                        <th>Специалист</th>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['service_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['specialist_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['date']); ?></td>
                            <td><?= htmlspecialchars($appointment['time']); ?></td>
                            <td>
                                
                                <a href="cancel.php?appointment_id=<?= $appointment['appointment_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите отменить запись?');">Отменить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center mt-4">У вас пока нет записей.</p>
            <p class="text-center mt-4">
                <a href="appointment.php" class="btn btn-success">Записаться на прием</a>
            </p>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer>
        <p>© 2024 Парикмахерская "Стиль". Все права защищены.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
