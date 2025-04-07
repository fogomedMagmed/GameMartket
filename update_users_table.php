<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаемся к базе данных
require_once 'config/database.php';

// Проверяем, что $pdo - это объект PDO
if (!($pdo instanceof PDO)) {
    die('Ошибка подключения к базе данных');
}

try {
    // Проверяем, существует ли столбец avatar в таблице users
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Добавляем столбец avatar в таблицу users
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT 'assets/images/avatars/default.png'");
        echo "Столбец 'avatar' успешно добавлен в таблицу 'users'.<br>";
        
        // Создаем директорию для аватаров, если она не существует
        if (!file_exists('assets/images/avatars/')) {
            mkdir('assets/images/avatars/', 0777, true);
            echo "Директория для аватаров успешно создана.<br>";
        }
    } else {
        echo "Столбец 'avatar' уже существует в таблице 'users'.<br>";
    }
    
    echo "Обновление структуры базы данных завершено успешно.";
} catch (PDOException $e) {
    die("Ошибка при обновлении структуры базы данных: " . $e->getMessage());
}
?>

