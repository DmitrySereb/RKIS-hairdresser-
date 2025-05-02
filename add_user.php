<?php
session_start();
require_once 'database.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора.';
    header('Location: login.php');
    exit();
}

// Получение списка ролей
$roles_query = "SELECT * FROM roles";
$roles_result = $conn->query($roles_query);

// Обработка формы добавления пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);
    $role_id = intval($_POST['role_id']);
    $active = isset($_POST['active']) ? 1 : 0;

    // Валидация данных
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Введите имя пользователя';
    }
    
    if (empty($login)) {
        $errors[] = 'Введите логин';
    } elseif (strlen($login) < 4) {
        $errors[] = 'Логин должен содержать минимум 4 символа';
    }
    
    if (empty($password)) {
        $errors[] = 'Введите пароль';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов';
    } elseif ($password !== $password_confirm) {
        $errors[] = 'Пароли не совпадают';
    }
    
    // Проверка уникальности логина
    if (empty($errors)) {
        $check_query = "SELECT user_id FROM users WHERE login = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Этот логин уже занят';
        }
        $stmt->close();
    }

    // Если нет ошибок - добавляем пользователя
    if (empty($errors)) {
        $insert_query = "INSERT INTO users (name, login, password, role_id, active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssii", $name, $login, $password, $role_id, $active);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Пользователь успешно добавлен';
            header('Location: admin_nav.php');
            exit();
        } else {
            $errors[] = 'Ошибка при добавлении пользователя: ' . $conn->error;
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить пользователя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-form {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="container-form">
            <h2 class="form-title">Добавить нового пользователя</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Имя пользователя</label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" class="form-control" id="login" name="login" required 
                           value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Подтверждение пароля</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                </div>
                
                <div class="mb-3">
                    <label for="role_id" class="form-label">Роль</label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <?php while ($role = $roles_result->fetch_assoc()): ?>
                            <option value="<?= $role['role_id'] ?>" 
                                <?= isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['role_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="active" name="active" 
                           <?= isset($_POST['active']) && $_POST['active'] ? 'checked' : 'checked' ?>>
                    <label class="form-check-label" for="active">Активный</label>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">Добавить</button>
                    <a href="admin_nav.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>