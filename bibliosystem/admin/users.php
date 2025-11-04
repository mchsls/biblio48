<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Получаем всех пользователей
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM book_loans WHERE user_id = u.id) as total_loans,
           (SELECT COUNT(*) FROM book_loans WHERE user_id = u.id AND status = 'active') as active_loans
    FROM users u 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Изменение роли пользователя
if (isset($_GET['toggle_role'])) {
    $user_id = (int)$_GET['toggle_role'];
    $stmt = $pdo->prepare("UPDATE users SET role = IF(role = 'admin', 'user', 'admin') WHERE id = ? AND id != ?");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    header('Location: users.php');
    exit;
}

// Удаление пользователя
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    }
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - Администратор</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="index.php" class="list-group-item list-group-item-action">
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
                    <a href="users.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-users"></i> Пользователи
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users"></i> Управление пользователями (<?= count($users) ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($users): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Пользователь</th>
                                            <th>Email</th>
                                            <th>Роль</th>
                                            <th>Дата регистрации</th>
                                            <th>Бронирования</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <strong><?= escape($user['username']) ?></strong>
                                                <?php if ($user['full_name']): ?>
                                                    <br><small class="text-muted"><?= escape($user['full_name']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= escape($user['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                    <?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?>
                                                </span>
                                            </td>
                                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <small>
                                                    Всего: <?= $user['total_loans'] ?><br>
                                                    Активных: <?= $user['active_loans'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="users.php?toggle_role=<?= $user['id'] ?>" class="btn btn-sm btn-warning"
                                                       onclick="return confirm('Изменить роль пользователя?')">
                                                        <i class="fas fa-user-cog"></i>
                                                    </a>
                                                    <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Удалить пользователя?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Вы</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Пользователи не найдены</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>