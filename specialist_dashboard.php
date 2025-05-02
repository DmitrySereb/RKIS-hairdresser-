<?php
session_start();
require_once 'database.php'; // Подключение к базе данных

// Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) { // Роль 2 - специалист
    $_SESSION['error'] = 'Доступ запрещен.';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "
    SELECT 
        appointments.appointment_id, 
        appointments.date, 
        appointments.time, 
        users.name AS client_name,
        specialists.name AS specialist_name, 
        services.service_name AS service_name,
        statuses.status_name AS status_name,
        statuses.status_id AS status_id
    FROM appointments
    JOIN users ON appointments.user_id = users.user_id
    JOIN specialists ON appointments.specialist_id = specialists.specialist_id
    JOIN services ON appointments.service_id = services.service_id
    JOIN statuses ON appointments.status_id = statuses.status_id
    WHERE appointments.specialist_id = ?
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

$appointments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель специалиста</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
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
    .badge-completed {
        background-color: #28a745;
    }
    .badge-pending {
        background-color: #ffc107;
    }
</style>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="fs-4">Панель специалиста</h1>
            <div>
                <a href="logout.php" class="btn btn-danger ms-3">Выйти</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Ваши записи</h2>
        <?php if (count($appointments) > 0): ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Клиент</th>
                        <th>Специалист</th>
                        <th>Услуга</th>
                        <th>Дата</th>
                        <th>Время</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['client_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['specialist_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['service_name']); ?></td>
                            <td><?= htmlspecialchars($appointment['date']); ?></td>
                            <td><?= htmlspecialchars($appointment['time']); ?></td>
                            <td>
                                <?php if ($appointment['status_name'] == 'Завершена'): ?>
                                    <span class="badge badge-completed">Завершено</span>
                                <?php else: ?>
                                    <span class="badge badge-pending"><?= htmlspecialchars($appointment['status_name']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($appointment['status_name'] != 'Завершена'): ?>
                                    <form action="mark_completed.php" method="post" style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Отметить как завершено</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Завершено</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center mt-4">У вас пока нет записей.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p>© 2024 Парикмахерская "Стиль". Все права защищены.</p>
    </footer>
</body>
</html>