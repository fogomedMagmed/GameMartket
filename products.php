<?php
// Начинаем сессию для хранения данных пользователя
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем все категории для фильтрации
$categories = getAllCategories($pdo);

// Фильтрация товаров
$filters = [];

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $filters['category_id'] = (int)$_GET['category_id'];
}

if (isset($_GET['price_min']) && !empty($_GET['price_min'])) {
    $filters['price_min'] = (float)$_GET['price_min'];
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $filters['price_max'] = (float)$_GET['price_max'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Получаем отфильтрованные товары
$products = getAllProducts($pdo, null, 0, $filters);

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-6 text-white">Все игровые товары</h1>
  
  <div class="flex flex-col md:flex-row gap-6">
    <div class="w-full md:w-64 mb-6">
      <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4">
        <h2 class="font-bold text-lg mb-4 text-white">Фильтры</h2>
        
        <form action="/products.php" method="get">
          <div class="mb-4">
            <h3 class="font-medium mb-2 text-white">Категории</h3>
            <div class="space-y-2">
              <?php foreach ($categories as $category): ?>
                <label class="flex items-center text-zinc-300">
                  <input 
                    type="checkbox" 
                    name="category_id" 
                    value="<?php echo $category['id']; ?>" 
                    <?php echo (isset($filters['category_id']) && $filters['category_id'] == $category['id']) ? 'checked' : ''; ?>
                    class="mr-2 bg-zinc-800 border-zinc-700"
                  >
                  <?php echo $category['name']; ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="mb-4">
            <h3 class="font-medium mb-2 text-white">Цена</h3>
            <div class="flex gap-2">
              <input 
                type="number" 
                name="price_min"
                placeholder="От" 
                value="<?php echo $filters['price_min'] ?? ''; ?>"
                class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white placeholder-zinc-500"
              >
              <input 
                type="number" 
                name="price_max"
                placeholder="До" 
                value="<?php echo $filters['price_max'] ?? ''; ?>"
                class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white placeholder-zinc-500"
              >
            </div>
          </div>
          
          <div class="mb-4">
            <h3 class="font-medium mb-2 text-white">Поиск</h3>
            <input 
              type="text" 
              name="search"
              placeholder="Поиск товаров..." 
              value="<?php echo $filters['search'] ?? ''; ?>"
              class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white placeholder-zinc-500"
            >
          </div>
          
          <button type="submit" class="w-full bg-white text-black py-2 px-4 rounded-md hover:bg-gray-200 transition-colors">
            Применить фильтры
          </button>
        </form>
      </div>
    </div>
    
    <div class="flex-1">
      <?php if (empty($products)): ?>
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-8 text-center">
          <p class="text-zinc-300 mb-4">По вашему запросу ничего не найдено</p>
          <a href="/products.php" class="inline-block px-6 py-3 bg-white text-black font-medium rounded-lg hover:bg-gray-200 transition-colors">
            Сбросить фильтры
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
  </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

