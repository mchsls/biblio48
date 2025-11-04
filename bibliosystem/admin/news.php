<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Добавление/редактирование новости
if ($_POST) {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    if ($id) {
        // Обновление новости
        $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, is_published = ? WHERE id = ?");
        $stmt->execute([$title, $content, $is_published, $id]);
    } else {
        // Добавление новой новости
        $stmt = $pdo->prepare("INSERT INTO news (title, content, is_published) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $is_published]);
    }
    
    header('Location: news.php');
    exit;
}

// Удаление новости
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
    header('Location: news.php');
    exit;
}

// Переключение статуса публикации
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE news SET is_published = NOT is_published WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: news.php');
    exit;
}

// Получение новости для редактирования
$edit_news = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_news = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получение всех новостей
$stmt = $pdo->query("SELECT * FROM news ORDER BY published_at DESC");
$all_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление новостями - Администратор</title>
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
                    <a href="news.php" class="list-group-item list-group-item-action active">
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
                            <i class="fas fa-newspaper"></i> <?= $edit_news ? 'Редактирование новости' : 'Добавление новости' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_news): ?>
                                <input type="hidden" name="id" value="<?= $edit_news['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Заголовок *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= $edit_news ? escape($edit_news['title']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Содержание *</label>
                                <textarea class="form-control" id="content" name="content" rows="8" required><?= $edit_news ? escape($edit_news['content']) : '' ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_published" name="is_published" 
                                       <?= $edit_news ? ($edit_news['is_published'] ? 'checked' : '') : 'checked' ?>>
                                <label class="form-check-label" for="is_published">Опубликовать сразу</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $edit_news ? 'Сохранить изменения' : 'Добавить новость' ?>
                            </button>
                            
                            <?php if ($edit_news): ?>
                                <a href="news.php" class="btn btn-secondary">Отмена</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Список новостей -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Список новостей (<?= count($all_news) ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($all_news): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Заголовок</th>
                                            <th>Дата публикации</th>
                                            <th>Статус</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_news as $news): ?>
                                        <tr>
                                            <td><?= escape($news['title']) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($news['published_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $news['is_published'] ? 'success' : 'secondary' ?>">
                                                    <?= $news['is_published'] ? 'Опубликовано' : 'Черновик' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="news.php?edit=<?= $news['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="news.php?toggle=<?= $news['id'] ?>" class="btn btn-sm btn-<?= $news['is_published'] ? 'secondary' : 'success' ?>">
                                                    <i class="fas fa-<?= $news['is_published'] ? 'eye-slash' : 'eye' ?>"></i>
                                                </a>
                                                <a href="news.php?delete=<?= $news['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Удалить новость?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Новости не найдены</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>