<?php
// Начинаем сессию для хранения данных пользователя
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Если нет данных для верификации, перенаправляем на страницу регистрации
if (!isset($_SESSION['verification'])) {
    header('Location: register.php');
    exit;
}

// Проверяем, не истек ли срок действия кода
if ($_SESSION['verification']['expires'] < time()) {
    // Удаляем данные верификации
    unset($_SESSION['verification']);
    
    // Перенаправляем на страницу регистрации с сообщением об ошибке
    header('Location: register.php?error=expired');
    exit;
}

$error = '';
$success = '';

// Обработка формы подтверждения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    // Проверяем код
    if ($code == $_SESSION['verification']['code']) {
        // Код верный, регистрируем пользователя
        $name = $_SESSION['verification']['name'];
        $email = $_SESSION['verification']['email'];
        $password = $_SESSION['verification']['password'];
        $role = $_SESSION['verification']['role'];
        
        // Регистрируем пользователя
        $result = registerUser($pdo, $name, $email, $password, $role);
        
        if ($result['success']) {
            // Удаляем данные верификации
            unset($_SESSION['verification']);
            
            // Авторизуем пользователя
            $loginResult = loginUser($pdo, $email, $password);
            
            if ($loginResult['success']) {
                // Перенаправляем на главную страницу
                header('Location: index.php');
                exit;
            } else {
                // Если не удалось авторизовать, перенаправляем на страницу входа
                header('Location: login.php?success=registered');
                exit;
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Неверный код подтверждения';
    }
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-md">
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-white">Подтверждение регистрации</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-900/30 border border-red-800 text-red-200 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <p class="text-zinc-300 mb-4">
            Код подтверждения был отправлен на ваш email: <strong><?php echo htmlspecialchars($_SESSION['verification']['email']); ?></strong>
        </p>
        
        <p class="text-zinc-300 mb-4">
            Для демонстрации, ваш код: <strong><?php echo $_SESSION['verification']['code']; ?></strong>
        </p>
        
        <form method="post" action="/verify.php" class="space-y-4">
            <div class="space-y-2">
                <label for="code" class="text-zinc-300">Код подтверждения</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    required
                    class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                >
            </div>
            
            <button type="submit" class="w-full bg-white hover:bg-gray-200 text-black py-2 px-4 rounded-md">Подтвердить</button>
        </form>
        
        <div class="mt-4 text-center text-zinc-400">
            <p>
                <a href="/register.php" class="text-white hover:underline">
                    Вернуться к регистрации
                </a>
            </p>
        </div>
    </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>