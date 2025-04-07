<?php
require_once 'includes/header.php';

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    header('Location: /login.php?redirect=chat.php');
    exit;
}

// Получаем ID пользователя
$userId = $_SESSION['user']['id'];

// Проверяем, есть ли у пользователя активный чат
$stmt = $pdo->prepare("SELECT * FROM chats WHERE user_id = ? AND status != 'closed' ORDER BY updated_at DESC LIMIT 1");
$stmt->execute([$userId]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

// Если нет активного чата, создаем новый
if (!$chat) {
    $stmt = $pdo->prepare("INSERT INTO chats (user_id) VALUES (?)");
    $stmt->execute([$userId]);
    $chatId = $pdo->lastInsertId();
    
    // Добавляем приветственное сообщение от бота
    $stmt = $pdo->prepare("INSERT INTO chat_messages (chat_id, sender_type, message) VALUES (?, 'bot', 'Здравствуйте! Я бот поддержки GameMarket. Чем могу помочь?')");
    $stmt->execute([$chatId]);
    
    // Получаем созданный чат
    $stmt = $pdo->prepare("SELECT * FROM chats WHERE id = ?");
    $stmt->execute([$chatId]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получаем сообщения чата
$stmt = $pdo->prepare("SELECT * FROM chat_messages WHERE chat_id = ? ORDER BY created_at ASC");
$stmt->execute([$chat['id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отмечаем все сообщения как прочитанные
$stmt = $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND sender_type != 'user'");
$stmt->execute([$chat['id']]);
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Чат поддержки</h1>
        
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
            <!-- Заголовок чата -->
            <div class="bg-zinc-800 p-4 border-b border-zinc-700 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                    <span class="font-medium">
                        <?php if ($chat['status'] === 'waiting_for_operator'): ?>
                            Ожидание оператора...
                        <?php elseif ($chat['status'] === 'active'): ?>
                            Чат с поддержкой
                        <?php else: ?>
                            Чат закрыт
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($chat['status'] !== 'closed'): ?>
                    <form method="post" action="/api/chat_close.php">
                        <input type="hidden" name="chat_id" value="<?php echo $chat['id']; ?>">
                        <button type="submit" class="text-sm text-gray-400 hover:text-white">
                            Закрыть чат
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Сообщения чата -->
            <div id="chat-messages" class="p-4 h-96 overflow-y-auto flex flex-col space-y-4">
                <?php foreach ($messages as $message): ?>
                    <div class="flex <?php echo $message['sender_type'] === 'user' ? 'justify-end' : 'justify-start'; ?>">
                        <div class="max-w-xs md:max-w-md rounded-lg p-3 <?php 
                            if ($message['sender_type'] === 'user') {
                                echo 'bg-zinc-700 text-white';
                            } elseif ($message['sender_type'] === 'bot') {
                                echo 'bg-zinc-800 text-gray-300';
                            } else {
                                echo 'bg-zinc-800 text-gray-300 border-l-4 border-blue-500';
                            }
                        ?>">
                            <?php if ($message['sender_type'] !== 'user'): ?>
                                <div class="text-xs text-gray-500 mb-1">
                                    <?php echo $message['sender_type'] === 'bot' ? 'Бот поддержки' : 'Оператор'; ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-sm whitespace-pre-wrap"><?php echo htmlspecialchars($message['message']); ?></div>
                            <div class="text-xs text-gray-500 mt-1 text-right">
                                <?php echo date('H:i', strtotime($message['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Форма отправки сообщения -->
            <?php if ($chat['status'] !== 'closed'): ?>
                <form id="chat-form" class="border-t border-zinc-800 p-4">
                    <input type="hidden" name="chat_id" value="<?php echo $chat['id']; ?>">
                    <div class="flex space-x-2">
                        <input 
                            type="text" 
                            name="message" 
                            id="message-input"
                            placeholder="Введите сообщение..." 
                            class="flex-grow p-2 rounded-lg bg-zinc-800 border border-zinc-700 text-white placeholder-gray-500 focus:outline-none focus:border-zinc-600"
                            required
                        >
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white rounded-lg transition-colors"
                        >
                            Отправить
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="border-t border-zinc-800 p-4 text-center text-gray-500">
                    Чат закрыт. <a href="/chat.php?new=1" class="text-blue-500 hover:underline">Начать новый чат</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Прокручиваем чат вниз при загрузке страницы
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Обработчик отправки формы - используем один раз
    const chatForm = document.getElementById('chat-form');
    if (chatForm) {
        // Удаляем все существующие обработчики события submit
        const clonedForm = chatForm.cloneNode(true);
        chatForm.parentNode.replaceChild(clonedForm, chatForm);
        
        // Добавляем новый обработчик
        clonedForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Блокируем кнопку отправки, чтобы предотвратить повторную отправку
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }
            
            const chatId = this.elements.chat_id.value;
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            
            if (message) {
                // Отправляем сообщение на сервер
                fetch('/api/chat_send.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `chat_id=${chatId}&message=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Добавляем сообщение пользователя в чат
                        const userMessageDiv = document.createElement('div');
                        userMessageDiv.className = 'flex justify-end';
                        userMessageDiv.innerHTML = `
                            <div class="max-w-xs md:max-w-md rounded-lg p-3 bg-zinc-700 text-white">
                                <div class="text-sm whitespace-pre-wrap">${message}</div>
                                <div class="text-xs text-gray-500 mt-1 text-right">
                                    ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </div>
                            </div>
                        `;
                        chatMessages.appendChild(userMessageDiv);
                        
                        // Если есть ответ бота, добавляем его в чат
                        if (data.botResponse) {
                            const botMessageDiv = document.createElement('div');
                            botMessageDiv.className = 'flex justify-start';
                            botMessageDiv.innerHTML = `
                                <div class="max-w-xs md:max-w-md rounded-lg p-3 bg-zinc-800 text-gray-300">
                                    <div class="text-xs text-gray-500 mb-1">
                                        Бот поддержки
                                    </div>
                                    <div class="text-sm whitespace-pre-wrap">${data.botResponse}</div>
                                    <div class="text-xs text-gray-500 mt-1 text-right">
                                        ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                    </div>
                                </div>
                            `;
                            setTimeout(() => {
                                chatMessages.appendChild(botMessageDiv);
                                chatMessages.scrollTop = chatMessages.scrollHeight;
                            }, 500);
                        }
                        
                        // Если статус чата изменился, обновляем страницу
                        if (data.statusChanged) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                        
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
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
            } else {
                // Разблокируем кнопку отправки, если сообщение пустое
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    }
    
    // Периодически проверяем наличие новых сообщений
    const chatId = document.querySelector('input[name="chat_id"]')?.value;
    if (chatId) {
        setInterval(() => {
            fetch(`/api/chat_check.php?chat_id=${chatId}&last_message_id=${getLastMessageId()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    // Добавляем новые сообщения в чат
                    data.messages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `flex ${message.sender_type === 'user' ? 'justify-end' : 'justify-start'}`;
                        
                        let messageClass = '';
                        let senderLabel = '';
                        
                        if (message.sender_type === 'user') {
                            messageClass = 'bg-zinc-700 text-white';
                        } else if (message.sender_type === 'bot') {
                            messageClass = 'bg-zinc-800 text-gray-300';
                            senderLabel = '<div class="text-xs text-gray-500 mb-1">Бот поддержки</div>';
                        } else {
                            messageClass = 'bg-zinc-800 text-gray-300 border-l-4 border-blue-500';
                            senderLabel = '<div class="text-xs text-gray-500 mb-1">Оператор</div>';
                        }
                        
                        messageDiv.innerHTML = `
                            <div class="max-w-xs md:max-w-md rounded-lg p-3 ${messageClass}" data-message-id="${message.id}">
                                ${message.sender_type !== 'user' ? senderLabel : ''}
                                <div class="text-sm whitespace-pre-wrap">${message.message}</div>
                                <div class="text-xs text-gray-500 mt-1 text-right">
                                    ${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </div>
                            </div>
                        `;
                        
                        chatMessages.appendChild(messageDiv);
                    });
                    
                    // Прокручиваем чат вниз
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    
                    // Если статус чата изменился, обновляем страницу
                    if (data.statusChanged) {
                        window.location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }, 5000); // Проверяем каждые 5 секунд
    }
    
    // Функция для получения ID последнего сообщения в чате
    function getLastMessageId() {
        const messages = chatMessages.querySelectorAll('[data-message-id]');
        return messages.length > 0 ? messages[messages.length - 1].dataset.messageId : 0;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

