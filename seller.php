<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем ID продавца из URL
$sellerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем информацию о продавце
$seller = getSellerById($pdo, $sellerId);

// Если продавец не найден, перенаправляем на страницу продавцов
if (!$seller) {
    header('Location: sellers.php');
    exit;
}

// Получаем товары продавца
$products = getProductsBySeller($pdo, $sellerId);

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
  <div class="mb-4">
    <a href="/sellers.php" class="text-zinc-400 hover:text-white transition-colors">
      ← Назад к продавцам
    </a>
  </div>
  
  <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 mb-8">
    <h1 class="text-3xl font-bold mb-2 text-white"><?php echo $seller['name']; ?></h1>
    <p class="text-zinc-400">Продавец на GameMarket</p>
  </div>
  
  <h2 class="text-2xl font-bold mb-4 text-white">Товары продавца</h2>
  
  <?php if (empty($products)): ?>
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
      <p class="text-zinc-300">У этого продавца пока нет товаров</p>
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

