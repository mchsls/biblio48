<?php
// library_system.php - вход в систему бронирования
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/' : 'user/'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотечная система - Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .system-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .btn-portal {
            background: #28a745;
            border-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .btn-portal:hover {
            background: #218838;
            border-color: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="system-header">
        <div class="container text-center">
            <h1>📚 Система бронирования книг</h1>
            <p class="lead">Забронируйте книги и запишитесь на мероприятия онлайн</p>
            <a href="https://mchsls.github.io/biblio48/" class="btn btn-light btn-lg">
                ← Вернуться на основной сайт
            </a>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="mb-0">Вход в систему</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Войти в систему
                            </a>
                            <a href="register.php" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus"></i> Зарегистрироваться
                            </a>
                            <a href="books.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-book"></i> Посмотреть каталог книг
                            </a>
                            <a href="events.php" class="btn btn-outline-info btn-lg">
                                <i class="fas fa-calendar-alt"></i> Посмотреть мероприятия
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <h6>Тестовый доступ:</h6>
                            <p class="small text-muted">
                                Администратор: admin / admin123<br>
                                Или зарегистрируйтесь как новый пользователь
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p>Система бронирования библиотеки | 
               <a href="https://mchsls.github.io/biblio48/" class="text-warning">Основной сайт библиотеки</a>
            </p>
        </div>
    </footer>
</body>
</html>