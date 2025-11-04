<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Получаем новости
try {
    $stmt = $pdo->query("
        SELECT * FROM news 
        WHERE is_published = TRUE 
        ORDER BY published_at DESC
    ");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $news = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости - Библиотечная система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Библиотечная система</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="index.php">Главная</a>
                <a class="nav-link" href="books.php">Книги</a>
                <a class="nav-link" href="events.php">Мероприятия</a>
                <a class="nav-link active" href="news.php">Новости</a>
            </div>
            <div class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a class="nav-link" href="admin/">Панель управления</a>
                    <?php else: ?>
                        <a class="nav-link" href="user/">Личный кабинет</a>
                    <?php endif; ?>
                    <a class="nav-link" href="logout.php">Выйти</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Войти</a>
                    <a class="nav-link" href="register.php">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Новости</h1>
        
        <div class="row">
            <?php if ($news): ?>
                <?php foreach ($news as $item): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= escape($item['title']) ?></h5>
                            <p class="card-text"><?= nl2br(escape($item['content'])) ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($item['published_at'])) ?>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Новости пока отсутствуют.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>