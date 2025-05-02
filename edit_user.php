<?php
session_start();
require_once 'database.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

// Получение данных пользователя для редактирования
$user_id = intval($_GET['id']);
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Получение списка ролей
$roles_query = "SELECT * FROM roles";
$roles_result = $conn->query($roles_query);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $role_id = intval($_POST['role_id']);
    $active = isset($_POST['active']) ? 1 : 0;

    $update_query = "UPDATE users SET name = ?, login = ?, password = ?, role_id = ?, active = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssiii", $name, $login, $password, $role_id, $active, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Пользователь успешно обновлен';
        header('Location: admin_nav.php');
        exit();
    } else {
        $_SESSION['error'] = 'Ошибка при обновлении пользователя';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование пользователя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Редактирование пользователя</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Имя</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Логин</label>
                <input type="text" name="login" class="form-control" value="<?= htmlspecialchars($user['login']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-control" value="<?= htmlspecialchars($user['password']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Роль</label>
                <select name="role_id" class="form-control" required>
                    <?php while($role = $roles_result->fetch_assoc()): ?>
                        <option value="<?= $role['role_id'] ?>" <?= $role['role_id'] == $user['role_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['role_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="active" class="form-check-input" id="active" <?= $user['active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="active">Активен</label>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="admin_nav.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>