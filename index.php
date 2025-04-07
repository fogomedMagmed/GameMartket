<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Начинаем сессию для хранения данных пользователя
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаем файл с данными (временное решение)
include 'includes/data.php';

// Пытаемся подключиться к базе данных
try {
    $pdo = require 'config/database.php';
    
    // Проверяем, что $pdo - это объект PDO
    if ($pdo instanceof PDO) {
        // Получаем популярные товары (первые 8)
        $popularProducts = getAllProducts($pdo, 8);
        
        // Получаем все категории
        $categories = getAllCategories($pdo);
    } else {
        // Если $pdo не является объектом PDO, используем данные из файла
        $popularProducts = array_slice($products, 0, 8);
        $categories = [
            ['id' => 1, 'name' => 'Аккаунты', 'description' => 'Игровые аккаунты с прокачанными персонажами'],
            ['id' => 2, 'name' => 'Игровая валюта', 'description' => 'Внутриигровая валюта для различных игр'],
            ['id' => 3, 'name' => 'Услуги', 'description' => 'Услуги по прокачке, бусту и помощи в играх'],
            ['id' => 4, 'name' => 'Предметы', 'description' => 'Внутриигровые предметы, скины и другие виртуальные товары']
        ];
    }
} catch (Exception $e) {
    // Если не удалось подключиться к базе данных, используем данные из файла
    $popularProducts = array_slice($products, 0, 8);
    $categories = [
        ['id' => 1, 'name' => 'Аккаунты', 'description' => 'Игровые аккаунты с прокачанными персонажами'],
        ['id' => 2, 'name' => 'Игровая валюта', 'description' => 'Внутриигровая валюта для различных игр'],
        ['id' => 3, 'name' => 'Услуги', 'description' => 'Услуги по прокачке, бусту и помощи в играх'],
        ['id' => 4, 'name' => 'Предметы', 'description' => 'Внутриигровые предметы, скины и другие виртуальные товары']
    ];
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
  <section class="mb-10">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-white">GameMarket</h1>
    </div>
    <div class="bg-gradient-to-r from-zinc-900 to-zinc-800 text-white p-8 rounded-xl border border-zinc-700">
      <h2 class="text-2xl font-bold mb-2">Добро пожаловать в GameMarket!</h2>
      <p class="mb-4 text-zinc-300">Покупайте и продавайте игровые товары, аккаунты и услуги быстро и безопасно.</p>
      <a href="/products.php" class="inline-block px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
        Смотреть все товары
      </a>
    </div>
  </section>

  <section class="mb-10">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-white">Категории</h2>
      <a href="/categories.php" class="text-zinc-400 hover:text-white transition-colors">
        Все категории
      </a>
    </div>
    <div class="grid">
      <?php foreach ($categories as $category): ?>
        <a href="/category.php?id=<?php echo $category['id']; ?>" class="block">
          <div class="card card-body">
            <h3 class="text-xl font-bold mb-2 text-white"><?php echo $category['name']; ?></h3>
            <p class="text-zinc-400 line-clamp-2"><?php echo $category['description']; ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-white">Популярные товары</h2>
      <a href="/products.php" class="text-zinc-400 hover:text-white transition-colors">
        Смотреть все
      </a>
    </div>
    <div class="grid">
      <?php foreach ($popularProducts as $product): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

