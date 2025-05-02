<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
            position: relative;
            bottom: 0;
            width: 100%;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="text-decoration-none text-white">
                <h1 class="fs-4 mb-0">Парикмахерская "Стиль"</h1>
            </a>
            <div>
                <a href="login.php" class="btn btn-outline-light me-2">Войти</a>
                <a href="signup.php" class="btn btn-outline-light">Зарегистрироваться</a>
            </div>
        </div>
    </header>

    <!-- Signup Form -->
    <main class="container my-4">
        <div class="form-container">
            <h2 class="text-center">Регистрация</h2>

            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form action="process_signup.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" class="form-control" id="login" name="login" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Подтвердите пароль</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                <p class="mt-3 text-center">Уже есть аккаунт? <a href="login.php" class="text-primary">Войти</a></p>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">© 2024 Парикмахерская "Стиль". Все права защищены.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
