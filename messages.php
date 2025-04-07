<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isLoggedIn()) {
    header('Location: login.php?redirect=messages.php');
    exit;
}

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем ID пользователя
$userId = $_SESSION['user']['id'];

// Получаем ID диалога из URL
$conversationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем список диалогов пользователя
$stmt = $pdo->prepare("
    SELECT c.*, 
           u1.name as user1_name, u1.avatar as user1_avatar,
           u2.name as user2_name, u2.avatar as user2_avatar,
           p.name as product_name, p.image as product_image,
           (SELECT COUNT(*) FROM user_messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) as unread_count
    FROM user_conversations c
    JOIN users u1 ON c.user1_id = u1.id
    JOIN users u2 ON c.user2_id = u2.id
    LEFT JOIN products p ON c.product_id = p.id
    WHERE c.user1_id = ? OR c.user2_id = ?
    ORDER BY c.updated_at DESC
");
$stmt->execute([$userId, $userId, $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Если выбран диалог, получаем сообщения
$messages = [];
$otherUser = null;
$product = null;

if ($conversationId) {
    // Проверяем, принадлежит ли диалог пользователю
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u1.id as user1_id, u1.name as user1_name, u1.avatar as user1_avatar,
               u2.id as user2_id, u2.name as user2_name, u2.avatar as user2_avatar,
               p.id as product_id, p.name as product_name, p.image as product_image, p.price as product_price
        FROM user_conversations c
        JOIN users u1 ON c.user1_id = u1.id
        JOIN users u2 ON c.user2_id = u2.id
        LEFT JOIN products p ON c.product_id = p.id
        WHERE c.id = ? AND (c.user1_id = ? OR c.user2_id = ?)
    ");
    $stmt->execute([$conversationId, $userId, $userId]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conversation) {
        // Определяем собеседника
        if ($conversation['user1_id'] == $userId) {
            $otherUser = [
                'id' => $conversation['user2_id'],
                'name' => $conversation['user2_name'],
                'avatar' => $conversation['user2_avatar'] ?: 'assets/images/avatars/default.png'
            ];
        } else {
            $otherUser = [
                'id' => $conversation['user1_id'],
                'name' => $conversation['user1_name'],
                'avatar' => $conversation['user1_avatar'] ?: 'assets/images/avatars/default.png'
            ];
        }
        
        // Получаем информацию о товаре, если он есть
        if ($conversation['product_id']) {
            $product = [
                'id' => $conversation['product_id'],
                'name' => $conversation['product_name'],
                'image' => $conversation['product_image'],
                'price' => $conversation['product_price']
            ];
        }
        
        // Получаем сообщения диалога
        $stmt = $pdo->prepare("
            SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
            FROM user_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Отмечаем сообщения как прочитанные
        $stmt = $pdo->prepare("
            UPDATE user_messages 
            SET is_read = 1 
            WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
        ");
        $stmt->execute([$conversationId, $userId]);
    } else {
        // Если диалог не найден или не принадлежит пользователю, сбрасываем ID диалога
        $conversationId = 0;
    }
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-white">Сообщения</h1>
    
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Список диалогов -->
        <div class="w-full md:w-1/3 bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
            <div class="bg-zinc-800 p-4 border-b border-zinc-700">
                <h2 class="font-medium">Диалоги</h2>
            </div>
            
            <div class="overflow-y-auto h-[calc(100vh-300px)]">
                <?php if (count($conversations) > 0): ?>
                    <?php foreach ($conversations as $conv): ?>
                        <?php
                        // Определяем собеседника
                        $otherUserName = $conv['user1_id'] == $userId ? $conv['user2_name'] : $conv['user1_name'];
                        $otherUserAvatar = $conv['user1_id'] == $userId ? 
                            ($conv['user2_avatar'] ?: 'assets/images/avatars/default.png') : 
                            ($conv['user1_avatar'] ?: 'assets/images/avatars/default.png');
                        ?>
                        <a 
                            href="/messages.php?id=<?php echo $conv['id']; ?>" 
                            class="block p-4 border-b border-zinc-800 hover:bg-zinc-800 transition-colors <?php echo $conversationId === $conv['id'] ? 'bg-zinc-800' : ''; ?>"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full overflow-hidden mr-3">
                                        <img 
                                            src="/<?php echo $otherUserAvatar; ?>" 
                                            alt="<?php echo htmlspecialchars($otherUserName); ?>" 
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($otherUserName); ?></div>
                                        <?php if ($conv['product_name']): ?>
                                            <div class="text-xs text-gray-400">Товар: <?php echo htmlspecialchars($conv['product_name']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <div class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $conv['unread_count']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?php echo date('d.m.Y H:i', strtotime($conv['updated_at'])); ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-gray-500">
                        У вас пока нет диалогов
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Сообщения -->
        <div class="w-full md:w-2/3 bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
            <?php if ($conversationId && $otherUser): ?>
                <!-- Заголовок диалога -->
                <div class="bg-zinc-800 p-4 border-b border-zinc-700 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full overflow-hidden mr-3">
                            <img 
                                src="/<?php echo $otherUser['avatar']; ?>" 
                                alt="<?php echo htmlspecialchars($otherUser['name']); ?>" 
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div>
                            <div class="font-medium"><?php echo htmlspecialchars($otherUser['name']); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($product): ?>
                    <!-- Информация о товаре -->
                    <div class="p-4 border-b border-zinc-700 bg-zinc-800/50">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded overflow-hidden mr-3">
                                <img 
                                    src="/<?php echo $product['image']; ?>" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            <div>
                                <div class="font-medium text-sm"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="text-sm text-gray-400"><?php echo $product['price']; ?> ₽</div>
                            </div>
                            <a href="/product.php?id=<?php echo $product['id']; ?>" class="ml-auto px-3 py-1 bg-zinc-700 hover:bg-zinc-600 text-white text-sm rounded transition-colors">
                                Перейти к товару
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Сообщения диалога -->
                <div id="chat-messages" class="p-4 h-[calc(100vh-400px)] overflow-y-auto flex flex-col space-y-4">
                    <?php foreach ($messages as $message): ?>
                        <div class="flex <?php echo $message['sender_id'] === $userId ? 'justify-end' : 'justify-start'; ?>">
                            <div class="max-w-xs md:max-w-md rounded-lg p-3 <?php 
                                echo $message['sender_id'] === $userId ? 'bg-zinc-700 text-white' : 'bg-zinc-800 text-gray-300';
                            ?>">
                                <div class="text-sm whitespace-pre-wrap"><?php echo htmlspecialchars($message['message']); ?></div>
                                <div class="text-xs text-gray-500 mt-1 text-right">
                                    <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Форма отправки сообщения -->
                <div class="border-t border-zinc-800 p-4">
                    <div id="message-form" class="flex space-x-2">
                        <input 
                            type="text" 
                            id="message-input"
                            placeholder="Введите сообщение..." 
                            class="flex-grow p-2 rounded-lg bg-zinc-800 border border-zinc-700 text-white placeholder-gray-500 focus:outline-none focus:border-zinc-600"
                            required
                        >
                        <button 
                            id="send-button"
                            type="button" 
                            class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white rounded-lg transition-colors"
                            data-conversation-id="<?php echo $conversationId; ?>"
                        >
                            Отправить
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-4 text-center text-gray-500">
                    Выберите диалог из списка или начните новый диалог с продавцом товара
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Прокручиваем чат вниз при загрузке страницы
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Обработчик отправки сообщения
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    
    if (messageInput && sendButton) {
        // Отправка по нажатию Enter
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        // Отправка по клику на кнопку
        sendButton.addEventListener('click', sendMessage);
        
        function sendMessage() {
            const message = messageInput.value.trim();
            const conversationId = sendButton.getAttribute('data-conversation-id');
            
            if (message && conversationId) {
                // Блокируем кнопку отправки, чтобы предотвратить повторную отправку
                sendButton.disabled = true;
                
                // Отправляем сообщение на сервер
                fetch('/api/send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `conversation_id=${conversationId}&message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Добавляем сообщение в чат
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'flex justify-end';
                        messageDiv.innerHTML = `
                            <div class="max-w-xs md:max-w-md rounded-lg p-3 bg-zinc-700 text-white">
                                <div class="text-sm whitespace-pre-wrap">${message}</div>
                                <div class="text-xs text-gray-500 mt-1 text-right">
                                    ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </div>
                            </div>
                        `;
                        chatMessages.appendChild(messageDiv);
                        
                        // Очищаем поле ввода
                        messageInput.value = '';
                        
                        // Прокручиваем чат вниз
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    } else {
                        alert('Ошибка при отправке сообщения: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при отправке сообщения');
                })
                .finally(() => {
                    // Разблокируем кнопку отправки
                    sendButton.disabled = false;
                    // Возвращаем фокус на поле ввода
                    messageInput.focus();
                });
            }
        }
    }
    
    // Периодически проверяем наличие новых сообщений
    const conversationId = document.querySelector('[data-conversation-id]')?.getAttribute('data-conversation-id');
    if (conversationId) {
        setInterval(() => {
            fetch(`/api/check_messages.php?conversation_id=${conversationId}&last_message_id=${getLastMessageId()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    // Добавляем новые сообщ


Создадим API для отправки личных сообщений:

