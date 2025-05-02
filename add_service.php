<?php
session_start();
require_once 'database.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора.';
    header('Location: login.php');
    exit();
}

// Получение списка специалистов
$specialists_query = "SELECT * FROM specialists";
$specialists_result = $conn->query($specialists_query);

// Обработка формы добавления услуги
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = trim($_POST['service_name']);
    $price = trim($_POST['price']);
    $duration = trim($_POST['duration']);
    $specialist_id = !empty($_POST['specialist_id']) ? $_POST['specialist_id'] : NULL;

    // Валидация данных
    $errors = [];
    
    if (empty($service_name)) {
        $errors[] = 'Введите название услуги';
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = 'Введите корректную цену';
    }
    
    if (empty($duration) || !is_numeric($duration) || $duration <= 0) {
        $errors[] = 'Введите корректную длительность (в минутах)';
    }

    // Если нет ошибок - добавляем услугу
    if (empty($errors)) {
        $insert_query = "INSERT INTO services (service_name, price, duration, specialist_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sdii", $service_name, $price, $duration, $specialist_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Услуга успешно добавлена';
            header('Location: admin_nav.php');
            exit();
        } else {
            $errors[] = 'Ошибка при добавлении услуги: ' . $conn->error;
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
    <title>Добавить услугу</title>
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
            <h2 class="form-title">Добавить новую услугу</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="service_name" class="form-label">Название услуги</label>
                    <input type="text" class="form-control" id="service_name" name="service_name" required
                           value="<?= isset($_POST['service_name']) ? htmlspecialchars($_POST['service_name']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="price" class="form-label">Цена (руб.)</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required
                           value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="duration" class="form-label">Длительность (минут)</label>
                    <input type="number" class="form-control" id="duration" name="duration" required
                           value="<?= isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : '' ?>">
                </div>
                
                <div class="mb-3">
                    <label for="specialist_id" class="form-label">Специалист (необязательно)</label>
                    <select class="form-select" id="specialist_id" name="specialist_id">
                        <option value="">Любой специалист</option>
                        <?php while ($specialist = $specialists_result->fetch_assoc()): ?>
                            <option value="<?= $specialist['specialist_id'] ?>"
                                <?= isset($_POST['specialist_id']) && $_POST['specialist_id'] == $specialist['specialist_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($specialist['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
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