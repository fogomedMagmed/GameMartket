<?php
// Начинаем сессию для хранения данных пользователя
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';
require_once 'includes/recaptcha.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

$error = '';
$success = '';

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  $role = $_POST['role'] ?? 'buyer';
  $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
  
  // Проверка reCAPTCHA
  if (!verifyReCaptcha($recaptchaResponse, $_SERVER['REMOTE_ADDR'])) {
      $error = 'Пожалуйста, подтвердите, что вы не робот';
  }
  // Проверка паролей
  else if ($password !== $confirmPassword) {
      $error = 'Пароли не совпадают';
  } else {
      // Генерируем код подтверждения
      $verificationCode = rand(100000, 999999);
      
      // Сохраняем код в сессии
      $_SESSION['verification'] = [
          'name' => $name,
          'email' => $email,
          'password' => $password,
          'role' => $role,
          'code' => $verificationCode,
          'expires' => time() + 3600 // Код действителен 1 час
      ];
      
      // Отправляем код на email (в реальном проекте)
      // mail($email, 'Код подтверждения регистрации', "Ваш код подтверждения: $verificationCode");
      
      // Для демонстрации просто показываем код
      $success = "Код подтверждения отправлен на ваш email. Для демонстрации, ваш код: $verificationCode";
      
      // Перенаправляем на страницу подтверждения
      header('Location: verify.php');
      exit;
  }
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-md">
<div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 shadow-md">
  <h1 class="text-2xl font-bold mb-6 text-center text-white">Регистрация</h1>
  
  <?php if ($error): ?>
    <div class="bg-red-900/30 border border-red-800 text-red-200 px-4 py-3 rounded mb-4">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>
  
  <?php if ($success): ?>
    <div class="bg-green-900/30 border border-green-800 text-green-200 px-4 py-3 rounded mb-4">
      <?php echo $success; ?>
    </div>
  <?php else: ?>
    <form method="post" action="/register.php" class="space-y-4">
      <div class="space-y-2">
        <label for="name" class="text-zinc-300">Имя</label>
        <input
          id="name"
          name="name"
          type="text"
          required
          class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
        >
      </div>
      
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
      
      <div class="space-y-2">
        <label for="confirm_password" class="text-zinc-300">Подтвердите пароль</label>
        <input
          id="confirm_password"
          name="confirm_password"
          type="password"
          required
          class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
        >
      </div>
      
      <div class="space-y-2">
        <label class="text-zinc-300">Тип аккаунта</label>
        <div class="flex gap-4">
          <label class="flex items-center space-x-2">
            <input type="radio" name="role" value="buyer" checked class="bg-zinc-800 border-zinc-700">
            <span class="text-zinc-300">Покупатель</span>
          </label>
          <label class="flex items-center space-x-2">
            <input type="radio" name="role" value="seller" class="bg-zinc-800 border-zinc-700">
            <span class="text-zinc-300">Продавец</span>
          </label>
        </div>
      </div>
      
      <?php echo getReCaptchaHtml(); ?>
      
      <button type="submit" class="w-full bg-white hover:bg-gray-200 text-black py-2 px-4 rounded-md">Зарегистрироваться</button>
    </form>
    
    <div class="mt-4 text-center text-zinc-400">
      <p>
        Уже есть аккаунт?
        <a href="/login.php" class="text-white hover:underline">
          Войти
        </a>
      </p>
    </div>
  <?php endif; ?>
</div>
</div>

<!-- Подключаем скрипт reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>