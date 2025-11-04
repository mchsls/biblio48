<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (isAdmin()) {
    header('Location: ../admin/');
    exit;
}

$user_id = $_SESSION['user_id'];

// Бронирование книги
if (isset($_GET['reserve'])) {
    $book_id = (int)$_GET['reserve'];
    
    // Проверяем доступность книги
    $stmt = $pdo->prepare("SELECT available FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($book && $book['available'] > 0) {
        // Создаем бронирование
        $loan_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+14 days')); // 14 дней на возврат
        
        $stmt = $pdo->prepare("INSERT INTO book_loans (user_id, book_id, loan_date, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $book_id, $loan_date, $due_date]);
        
        // Уменьшаем количество доступных книг
        $stmt = $pdo->prepare("UPDATE books SET available = available - 1 WHERE id = ?");
        $stmt->execute([$book_id]);
        
        $_SESSION['success'] = 'Книга успешно забронирована!';
    } else {
        $_SESSION['error'] = 'Книга недоступна для бронирования';
    }
    
    header('Location: books.php');
    exit;
}

// Возврат книги
if (isset($_GET['return'])) {
    $loan_id = (int)$_GET['return'];
    
    // Получаем информацию о бронировании
    $stmt = $pdo->prepare("SELECT book_id FROM book_loans WHERE id = ? AND user_id = ?");
    $stmt->execute([$loan_id, $user_id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($loan) {
        // Помечаем как возвращенную
        $return_date = date('Y-m-d');
        $stmt = $pdo->prepare("UPDATE book_loans SET return_date = ?, status = 'returned' WHERE id = ?");
        $stmt->execute([$return_date, $loan_id]);
        
        // Увеличиваем количество доступных книг
        $stmt = $pdo->prepare("UPDATE books SET available = available + 1 WHERE id = ?");
        $stmt->execute([$loan['book_id']]);
        
        $_SESSION['success'] = 'Книга успешно возвращена!';
    }
    
    header('Location: books.php');
    exit;
}

// Получаем историю бронирований пользователя
$stmt = $pdo->prepare("
    SELECT bl.*, b.title, b.author, b.isbn,
           CASE 
               WHEN bl.return_date IS NOT NULL THEN 'returned'
               WHEN bl.due_date < CURDATE() THEN 'overdue' 
               ELSE 'active' 
           END as loan_status
    FROM book_loans bl 
    JOIN books b ON bl.book_id = b.id 
    WHERE bl.user_id = ? 
    ORDER BY bl.created_at DESC
");
$stmt->execute([$user_id]);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои книги - Библиотечная система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user"></i> Личный кабинет
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
                <div class="card">
                    <div class="card-body text-center">
                        <h5><?= escape($_SESSION['full_name'] ?: $_SESSION['username']) ?></h5>
                        <p class="text-muted">Пользователь</p>
                    </div>
                </div>
                <div class="list-group mt-3">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt"></i> Обзор
                    </a>
                    <a href="books.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-book"></i> Мои книги
                    </a>
                    <a href="events.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt"></i> Мои мероприятия
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-edit"></i> Редактировать профиль
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book"></i> История бронирования книг
                        </h5>
                        <a href="../books.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Забронировать книгу
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <?php if ($loans): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Книга</th>
                                            <th>Автор</th>
                                            <th>Дата выдачи</th>
                                            <th>Срок возврата</th>
                                            <th>Дата возврата</th>
                                            <th>Статус</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loans as $loan): 
                                            $due_date = strtotime($loan['due_date']);
                                            $today = time();
                                            $days_left = floor(($due_date - $today) / (60 * 60 * 24));
                                        ?>
                                        <tr>
                                            <td><?= escape($loan['title']) ?></td>
                                            <td><?= escape($loan['author']) ?></td>
                                            <td><?= date('d.m.Y', strtotime($loan['loan_date'])) ?></td>
                                            <td><?= date('d.m.Y', strtotime($loan['due_date'])) ?></td>
                                            <td>
                                                <?= $loan['return_date'] ? date('d.m.Y', strtotime($loan['return_date'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($loan['loan_status'] === 'active'): ?>
                                                    <span class="badge bg-success">Активно 
                                                        (<?= $days_left > 0 ? "осталось $days_left дн." : 'сегодня' ?>)
                                                    </span>
                                                <?php elseif ($loan['loan_status'] === 'overdue'): ?>
                                                    <span class="badge bg-danger">Просрочено</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Возвращено</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($loan['loan_status'] === 'active'): ?>
                                                    <a href="books.php?return=<?= $loan['id'] ?>" class="btn btn-sm btn-success"
                                                       onclick="return confirm('Подтвердите возврат книги')">
                                                        <i class="fas fa-undo"></i> Вернуть
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">У вас еще нет бронирований</p>
                                <a href="../books.php" class="btn btn-primary">Перейти к каталогу книг</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>