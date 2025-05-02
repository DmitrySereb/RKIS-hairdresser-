<?php

session_start();

require_once 'database.php';    

$search = '';
if (isset($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); // Обработка спецсимволов
}

// Устанавливаем кодировку соединения
$conn->set_charset("utf8");

$query = "
    SELECT specialists.name AS specialist_name, services.service_name AS service_name
    FROM services
    LEFT JOIN specialists ON services.specialist_id = specialists.specialist_id
    WHERE services.service_name LIKE ? OR specialists.name LIKE ?
";


// Подготовка запроса
$stmt = $conn->prepare($query);

// Проверка на успешную подготовку
if ($stmt === false) {
    die('Ошибка при подготовке запроса: ' . $conn->error);
}

// Привязка параметров
$stmt->bind_param('ss', $search, $search);

// Выполнение запроса
$stmt->execute();

// Получение результата
$result = $stmt->get_result();

$services_and_specialists = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services_and_specialists[] = $row;
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парикмахерская</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="fs-4">Парикмахерская "Стиль"</h1>
            <div>
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['name'])): ?>
                    <a class="btn btn-outline-primary" href="user_dashboard.php"><span>Ваши записи</span></a>
                    <a href="logout.php" class="btn btn-danger ms-3">Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Войти</a>
                    <a href="signup.php" class="btn btn-outline-light">Зарегистрироваться</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero bg-light text-center">
        <div class="container">
            <h2 class="mb-4">Добро пожаловать в парикмахерскую "Стиль"</h2>
            <p class="mb-5">Мы создаем ваш уникальный стиль с заботой и профессионализмом.</p>
            <a href="#booking" class="btn btn-primary btn-lg">Записаться сейчас</a>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-4">О нас</h2>
            <p class="text-center">Наша команда профессионалов работает с каждым клиентом индивидуально, создавая образы, которые подчеркивают вашу уникальность.</p>
            <div class="row mt-4">
                <div class="col-md-6">
                    <img src="./img/about.png" alt="Парикмахерская" class="img-fluid rounded">
                </div>
                <div class="col-md-6">
                    <h3>Почему выбирают нас?</h3>
                    <ul>
                        <li>Профессиональные мастера</li>
                        <li>Индивидуальный подход</li>
                        <li>Современные техники и материалы</li>
                        <li>Доступные цены</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    <!-- Booking Section -->
<section id="booking" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Онлайн-запись</h2>

        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['name'])): ?>
            <!-- Если пользователь авторизован, показываем форму -->
            <div class="text-center">
                <p class="mb-4">
                    <a href="appointment.php" class="btn btn-outline-primary">ЗАПИСЬ</a>
                </p>
            </div>
        <?php else: ?>
            <!-- Если пользователь не авторизован, показываем сообщение -->
            <div class="text-center">
                <p class="mb-4">Для записи на прием, пожалуйста, <a class="text-black" href="login.php">войдите</a> в свой аккаунт или <a  class="text-black" href="signup.php">зарегистрируйтесь</a>.</p>
                <a href="login.php" class="btn btn-primary">Войти</a>
                <a href="signup.php" class="btn btn-outline-primary">Зарегистрироваться</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<section id="search" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-4">Поиск специалистов и услуг</h2>
            <form method="get" action="index.php" class="d-flex justify-content-center">
                <input type="text" name="search" class="form-control w-50" placeholder="Поиск по услуге или специалисту" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary ms-2">Найти</button>
            </form>

            <?php if (!empty($services_and_specialists)): ?>
                <h3 class="mt-4">Результаты поиска:</h3>
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Специалист</th>
                            <th>Услуга</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services_and_specialists as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['specialist_name']); ?></td>
                                <td><?= htmlspecialchars($row['service_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (isset($_GET['search']) && empty($services_and_specialists)): ?>
                <p class="text-center mt-4">Нет результатов для поиска.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p>© 2024 Парикмахерская "Стиль". Все права защищены.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
