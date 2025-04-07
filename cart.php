<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Инициализируем корзину, если она еще не создана
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Обработка добавления товара в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Получаем информацию о товаре
    $product = getProductById($pdo, $productId);
    
    if ($product) {
        // Проверяем, есть ли уже такой товар в корзине
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // Если товар не найден в корзине, добавляем его
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        // Перенаправляем на страницу корзины с сообщением об успехе
        header('Location: cart.php?success=1');
        exit;
    }
}

// Обработка удаления товара из корзины
if (isset($_GET['remove']) && isset($_GET['id'])) {
    $removeId = (int)$_GET['id'];
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $removeId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Переиндексируем массив
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    // Перенаправляем на страницу корзины
    header('Location: cart.php');
    exit;
}

// Обработка очистки корзины
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    
    // Перенаправляем на страницу корзины
    header('Location: cart.php');
    exit;
}

// Обработка обновления количества товаров
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $quantity) {
        $id = (int)$id;
        $quantity = (int)$quantity;
        
        if ($quantity > 0) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] === $id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
        }
    }
    
    // Перенаправляем на страницу корзины
    header('Location: cart.php');
    exit;
}

// Рассчитываем общую стоимость корзины
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-white">Корзина</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-900/30 border border-green-800 text-green-200 px-4 py-3 rounded mb-4">
            Товар успешно добавлен в корзину
        </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
            <p class="text-zinc-300 mb-4">Ваша корзина пуста</p>
            <a href="/products.php" class="inline-block px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Перейти к товарам
            </a>
        </div>
    <?php else: ?>
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
            <form method="post" action="/cart.php">
                <table class="w-full">
                    <thead class="bg-zinc-800">
                        <tr>
                            <th class="py-3 px-4 text-left text-white">Товар</th>
                            <th class="py-3 px-4 text-center text-white">Цена</th>
                            <th class="py-3 px-4 text-center text-white">Количество</th>
                            <th class="py-3 px-4 text-center text-white">Сумма</th>
                            <th class="py-3 px-4 text-center text-white">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr class="border-t border-zinc-800">
                                <td class="py-4 px-4">
                                    <div class="flex items-center">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover rounded mr-4">
                                        <div>
                                            <h3 class="text-white font-medium"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center text-white"><?php echo $item['price']; ?> ₽</td>
                                <td class="py-4 px-4 text-center">
                                    <input 
                                        type="number" 
                                        name="quantity[<?php echo $item['id']; ?>]" 
                                        value="<?php echo $item['quantity']; ?>" 
                                        min="1" 
                                        class="w-16 bg-zinc-800 border border-zinc-700 rounded p-2 text-white text-center"
                                    >
                                </td>
                                <td class="py-4 px-4 text-center text-white"><?php echo $item['price'] * $item['quantity']; ?> ₽</td>
                                <td class="py-4 px-4 text-center">
                                    <a href="/cart.php?remove=1&id=<?php echo $item['id']; ?>" class="text-red-400 hover:text-red-300">
                                        Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="p-4 bg-zinc-800 flex justify-between items-center">
                    <div>
                        <button type="submit" name="update_cart" class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white rounded-md transition-colors">
                            Обновить корзину
                        </button>
                        <a href="/cart.php?clear=1" class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors">
                            Очистить корзину
                        </a>
                    </div>
                    <div class="text-right">
                        <p class="text-zinc-400 text-sm">Итого:</p>
                        <p class="text-white text-xl font-bold"><?php echo $totalPrice; ?> ₽</p>
                    </div>
                </div>
            </form>
            
            <div class="p-4 border-t border-zinc-800 flex justify-end">
                <a href="/checkout.php" class="px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Оформить заказ
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

