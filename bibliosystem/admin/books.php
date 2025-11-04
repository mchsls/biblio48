<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Добавление/редактирование книги
if ($_POST) {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $isbn = trim($_POST['isbn']);
    $year_published = (int)$_POST['year_published'];
    $quantity = (int)$_POST['quantity'];
    $category = trim($_POST['category']);
    
    if ($id) {
        // Обновление книги
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, description = ?, isbn = ?, year_published = ?, quantity = ?, category = ? WHERE id = ?");
        $stmt->execute([$title, $author, $description, $isbn, $year_published, $quantity, $category, $id]);
    } else {
        // Добавление новой книги
        $stmt = $pdo->prepare("INSERT INTO books (title, author, description, isbn, year_published, quantity, available, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $description, $isbn, $year_published, $quantity, $quantity, $category]);
    }
    
    header('Location: books.php');
    exit;
}

// Удаление книги
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
    header('Location: books.php');
    exit;
}

// Получение книги для редактирования
$edit_book = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получение всех книг
$stmt = $pdo->query("SELECT * FROM books ORDER BY title");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление книгами - Администратор</title>
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
                    <a href="books.php" class="list-group-item list-group-item-action active">
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
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book"></i> <?= $edit_book ? 'Редактирование книги' : 'Добавление новой книги' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_book): ?>
                                <input type="hidden" name="id" value="<?= $edit_book['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Название *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= $edit_book ? escape($edit_book['title']) : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="author" class="form-label">Автор *</label>
                                        <input type="text" class="form-control" id="author" name="author" 
                                               value="<?= $edit_book ? escape($edit_book['author']) : '' ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" 
                                               value="<?= $edit_book ? escape($edit_book['isbn']) : '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="year_published" class="form-label">Год издания</label>
                                        <input type="number" class="form-control" id="year_published" name="year_published" 
                                               value="<?= $edit_book ? escape($edit_book['year_published']) : '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Количество экземпляров *</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" 
                                               value="<?= $edit_book ? escape($edit_book['quantity']) : '1' ?>" required min="1">
                                    </div>
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Категория</label>
                                        <input type="text" class="form-control" id="category" name="category" 
                                               value="<?= $edit_book ? escape($edit_book['category']) : '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Описание</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?= $edit_book ? escape($edit_book['description']) : '' ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $edit_book ? 'Сохранить изменения' : 'Добавить книгу' ?>
                            </button>
                            
                            <?php if ($edit_book): ?>
                                <a href="books.php" class="btn btn-secondary">Отмена</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Список книг -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list"></i> Список книг (<?= count($books) ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($books): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Автор</th>
                                            <th>Год</th>
                                            <th>Доступно</th>
                                            <th>Категория</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?= escape($book['title']) ?></td>
                                            <td><?= escape($book['author']) ?></td>
                                            <td><?= escape($book['year_published']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $book['available'] > 0 ? 'success' : 'danger' ?>">
                                                    <?= $book['available'] ?>/<?= $book['quantity'] ?>
                                                </span>
                                            </td>
                                            <td><?= escape($book['category']) ?></td>
                                            <td>
                                                <a href="books.php?edit=<?= $book['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="books.php?delete=<?= $book['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Удалить книгу?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Книги не найдены</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>