<?php
session_start();
require_once 'database.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора.';
    header('Location: login.php');
    exit();
}

// Обработка формы добавления специалиста
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $expertise = trim($_POST['expertise']);

    // Валидация данных
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Введите имя специалиста';
    }

    // Если нет ошибок - добавляем специалиста
    if (empty($errors)) {
        $insert_query = "INSERT INTO specialists (name, expertise) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $name, $expertise);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Специалист успешно добавлен';
            header('Location: admin_nav.php');
            exit();
        } else {
            $errors[] = 'Ошибка при добавлении специалиста: ' . $conn->error;
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
    <title>Добавить специалиста</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container-form {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-title { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="container-form">
            <h2 class="form-title">Добавить нового специалиста</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Имя специалиста</label>
                    <input type="text" class="form-control" id="name" name="name" required
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="expertise" class="form-label">Специализация</label>
                    <input type="text" class="form-control" id="expertise" name="expertise"
                           value="<?= isset($_POST['expertise']) ? htmlspecialchars($_POST['expertise']) : '' ?>">
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