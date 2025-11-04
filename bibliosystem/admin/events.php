<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Добавление/редактирование мероприятия
if ($_POST) {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $max_participants = (int)$_POST['max_participants'];
    
    if ($id) {
        // Обновление мероприятия
        $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, max_participants = ? WHERE id = ?");
        $stmt->execute([$title, $description, $event_date, $location, $max_participants, $id]);
    } else {
        // Добавление нового мероприятия
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, max_participants) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $event_date, $location, $max_participants]);
    }
    
    header('Location: events.php');
    exit;
}

// Удаление мероприятия
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
    header('Location: events.php');
    exit;
}

// Получение мероприятия для редактирования
$edit_event = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получение всех мероприятий
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление мероприятиями - Администратор</title>
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
                    <a href="events.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-calendar-alt"></i> Мероприятия
                    </a>
                    <a href="news.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-newspaper"></i> Новости
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Пользователи
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt"></i> <?= $edit_event ? 'Редактирование мероприятия' : 'Добавление мероприятия' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Название *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= $edit_event ? escape($edit_event['title']) : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="event_date" class="form-label">Дата и время *</label>
                                        <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                                               value="<?= $edit_event ? date('Y-m-d\TH:i', strtotime($edit_event['event_date'])) : '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Место проведения *</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?= $edit_event ? escape($edit_event['location']) : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="max_participants" class="form-label">Макс. участников *</label>
                                        <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                               value="<?= $edit_event ? escape($edit_event['max_participants']) : '50' ?>" required min="1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Описание</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?= $edit_event ? escape($edit_event['description']) : '' ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $edit_event ? 'Сохранить изменения' : 'Добавить мероприятие' ?>
                            </button>
                            
                            <?php if ($edit_event): ?>
                                <a href="events.php" class="btn btn-secondary">Отмена</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Список мероприятий -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Список мероприятий (<?= count($events) ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($events): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Дата</th>
                                            <th>Место</th>
                                            <th>Участники</th>
                                            <th>Статус</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): 
                                            $is_past = strtotime($event['event_date']) < time();
                                        ?>
                                        <tr>
                                            <td><?= escape($event['title']) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($event['event_date'])) ?></td>
                                            <td><?= escape($event['location']) ?></td>
                                            <td><?= $event['current_participants'] ?>/<?= $event['max_participants'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $is_past ? 'secondary' : 'success' ?>">
                                                    <?= $is_past ? 'Завершено' : 'Активно' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="events.php?edit=<?= $event['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="events.php?delete=<?= $event['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Удалить мероприятие?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Мероприятия не найдены</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>