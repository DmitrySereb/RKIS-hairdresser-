<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$service_id = intval($_GET['id']);
$query = "SELECT * FROM services WHERE service_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$stmt->close();

$specialists_query = "SELECT * FROM specialists";
$specialists_result = $conn->query($specialists_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_name = $_POST['service_name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $specialist_id = !empty($_POST['specialist_id']) ? $_POST['specialist_id'] : NULL;

    $update_query = "UPDATE services SET service_name = ?, price = ?, duration = ?, specialist_id = ? WHERE service_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sdiii", $service_name, $price, $duration, $specialist_id, $service_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Услуга успешно обновлена';
        header('Location: admin_nav.php');
        exit();
    } else {
        $_SESSION['error'] = 'Ошибка при обновлении услуги';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование услуги</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Редактирование услуги</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Название услуги</label>
                <input type="text" name="service_name" class="form-control" value="<?= htmlspecialchars($service['service_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Цена</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($service['price']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Длительность (минут)</label>
                <input type="number" name="duration" class="form-control" value="<?= htmlspecialchars($service['duration']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Специалист</label>
                <select name="specialist_id" class="form-control">
                    <option value="">Любой специалист</option>
                    <?php while($specialist = $specialists_result->fetch_assoc()): ?>
                        <option value="<?= $specialist['specialist_id'] ?>" <?= $specialist['specialist_id'] == $service['specialist_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($specialist['name']) ?>
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