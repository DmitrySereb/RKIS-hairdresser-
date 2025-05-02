<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$appointment_id = intval($_GET['id']);
$query = "SELECT * FROM appointments WHERE appointment_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Получение списка пользователей, услуг, специалистов и статусов
$users_query = "SELECT * FROM users WHERE role_id = 3"; // Только клиенты
$users_result = $conn->query($users_query);

$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);

$specialists_query = "SELECT * FROM specialists";
$specialists_result = $conn->query($specialists_query);

$statuses_query = "SELECT * FROM statuses";
$statuses_result = $conn->query($statuses_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $service_id = intval($_POST['service_id']);
    $specialist_id = intval($_POST['specialist_id']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status_id = intval($_POST['status_id']);

    $update_query = "UPDATE appointments SET user_id = ?, service_id = ?, specialist_id = ?, date = ?, time = ?, status_id = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iiissii", $user_id, $service_id, $specialist_id, $date, $time, $status_id, $appointment_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Запись успешно обновлена';
        header('Location: admin_nav.php');
        exit();
    } else {
        $_SESSION['error'] = 'Ошибка при обновлении записи';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование записи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Редактирование записи</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Клиент</label>
                <select name="user_id" class="form-control" required>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                        <option value="<?= $user['user_id'] ?>" <?= $user['user_id'] == $appointment['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Услуга</label>
                <select name="service_id" class="form-control" required>
                    <?php while($service = $services_result->fetch_assoc()): ?>
                        <option value="<?= $service['service_id'] ?>" <?= $service['service_id'] == $appointment['service_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['service_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Специалист</label>
                <select name="specialist_id" class="form-control" required>
                    <?php while($specialist = $specialists_result->fetch_assoc()): ?>
                        <option value="<?= $specialist['specialist_id'] ?>" <?= $specialist['specialist_id'] == $appointment['specialist_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($specialist['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Дата</label>
                <input type="date" name="date" class="form-control" value="<?= $appointment['date'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Время</label>
                <input type="time" name="time" class="form-control" value="<?= $appointment['time'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Статус</label>
                <select name="status_id" class="form-control" required>
                    <?php while($status = $statuses_result->fetch_assoc()): ?>
                        <option value="<?= $status['status_id'] ?>" <?= $status['status_id'] == $appointment['status_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($status['status_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="admin_nav.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>