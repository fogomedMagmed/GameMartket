-- Создание таблицы чатов
CREATE TABLE IF NOT EXISTS `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` enum('active', 'closed', 'waiting_for_operator') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы сообщений чата
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `sender_type` enum('user', 'bot', 'operator') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы операторов
CREATE TABLE IF NOT EXISTS `operators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `operators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы назначений чатов операторам
CREATE TABLE IF NOT EXISTS `chat_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  KEY `operator_id` (`operator_id`),
  CONSTRAINT `chat_assignments_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_assignments_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Создание таблицы для хранения ответов бота
CREATE TABLE IF NOT EXISTS `bot_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Вставка тестовых данных для бота
INSERT INTO `bot_responses` (`keyword`, `response`) VALUES
('привет', 'Здравствуйте! Я бот поддержки GameMarket. Чем могу помочь?'),
('здравствуйте', 'Здравствуйте! Я бот поддержки GameMarket. Чем могу помочь?'),
('помощь', 'Я могу помочь вам с вопросами о заказах, товарах, регистрации и оплате. Что именно вас интересует?'),
('заказ', 'Чтобы узнать статус заказа, перейдите в раздел "Мои заказы" в личном кабинете. Если у вас возникли проблемы с заказом, опишите их подробнее.'),
('оплата', 'Мы принимаем оплату банковскими картами и электронными деньгами. Если у вас возникли проблемы с оплатой, опишите их подробнее.'),
('регистрация', 'Для регистрации на сайте нажмите кнопку "Регистрация" в верхнем меню и заполните форму. Если у вас возникли проблемы с регистрацией, опишите их подробнее.'),
('товар', 'Все товары на нашем сайте проходят проверку перед публикацией. Если у вас возникли проблемы с товаром, опишите их подробнее.'),
('оператор', 'Сейчас я подключу вас к оператору. Пожалуйста, подождите немного.'),
('человек', 'Сейчас я подключу вас к оператору. Пожалуйста, подождите немного.');

-- Добавление администратора как оператора
INSERT INTO `operators` (`user_id`) VALUES (1);

