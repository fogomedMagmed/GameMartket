-- Создание таблицы пользователей
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы категорий
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы товаров
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` enum('active', 'inactive', 'sold') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы заказов
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` enum('pending', 'paid', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы элементов заказа
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы отзывов
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Вставка данных в таблицу категорий
INSERT INTO `categories` (`name`, `description`) VALUES
('Аккаунты', 'Игровые аккаунты с прокачанными персонажами'),
('Игровая валюта', 'Внутриигровая валюта для различных игр'),
('Услуги', 'Услуги по прокачке, бусту и помощи в играх'),
('Предметы', 'Внутриигровые предметы, скины и другие виртуальные товары');

-- Вставка тестового администратора
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Администратор', 'admin@gamemarket.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Пароль: password

-- Вставка тестового продавца
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Продавец', 'seller@gamemarket.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller');
-- Пароль: password

-- Вставка тестового покупателя
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Покупатель', 'buyer@gamemarket.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer');
-- Пароль: password

-- Вставка тестовых товаров
INSERT INTO `products` (`name`, `description`, `price`, `image`, `seller_id`, `category_id`) VALUES
('Аккаунт World of Warcraft (70 уровень)', 'Аккаунт с персонажем 70 уровня, полным комплектом эпического снаряжения и редкими маунтами', 5000.00, 'assets/images/placeholder.jpg', 2, 1),
('10000 золота в World of Warcraft', 'Внутриигровая валюта для World of Warcraft. Доставка в течение 24 часов.', 1500.00, 'assets/images/placeholder.jpg', 2, 2),
('Буст рейтинга в Dota 2 (1000-2000 MMR)', 'Профессиональный игрок поднимет ваш рейтинг с 1000 до 2000 MMR за 3-5 дней', 3000.00, 'assets/images/placeholder.jpg', 2, 3),
('Скин AWP Dragon Lore (CS:GO)', 'Редкий скин для AWP в CS:GO. Минимальный износ, без царапин.', 120000.00, 'assets/images/placeholder.jpg', 2, 4),
('Аккаунт League of Legends (Платина)', 'Аккаунт с платиновым рангом, 80+ чемпионов и 50+ скинов', 4500.00, 'assets/images/placeholder.jpg', 2, 1),
('Прокачка персонажа в Diablo 4', 'Прокачка вашего персонажа до 50 уровня с прохождением всех сюжетных заданий', 2500.00, 'assets/images/placeholder.jpg', 2, 3),
('5000 V-Bucks для Fortnite', 'Внутриигровая валюта для Fortnite. Мгновенная доставка.', 1800.00, 'assets/images/placeholder.jpg', 2, 2),
('Аккаунт Genshin Impact (AR 55)', 'Аккаунт с Adventure Rank 55, множеством 5-звездочных персонажей и оружия', 8000.00, 'assets/images/placeholder.jpg', 2, 1),
('Прохождение рейда в Destiny 2', 'Профессиональная команда проведет вас через любой рейд в Destiny 2 с получением всех наград', 1200.00, 'assets/images/placeholder.jpg', 2, 3),
('Набор редких предметов в Minecraft', 'Набор редких и труднодоступных предметов для вашего сервера Minecraft', 800.00, 'assets/images/placeholder.jpg', 2, 4);

