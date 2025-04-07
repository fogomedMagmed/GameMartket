<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем всех продавцов
$sellers = getAllSellers($pdo);

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-6 text-white">Продавцы</h1>
  
  <?php if (empty($sellers)): ?>
    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
      <p class="text-zinc-300">На платформе пока нет продавцов</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php foreach ($sellers as $seller): ?>
        <a href="seller.php?id=<?php echo $seller['id']; ?>" class="block">
          <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 hover:border-zinc-700 transition-colors">
            <h2 class="text-xl font-bold mb-2 text-white"><?php echo $seller['name']; ?></h2>
            <p class="text-zinc-400">Продавец на GameMarket</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

