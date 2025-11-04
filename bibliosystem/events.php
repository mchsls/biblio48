<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Получаем мероприятия
try {
    $stmt = $pdo->query("
        SELECT * FROM events 
        WHERE event_date > NOW() 
        ORDER BY event_date ASC
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мероприятия - Библиотечная система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Библиотечная система</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="index.php">Главная</a>
                <a class="nav-link" href="books.php">Книги</a>
                <a class="nav-link active" href="events.php">Мероприятия</a>
                <a class="nav-link" href="news.php">Новости</a>
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
        <h1 class="mb-4">Мероприятия</h1>
        
        <div class="row">
            <?php if ($events): ?>
                <?php foreach ($events as $event): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= escape($event['title']) ?></h5>
                            <p class="card-text"><?= escape($event['description']) ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> 
                                    <?= date('d.m.Y H:i', strtotime($event['event_date'])) ?><br>
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?= escape($event['location']) ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <span class="badge bg-info">
                                    Участники: <?= escape($event['current_participants']) ?>/<?= escape($event['max_participants']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="user/events.php?register=<?= $event['id'] ?>" class="btn btn-primary btn-sm">
                                    Записаться
                                </a>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="login.php" class="btn btn-outline-primary btn-sm">Войдите для записи</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        На данный момент нет запланированных мероприятий.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>