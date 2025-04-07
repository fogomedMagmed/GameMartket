<?php
// Начинаем сессию для хранения данных пользователя
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';
require_once 'includes/recaptcha.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
  header('Location: index.php');
  exit;
}

$error = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
  
  // Проверяем reCAPTCHA
  if (!verifyReCaptcha($recaptchaResponse, $_SERVER['REMOTE_ADDR'])) {
      $error = 'Пожалуйста, подтвердите, что вы не робот';
  } else {
      // Авторизуем пользователя
      $result = loginUser($pdo, $email, $password);
      
      if ($result['success']) {
          // Перенаправляем на главную страницу или на страницу, с которой пришел пользователь
          $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
          header('Location: ' . $redirect);
          exit;
      } else {
          $error = $result['message'];
      }
  }
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-md">
<div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 shadow-md">
  <h1 class="text-2xl font-bold mb-6 text-center text-white">Вход в аккаунт</h1>
  
  <?php if ($error): ?>
    <div class="bg-red-900/30 border border-red-800 text-red-200 px-4 py-3 rounded mb-4">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>
  
  <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="space-y-4">
    <div class="space-y-2">
      <label for="email" class="text-zinc-300">Email</label>
      <input
        id="email"
        name="email"
        type="email"
        required
        class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
      >
    </div>
    
    <div class="space-y-2">
      <label for="password" class="text-zinc-300">Пароль</label>
      <input
        id="password"
        name="password"
        type="password"
        required
        class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
      >
    </div>
    
    <?php echo getReCaptchaHtml(); ?>
    
    <button type="submit" class="w-full bg-white hover:bg-gray-200 text-black py-2 px-4 rounded-md">Войти</button>
  </form>
  
  <div class="mt-4 text-center text-zinc-400">
    <p>
      Нет аккаунта?
      <a href="/register.php" class="text-white hover:underline">
        Зарегистрироваться
      </a>
    </p>
  </div>
</div>
</div>

<!-- Подключаем скрипт reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>