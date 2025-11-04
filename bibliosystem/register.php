<?php
// register.php - исправленная версия
require_once 'includes/config.php';

// Упрощенная функция аутентификации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        try {
            // Детальная проверка существующих пользователей
            $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing = $stmt->fetchAll();
            
            if ($existing) {
                $conflicts = [];
                foreach ($existing as $user) {
                    if ($user['username'] === $username) {
                        $conflicts[] = "логин '{$username}'";
                    }
                    if ($user['email'] === $email) {
                        $conflicts[] = "email '{$email}'";
                    }
                }
                $error = 'Пользователь с ' . implode(' и ', $conflicts) . ' уже существует';
            } else {
                // Создаем пользователя
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                    $success = 'Регистрация успешна! Теперь вы можете войти.';
                    // Очищаем форму
                    $_POST = [];
                } else {
                    $error = 'Ошибка при регистрации';
                }
            }
        } catch(PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Библиотечная система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .alert { margin-bottom: 20px; }
        .test-accounts { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Регистрация</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <strong>Ошибка:</strong> <?= htmlspecialchars($error) ?>
                                <br><small>Попробуйте другой логин или email</small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($success) ?>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-success">Войти в систему</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" id="registerForm">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Имя пользователя *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                           required minlength="3" maxlength="50">
                                    <div class="form-text">Минимум 3 символа</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">ФИО</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Телефон</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required minlength="6">
                                    <div class="form-text">Минимум 6 символов</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Подтверждение пароля *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                            </form>
                            
                            <div class="text-center mt-3">
                                <a href="login.php">Уже есть аккаунт? Войдите</a>
                            </div>
                            
                            <div class="test-accounts">
                                <h6>Тестовые данные для проверки:</h6>
                                <small>
                                    <strong>Администратор:</strong> admin / admin123<br>
                                    <strong>Для регистрации:</strong> Используйте уникальные логин и email
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Клиентская валидация паролей
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Пароль должен быть не менее 6 символов!');
                return false;
            }
        });
    </script>
</body>
</html>