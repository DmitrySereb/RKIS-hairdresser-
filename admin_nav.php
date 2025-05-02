<?php
session_start();
require_once 'database.php';

// Проверка авторизации и роли администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора.';
    header('Location: login.php');
    exit();
}

// Получение списка пользователей
$users_query = "SELECT u.user_id, u.name, u.login, r.role_name, u.active 
                FROM users u 
                JOIN roles r ON u.role_id = r.role_id";
$users_result = $conn->query($users_query);

// Получение списка услуг
$services_query = "SELECT s.service_id, s.service_name, s.price, s.duration, sp.name as specialist_name 
                   FROM services s 
                   LEFT JOIN specialists sp ON s.specialist_id = sp.specialist_id";
$services_result = $conn->query($services_query);

// Получение списка специалистов
$specialists_query = "SELECT * FROM specialists";
$specialists_result = $conn->query($specialists_query);

// Получение списка всех записей
$appointments_query = "SELECT a.appointment_id, u.name as user_name, s.service_name, sp.name as specialist_name, 
                              a.date, a.time, st.status_name
                       FROM appointments a
                       JOIN users u ON a.user_id = u.user_id
                       JOIN services s ON a.service_id = s.service_id
                       JOIN specialists sp ON a.specialist_id = sp.specialist_id
                       JOIN statuses st ON a.status_id = st.status_id";
$appointments_result = $conn->query($appointments_query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .nav-tabs {
            margin-bottom: 20px;
        }
        .tab-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="fs-4">Панель администратора</h1>
            <div>
                <span class="me-3">Добро пожаловать, <?= htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-4">
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Пользователи</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">Услуги</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="specialists-tab" data-bs-toggle="tab" data-bs-target="#specialists" type="button" role="tab">Специалисты</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">Записи</button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabsContent">
            <!-- Пользователи -->
            <div class="tab-pane fade show active" id="users" role="tabpanel">
                <h2>Управление пользователями</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Логин</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['login']) ?></td>
                            <td><?= htmlspecialchars($user['role_name']) ?></td>
                            <td><?= $user['active'] ? 'Активен' : 'Заблокирован' ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <?php if($user['active']): ?>
                                    <a href="block_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">Заблокировать</a>
                                <?php else: ?>
                                    <a href="unblock_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-success">Разблокировать</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="add_user.php" class="btn btn-success">Добавить пользователя</a>
            </div>

            <!-- Услуги -->
            <div class="tab-pane fade" id="services" role="tabpanel">
                <h2>Управление услугами</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Цена</th>
                            <th>Длительность</th>
                            <th>Специалист</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($service = $services_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $service['service_id'] ?></td>
                            <td><?= htmlspecialchars($service['service_name']) ?></td>
                            <td><?= $service['price'] ?> руб.</td>
                            <td><?= $service['duration'] ?> мин.</td>
                            <td><?= $service['specialist_name'] ?? 'Любой' ?></td>
                            <td>
                                <a href="edit_service.php?id=<?= $service['service_id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="delete_service.php?id=<?= $service['service_id'] ?>" class="btn btn-sm btn-danger">Удалить</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="add_service.php" class="btn btn-success">Добавить услугу</a>
            </div>

            <!-- Специалисты -->
            <div class="tab-pane fade" id="specialists" role="tabpanel">
                <h2>Управление специалистами</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Специализация</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($specialist = $specialists_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $specialist['specialist_id'] ?></td>
                            <td><?= htmlspecialchars($specialist['name']) ?></td>
                            <td><?= htmlspecialchars($specialist['expertise']) ?></td>
                            <td>
                                <a href="edit_specialist.php?id=<?= $specialist['specialist_id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="delete_specialist.php?id=<?= $specialist['specialist_id'] ?>" class="btn btn-sm btn-danger">Удалить</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="add_specialist.php" class="btn btn-success">Добавить специалиста</a>
            </div>

            <!-- Записи -->
            <div class="tab-pane fade" id="appointments" role="tabpanel">
                <h2>Управление записями</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Услуга</th>
                            <th>Специалист</th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($appointment = $appointments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $appointment['appointment_id'] ?></td>
                            <td><?= htmlspecialchars($appointment['user_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['specialist_name']) ?></td>
                            <td><?= $appointment['date'] ?></td>
                            <td><?= $appointment['time'] ?></td>
                            <td><?= htmlspecialchars($appointment['status_name']) ?></td>
                            <td>
                                <a href="edit_appointment.php?id=<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="cancel.php?id=<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-danger">Отменить</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-auto">
        <div class="container">
            <p class="mb-0">© 2024 Парикмахерская "Стиль". Все права защищены.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>