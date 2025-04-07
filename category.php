<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем ID категории из URL
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о категории
$category = getCategoryById($pdo, $categoryId);

// Если категория не найдена, перенаправляем на страницу категорий
if (!$category) {
    header('Location: categories.php');
    exit;
}

// Получаем товары в этой категории
$products = getProductsByCategory($pdo, $categoryId);

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
  <div class="mb-4">
    <a href="/categories.php" class="text-zinc-400 hover:text-white transition-colors">
      ← Назад к категориям
    </a>
  </div>
  
  <h1 class="text-3xl font-bold mb-2 text-white"><?php echo $category['name']; ?></h1>
  <p class="text-zinc-400 mb-6"><?php echo $category['description']; ?></p>
  
  <?php if (empty($products)): ?>
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
      <p class="text-zinc-300 mb-4">В этой категории пока нет товаров</p>
      <a href="/products.php" class="inline-block px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
        Смотреть все товары
      </a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php foreach ($products as $product): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

