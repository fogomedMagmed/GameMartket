<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Проверяем, есть ли товары в корзине
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Рассчитываем общую стоимость корзины
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Обработка формы оформления заказа
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Проверяем, авторизован ли пользователь
    if (!isLoggedIn()) {
        $error = 'Для оформления заказа необходимо авторизоваться';
    } else {
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Получаем ID пользователя
            $userId = $_SESSION['user']['id'];
            
            // Создаем заказ
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'pending')");
            $stmt->execute([$userId]);
            $orderId = $pdo->lastInsertId();
            
            // Добавляем товары в заказ
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
            
            foreach ($_SESSION['cart'] as $item) {
                $stmt->execute([$orderId, $item['id'], $item['price'], $item['quantity']]);
            }
            
            // Завершаем транзакцию
            $pdo->commit();
            
            // Очищаем корзину
            $_SESSION['cart'] = [];
            
            // Перенаправляем на страницу успешного оформления заказа
            header('Location: order-success.php?id=' . $orderId);
            exit;
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $pdo->rollBack();
            $error = 'Ошибка при оформлении заказа: ' . $e->getMessage();
        }
    }
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-white">Оформление заказа</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-900/30 border border-red-800 text-red-200 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-2">
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 text-white">Информация о заказе</h2>
                
                <?php if (!isLoggedIn()): ?>
                    <div class="bg-zinc-800 p-4 rounded mb-4">
                        <p class="text-zinc-300 mb-2">Для оформления заказа необходимо авторизоваться</p>
                        <div class="flex space-x-4">
                            <a href="/login.php?redirect=checkout.php" class="px-4 py-2 bg-white text-black rounded-md hover:bg-gray-200 transition-colors">
                                Войти
                            </a>
                            <a href="/register.php?redirect=checkout.php" class="px-4 py-2 bg-zinc-700 text-white rounded-md hover:bg-zinc-600 transition-colors">
                                Зарегистрироваться
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="post" action="/checkout.php" class="space-y-4">
                        <div class="space-y-2">
                            <label for="name" class="text-zinc-300">Имя</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>"
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
                                value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>"
                                required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                            >
                        </div>
                        
                        <div class="space-y-2">
                            <label for="phone" class="text-zinc-300">Телефон</label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                required
                                class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                            >
                        </div>
                        
                        <div class="space-y-2">
                            <label for="comment" class="text-zinc-300">Комментарий к заказу</label>
                            <textarea
                                id="comment"
                                name="comment"
                                rows="3"
                                class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                            ></textarea>
                        </div>
                        
                        <button type="submit" name="place_order" class="w-full bg-white hover:bg-gray-200 text-black py-3 px-4 rounded-md font-medium">
                            Оформить заказ
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 text-white">Ваш заказ</h2>
                
                <div class="space-y-4">
                    <div class="border-b border-zinc-800 pb-4">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span class="text-white"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="text-zinc-500"> × <?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="text-white"><?php echo $item['price'] * $item['quantity']; ?> ₽</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="flex justify-between items-center font-bold">
                        <span class="text-white">Итого:</span>
                        <span class="text-white text-xl"><?php echo $totalPrice; ?> ₽</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

