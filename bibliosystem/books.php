<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Получаем книги из базы
try {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    
    $sql = "SELECT * FROM books WHERE available > 0";
    $params = [];
    
    if ($search) {
        $sql .= " AND (title LIKE ? OR author LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY title";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем категории для фильтра
    $categories_stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $books = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог книг - Библиотечная система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Библиотечная система</a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="index.php">Главная</a>
                <a class="nav-link active" href="books.php">Книги</a>
                <a class="nav-link" href="events.php">Мероприятия</a>
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
        <h1 class="mb-4">Каталог книг</h1>
        
        <!-- Поиск и фильтры -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Поиск по названию или автору..." 
                               value="<?= escape($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= escape($cat) ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= escape($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Найти</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Список книг -->
        <div class="row">
            <?php if ($books): ?>
                <?php foreach ($books as $book): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= escape($book['title']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= escape($book['author']) ?></h6>
                            <p class="card-text"><?= escape(substr($book['description'] ?? '', 0, 100)) ?>...</p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Год: <?= escape($book['year_published']) ?><br>
                                    Категория: <?= escape($book['category'] ?? 'Не указана') ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <span class="badge bg-success">Доступно: <?= escape($book['available']) ?> экз.</span>
                            </p>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="user/books.php?reserve=<?= $book['id'] ?>" class="btn btn-primary btn-sm">
                                    Забронировать
                                </a>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="login.php" class="btn btn-outline-primary btn-sm">Войдите для бронирования</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Книги не найдены. Попробуйте изменить параметры поиска.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>