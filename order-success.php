<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получаем ID заказа из URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Если ID заказа не передан, перенаправляем на страницу заказов
if ($orderId === 0) {
    header('Location: orders.php');
    exit;
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
        <div class="mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold mb-4 text-white">Заказ успешно оформлен!</h1>
        
        <p class="text-zinc-300 mb-6">
            Спасибо за ваш заказ! Номер вашего заказа: <strong>#<?php echo $orderId; ?></strong>
        </p>
        
        <p class="text-zinc-300 mb-8">
            Мы отправили подтверждение на ваш email. Вы можете отслеживать статус заказа в разделе "Мои заказы".
        </p>
        
        <div class="flex justify-center space-x-4">
            <a href="/orders.php" class="px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Мои заказы
            </a>
            <a href="/products.php" class="px-6 py-3 bg-zinc-700 text-white font-medium rounded-lg hover:bg-zinc-600 transition-colors">
                Продолжить покупки
            </a>
        </div>
    </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

