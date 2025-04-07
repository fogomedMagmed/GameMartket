<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем параметры подключения из формы
    $host = $_POST['host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $charset = 'utf8mb4';
    
    // Проверяем, что все необходимые поля заполнены
    if (empty($db_name) || empty($username)) {
        $error = "Пожалуйста, заполните все обязательные поля.";
    } else {
        try {
            // Создаем DSN для PDO
            $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
            
            // Опции PDO для обработки ошибок и настройки соединения
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Создаем экземпляр PDO
            $pdo = new PDO($dsn, $username, $password, $options);
            
            // Читаем SQL-запросы из файла
            $sqlFile = 'database.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                
                // Разделяем SQL-запросы по точке с запятой и выполняем их по одному
                $queries = explode(';', $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $pdo->exec($query);
                    }
                }
                
                $success = "База данных успешно установлена!";
                
                // Сохраняем параметры подключения в config/database.php
                $configDir = 'config';
                if (!is_dir($configDir)) {
                    mkdir($configDir, 0755, true);
                }
                
                $configContent = "<?php
// Параметры подключения к базе данных
\$host = '$host';
\$db_name = '$db_name';
\$username = '$username';
\$password = '$password';
\$charset = '$charset';

try {
    // Создаем DSN для PDO
    \$dsn = \"mysql:host=\$host;dbname=\$db_name;charset=\$charset\";
    
    // Опции PDO для обработки ошибок и настройки соединения
    \$options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    // Создаем экземпляр PDO
    \$pdo = new PDO(\$dsn, \$username, \$password, \$options);
    
    // Возвращаем объект подключения
    return \$pdo;
} catch (PDOException \$e) {
    // В случае ошибки выводим сообщение и прекращаем выполнение скрипта
    echo \"Ошибка подключения к базе данных: \" . \$e->getMessage();
    // Возвращаем null вместо прекращения выполнения скрипта
    return null;
}
?>";
                
                file_put_contents("$configDir/database.php", $configContent);
                
                $configSuccess = "Файл конфигурации config/database.php успешно создан!";
            } else {
                $error = "Файл database.sql не найден.";
            }
        } catch (PDOException $e) {
            // В случае ошибки выводим сообщение
            $error = "Ошибка подключения к базе данных: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка GameMarket</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: #f44336;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success {
            color: #4CAF50;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .note {
            background-color: #fffde7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border-left: 4px solid #fbc02d;
        }
    </style>
</head>
<body>
    <h1>Установка GameMarket</h1>
    
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($configSuccess)): ?>
            <div class="success"><?php echo $configSuccess; ?></div>
        <?php endif; ?>
        
        <?php if (!isset($success)): ?>
            <div class="note">
                <p><strong>Примечание:</strong> Перед установкой убедитесь, что:</p>
                <ol>
                    <li>У вас есть доступ к базе данных MySQL</li>
                    <li>База данных уже создана</li>
                    <li>Пользователь имеет права на создание таблиц в этой базе данных</li>
                </ol>
            </div>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="host">Хост базы данных:</label>
                    <input type="text" id="host" name="host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Имя базы данных:</label>
                    <input type="text" id="db_name" name="db_name" value="f1103515_fogomed" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" value="f1103515_fogomed" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" value="29082010Vova!Vova">
                </div>
                
                <button type="submit">Установить</button>
            </form>
        <?php else: ?>
            <p>После установки вы можете войти в систему, используя следующие учетные данные:</p>
            <ul>
                <li><strong>Администратор:</strong> admin@gamemarket.ru / password</li>
                <li><strong>Продавец:</strong> seller@gamemarket.ru / password</li>
                <li><strong>Покупатель:</strong> buyer@gamemarket.ru / password</li>
            </ul>
            <p>Теперь вы можете <a href="index.php">перейти на главную страницу</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>

