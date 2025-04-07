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

// Читаем SQL-запросы из файлов
$sqlFiles = [
    'database_update_chat.sql',
    'database_update_smart_bot.sql'
];

$success = true;
$messages = [];

foreach ($sqlFiles as $sqlFile) {
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Разделяем SQL-запросы по точке с запятой и выполняем их по одному
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                try {
                    $pdo->exec($query);
                    $messages[] = "Успешно выполнен запрос: " . substr($query, 0, 50) . "...";
                } catch (PDOException $e) {
                    $success = false;
                    $messages[] = "Ошибка при выполнении запроса: " . $e->getMessage();
                }
            }
        }
    } else {
        $success = false;
        $messages[] = "Файл $sqlFile не найден";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка системы чатов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .success {
            color: #4CAF50;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            color: #f44336;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .message {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <h1>Установка системы чатов</h1>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success">
                <p>Система чатов успешно установлена!</p>
            </div>
        <?php else: ?>
            <div class="error">
                <p>При установке системы чатов возникли ошибки.</p>
            </div>
        <?php endif; ?>
        
        <h2>Результаты установки:</h2>
        <div>
            <?php foreach ($messages as $message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endforeach; ?>
        </div>
        
        <p>Теперь вы можете <a href="/chat.php">перейти на страницу чата</a> или <a href="/index.php">вернуться на главную страницу</a>.</p>
    </div>
</body>
</html>

