<?php
// check_functions.php - проверка функций
session_start();

echo "<h3>🔧 Проверка функций системы</h3>";

// Проверяем config.php
require_once 'includes/config.php';
echo "✅ config.php загружен<br>";

// Проверяем auth.php
require_once 'includes/auth.php';
echo "✅ auth.php загружен<br>";

// Проверяем функции
echo "Проверка функций:<br>";
echo "- isLoggedIn(): " . (function_exists('isLoggedIn') ? '✅ существует' : '❌ отсутствует') . "<br>";
echo "- isAdmin(): " . (function_exists('isAdmin') ? '✅ существует' : '❌ отсутствует') . "<br>";
echo "- escape(): " . (function_exists('escape') ? '✅ существует' : '❌ отсутствует') . "<br>";

// Тестируем функцию escape
if (function_exists('escape')) {
    $test_string = "<script>alert('test')</script>";
    $result = escape($test_string);
    echo "- escape() работает: " . ($result === "&lt;script&gt;alert('test')&lt;/script&gt;" ? '✅ да' : '❌ нет') . "<br>";
}

echo "<h3>🎉 Проверка завершена!</h3>";
echo "<a href='user/'>Проверить личный кабинет</a>";
?>