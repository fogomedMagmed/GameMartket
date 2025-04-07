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

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем данные текущего пользователя
$user = getCurrentUser();

// Получаем заказы пользователя
$orders = [];
try {
    if ($pdo instanceof PDO) {
        // Проверяем, существует ли таблица orders
        $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
        if ($stmt->rowCount() > 0) {
            // Получаем заказы пользователя
            $stmt = $pdo->prepare("
                SELECT o.*, COUNT(oi.id) as items_count, SUM(oi.price * oi.quantity) as total_price
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $orders = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    // В случае ошибки просто показываем пустой список заказов
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-white">Мои заказы</h1>
    
    <?php if (empty($orders)): ?>
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
            <p class="text-zinc-300 mb-4">У вас пока нет заказов</p>
            <a href="/products.php" class="inline-block px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Перейти к товарам
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
                <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
                    <div class="p-4 bg-zinc-800 flex justify-between items-center">
                        <div>
                            <p class="text-zinc-400 text-sm">Заказ №<?php echo $order['id']; ?></p>
                            <p class="text-white font-medium">
                                <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-zinc-400 text-sm">Статус</p>
                            <p class="text-white font-medium">
                                <?php 
                                switch ($order['status']) {
                                    case 'pending':
                                        echo 'Ожидает оплаты';
                                        break;
                                    case 'paid':
                                        echo 'Оплачен';
                                        break;
                                    case 'completed':
                                        echo 'Выполнен';
                                        break;
                                    case 'cancelled':
                                        echo 'Отменен';
                                        break;
                                    default:
                                        echo 'В обработке';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-4">
                            <p class="text-white">
                                <span class="text-zinc-400">Товаров:</span> <?php echo $order['items_count']; ?>
                            </p>
                            <p class="text-white font-bold">
                                <span class="text-zinc-400">Сумма:</span> <?php echo $order['total_price']; ?> ₽
                            </p>
                        </div>
                        <a href="/order.php?id=<?php echo $order['id']; ?>" class="block w-full text-center py-2 px-4 bg-zinc-800 hover:bg-zinc-700 text-white rounded-md transition-colors">
                            Подробнее
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

