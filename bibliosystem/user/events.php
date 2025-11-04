<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (isAdmin()) {
    header('Location: ../admin/');
    exit;
}

$user_id = $_SESSION['user_id'];

// Регистрация на мероприятие
if (isset($_GET['register'])) {
    $event_id = (int)$_GET['register'];
    
    // Проверяем возможность регистрации
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND event_date > NOW()");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($event && $event['current_participants'] < $event['max_participants']) {
        // Проверяем, не зарегистрирован ли уже
        $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ? AND status = 'registered'");
        $stmt->execute([$user_id, $event_id]);
        
        if (!$stmt->fetch()) {
            // Регистрируем
            $stmt = $pdo->prepare("INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $event_id]);
            
            // Увеличиваем счетчик участников
            $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants + 1 WHERE id = ?");
            $stmt->execute([$event_id]);
            
            $_SESSION['success'] = 'Вы успешно зарегистрированы на мероприятие!';
        } else {
            $_SESSION['error'] = 'Вы уже зарегистрированы на это мероприятие';
        }
    } else {
        $_SESSION['error'] = 'Регистрация на мероприятие невозможна';
    }
    
    header('Location: events.php');
    exit;
}

// Отмена регистрации
if (isset($_GET['cancel'])) {
    $registration_id = (int)$_GET['cancel'];
    
    $stmt = $pdo->prepare("SELECT event_id FROM event_registrations WHERE id = ? AND user_id = ?");
    $stmt->execute([$registration_id, $user_id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($registration) {
        // Отменяем регистрацию
        $stmt = $pdo->prepare("UPDATE event_registrations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$registration_id]);
        
        // Уменьшаем счетчик участников
        $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE id = ?");
        $stmt->execute([$registration['event_id']]);
        
        $_SESSION['success'] = 'Регистрация на мероприятие отменена';
    }
    
    header('Location: events.php');
    exit;
}

// Получаем зарегистрированные мероприятия пользователя
$stmt = $pdo->prepare("
    SELECT er.*, e.title, e.description, e.event_date, e.location, e.max_participants, e.current_participants
    FROM event_registrations er 
    JOIN events e ON er.event_id = e.id 
    WHERE er.user_id = ? 
    ORDER BY e.event_date ASC
");
$stmt->execute([$user_id]);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои мероприятия - Библиотечная система</title>
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
                    <a href="books.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-book"></i> Мои книги
                    </a>
                    <a href="events.php" class="list-group-item list-group-item-action active">
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
                            <i class="fas fa-calendar-alt"></i> Мои мероприятия
                        </h5>
                        <a href="../events.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Найти мероприятия
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
                        
                        <?php if ($registrations): ?>
                            <div class="row">
                                <?php foreach ($registrations as $reg): 
                                    $event_date = strtotime($reg['event_date']);
                                    $is_past = $event_date < time();
                                ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 <?= $is_past ? 'border-secondary' : '' ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= escape($reg['title']) ?></h5>
                                            <p class="card-text"><?= escape(substr($reg['description'], 0, 100)) ?>...</p>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> 
                                                    <?= date('d.m.Y H:i', $event_date) ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i> 
                                                    <?= escape($reg['location']) ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    Участники: <?= $reg['current_participants'] ?>/<?= $reg['max_participants'] ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <span class="badge bg-<?= 
                                                    $reg['status'] === 'registered' ? 'success' : 
                                                    ($reg['status'] === 'attended' ? 'primary' : 'secondary')
                                                ?>">
                                                    <?= $reg['status'] === 'registered' ? 'Зарегистрирован' : 
                                                         ($reg['status'] === 'attended' ? 'Посетил' : 'Отменено') ?>
                                                </span>
                                                
                                                <?php if ($is_past): ?>
                                                    <span class="badge bg-secondary">Завершено</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (!$is_past && $reg['status'] === 'registered'): ?>
                                            <div class="card-footer">
                                                <a href="events.php?cancel=<?= $reg['id'] ?>" class="btn btn-warning btn-sm"
                                                   onclick="return confirm('Отменить регистрацию на мероприятие?')">
                                                    <i class="fas fa-times"></i> Отменить
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Вы не зарегистрированы на мероприятия</p>
                                <a href="../events.php" class="btn btn-primary">Посмотреть мероприятия</a>
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