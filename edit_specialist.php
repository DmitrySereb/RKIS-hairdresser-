<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$specialist_id = intval($_GET['id']);
$query = "SELECT * FROM specialists WHERE specialist_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $specialist_id);
$stmt->execute();
$specialist = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $expertise = $_POST['expertise'];

    $update_query = "UPDATE specialists SET name = ?, expertise = ? WHERE specialist_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $name, $expertise, $specialist_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Специалист успешно обновлен';
        header('Location: admin_nav.php');
        exit();
    } else {
        $_SESSION['error'] = 'Ошибка при обновлении специалиста';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование специалиста</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Редактирование специалиста</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Имя</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($specialist['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Специализация</label>
                <input type="text" name="expertise" class="form-control" value="<?= htmlspecialchars($specialist['expertise']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="admin_nav.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>