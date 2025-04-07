<?php
// Начинаем сессию для хранения данных пользователя
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем ID товара из URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о товаре
$product = getProductById($pdo, $productId);

// Если товар не найден, перенаправляем на страницу 404
if (!$product) {
    header('Location: 404.php');
    exit;
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
  <div class="mb-4">
    <a href="/products.php" class="text-zinc-400 hover:text-white transition-colors">
      ← Назад к товарам
    </a>
  </div>
  
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="relative h-[400px] w-full rounded-lg overflow-hidden bg-zinc-800">
      <img 
        src="<?php echo $product['image']; ?>" 
        alt="<?php echo $product['name']; ?>" 
        class="object-cover w-full h-full"
      >
    </div>
    
    <div class="bg-zinc-900 p-6 rounded-lg border border-zinc-800">
      <h1 class="text-3xl font-bold mb-2 text-white"><?php echo $product['name']; ?></h1>
      <p class="text-xl font-bold text-white mb-4"><?php echo $product['price']; ?> ₽</p>
      <p class="text-zinc-300 mb-6"><?php echo $product['description']; ?></p>
      
      <div class="mb-6 border-t border-zinc-800 pt-4">
        <h2 class="font-medium mb-2 text-white">Продавец</h2>
        <p class="text-zinc-300"><?php echo $product['seller_name']; ?></p>
      </div>
      
      <div class="mb-6 border-t border-zinc-800 pt-4">
        <h2 class="font-medium mb-2 text-white">Категория</h2>
        <p class="text-zinc-300"><?php echo $product['category_name']; ?></p>
      </div>
      
      <form action="/cart.php" method="post">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <button type="submit" name="add_to_cart" class="w-full py-6 text-lg bg-white hover:bg-gray-200 text-black rounded-md flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          Добавить в корзину
        </button>
      </form>
    </div>
  </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

