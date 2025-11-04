<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Получаем последние новости
$stmt = $pdo->query("SELECT * FROM news WHERE is_published = TRUE ORDER BY published_at DESC LIMIT 3");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем предстоящие мероприятия
$stmt = $pdo->query("SELECT * FROM events WHERE event_date > NOW() ORDER BY event_date ASC LIMIT 3");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем новые книги
$stmt = $pdo->query("SELECT * FROM books WHERE available > 0 ORDER BY created_at DESC LIMIT 6");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Библиотечная система - Главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Библиотечная система</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Главная</a></li>
                    <li class="nav-item"><a class="nav-link" href="books.php">Книги</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Мероприятия</a></li>
                    <li class="nav-item"><a class="nav-link" href="news.php">Новости</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/">Панель управления</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="user/">Личный кабинет</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Выйти (<?= escape($_SESSION['username']) ?>)</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Войти</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Герой секция -->
    <section class="hero bg-light py-5">
        <div class="container text-center">
            <h1 class="display-4">Добро пожаловать в библиотечную систему</h1>
            <p class="lead">Откройте для себя мир книг и знаний</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-lg">Присоединиться</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Новые книги -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Новые поступления</h2>
            <div class="row">
                <?php foreach ($books as $book): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= escape($book['title']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= escape($book['author']) ?></h6>
                            <p class="card-text"><?= escape(substr($book['description'], 0, 100)) ?>...</p>
                            <p class="card-text"><small class="text-muted">Год: <?= escape($book['year_published']) ?></small></p>
                            <p class="card-text"><small class="text-success">Доступно: <?= escape($book['available']) ?> экз.</small></p>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="user/books.php?action=reserve&book_id=<?= $book['id'] ?>" class="btn btn-primary btn-sm">Забронировать</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="books.php" class="btn btn-outline-primary">Все книги</a>
            </div>
        </div>
    </section>

    <!-- Мероприятия -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Ближайшие мероприятия</h2>
            <div class="row">
                <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= escape($event['title']) ?></h5>
                            <p class="card-text"><?= escape(substr($event['description'], 0, 100)) ?>...</p>
                            <p class="card-text"><small class="text-muted">
                                <?= date('d.m.Y H:i', strtotime($event['event_date'])) ?><br>
                                Место: <?= escape($event['location']) ?>
                            </small></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Участники: <?= escape($event['current_participants']) ?>/<?= escape($event['max_participants']) ?>
                                </small>
                            </p>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="user/events.php?action=register&event_id=<?= $event['id'] ?>" class="btn btn-primary btn-sm">Записаться</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="events.php" class="btn btn-outline-primary">Все мероприятия</a>
            </div>
        </div>
    </section>

    <!-- Новости -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Последние новости</h2>
            <div class="row">
                <?php foreach ($news as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= escape($item['title']) ?></h5>
                            <p class="card-text"><?= escape(substr($item['content'], 0, 150)) ?>...</p>
                            <p class="card-text"><small class="text-muted">
                                <?= date('d.m.Y H:i', strtotime($item['published_at'])) ?>
                            </small></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="news.php" class="btn btn-outline-primary">Все новости</a>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 Библиотечная система. Все права защищены.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>