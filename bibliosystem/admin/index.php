<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Статистика
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM books");
$total_books = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE event_date > NOW()");
$active_events = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM book_loans WHERE status = 'active'");
$active_loans = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Последние бронирования
$stmt = $pdo->query("
    SELECT bl.*, u.username, u.full_name, b.title 
    FROM book_loans bl 
    JOIN users u ON bl.user_id = u.id 
    JOIN books b ON bl.book_id = b.id 
    ORDER BY bl.created_at DESC 
    LIMIT 5
");
$recent_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления - Администратор</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog"></i> Панель управления
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../"><i class="fas fa-home"></i> На сайт</a>
                <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Боковое меню -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="index.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt"></i> Дашборд
                    </a>
                    <a href="books.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-book"></i> Управление книгами
                    </a>
                    <a href="events.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt"></i> Мероприятия
                    </a>
                    <a href="news.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-newspaper"></i> Новости
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Пользователи
                    </a>
                    <a href="loans.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-exchange-alt"></i> Бронирования
                    </a>
                </div>
            </div>

            <!-- Контент -->
            <div class="col-md-9">
                <!-- Статистика -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $total_users ?></h4>
                                        <p>Пользователей</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $total_books ?></h4>
                                        <p>Книг в каталоге</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $active_events ?></h4>
                                        <p>Активных мероприятий</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?= $active_loans ?></h4>
                                        <p>Активных бронирований</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exchange-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Последние бронирования -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock"></i> Последние бронирования
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_loans): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Пользователь</th>
                                            <th>Книга</th>
                                            <th>Дата выдачи</th>
                                            <th>Срок возврата</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_loans as $loan): ?>
                                        <tr>
                                            <td><?= escape($loan['full_name'] ?: $loan['username']) ?></td>
                                            <td><?= escape($loan['title']) ?></td>
                                            <td><?= date('d.m.Y', strtotime($loan['loan_date'])) ?></td>
                                            <td><?= date('d.m.Y', strtotime($loan['due_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $loan['status'] === 'active' ? 'success' : 
                                                    ($loan['status'] === 'overdue' ? 'danger' : 'secondary')
                                                ?>">
                                                    <?= $loan['status'] === 'active' ? 'Активно' : 
                                                         ($loan['status'] === 'overdue' ? 'Просрочено' : 'Возвращено') ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Нет активных бронирований</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>