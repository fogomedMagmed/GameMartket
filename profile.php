<?php
// Начинаем сессию
session_start();

// Подключаем файл с функциями
require_once 'includes/functions.php';

// Если пользователь не авторизован, перенаправляем на страницу входа
if (!isLoggedIn()) {
   header('Location: login.php');
   exit;
}

// Подключаемся к базе данных
$pdo = require 'config/database.php';

// Получаем данные текущего пользователя
$user = getCurrentUser();

// Обработка формы обновления профиля
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
   $name = $_POST['name'] ?? '';
   $email = $_POST['email'] ?? '';
   $currentPassword = $_POST['current_password'] ?? '';
   $newPassword = $_POST['new_password'] ?? '';
   $confirmPassword = $_POST['confirm_password'] ?? '';
   
   // Проверяем, что новые пароли совпадают
   if (!empty($newPassword) && $newPassword !== $confirmPassword) {
       $error = 'Новые пароли не совпадают';
   } else {
       try {
           if ($pdo instanceof PDO) {
               // Получаем текущий пароль пользователя из базы данных
               $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
               $stmt->execute([$user['id']]);
               $userData = $stmt->fetch();
               
               if ($userData) {
                   // Если пользователь хочет изменить пароль, проверяем текущий пароль
                   if (!empty($newPassword)) {
                       if (empty($currentPassword) || !password_verify($currentPassword, $userData['password'])) {
                           $error = 'Текущий пароль введен неверно';
                       } else {
                           // Хешируем новый пароль
                           $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                           
                           // Обновляем данные пользователя с новым паролем
                           $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                           $stmt->execute([$name, $email, $hashedPassword, $user['id']]);
                           
                           // Обновляем данные в сессии
                           $_SESSION['user']['name'] = $name;
                           $_SESSION['user']['email'] = $email;
                           
                           $success = 'Профиль успешно обновлен';
                       }
                   } else {
                       // Обновляем только имя и email
                       $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                       $stmt->execute([$name, $email, $user['id']]);
                       
                       // Обновляем данные в сессии
                       $_SESSION['user']['name'] = $name;
                       $_SESSION['user']['email'] = $email;
                       
                       $success = 'Профиль успешно обновлен';
                   }
               } else {
                   $error = 'Пользователь не найден';
               }
           } else {
               $error = 'Ошибка подключения к базе данных';
           }
       } catch (PDOException $e) {
           $error = 'Ошибка при обновлении профиля: ' . $e->getMessage();
       }
   }
}

// Обработка загрузки аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['avatar'], 'assets/images/avatars/');
        
        if ($uploadResult['success']) {
            try {
                // Проверяем, есть ли у пользователя уже аватар
                $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $oldAvatar = $stmt->fetchColumn();
                
                // Если есть старый аватар и это не дефолтный аватар, удаляем его
                if ($oldAvatar && $oldAvatar !== 'assets/images/avatars/default.png' && file_exists($oldAvatar)) {
                    unlink($oldAvatar);
                }
                
                // Обновляем аватар пользователя
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$uploadResult['file_path'], $user['id']]);
                
                // Обновляем данные в сессии
                $_SESSION['user']['avatar'] = $uploadResult['file_path'];
                
                $success = 'Аватар успешно обновлен';
                
                // Обновляем данные пользователя
                $user = getCurrentUser();
            } catch (PDOException $e) {
                $error = 'Ошибка при обновлении аватара: ' . $e->getMessage();
            }
        } else {
            $error = $uploadResult['message'];
        }
    } else {
        $error = 'Ошибка при загрузке файла';
    }
}

// Изменяем код получения аватара пользователя, чтобы он был более устойчивым к ошибкам
try {
    // Проверяем, существует ли столбец avatar в таблице users
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        // Если столбец существует, получаем значение аватара
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $avatar = $stmt->fetchColumn();
    } else {
        // Если столбца нет, используем значение по умолчанию
        $avatar = 'assets/images/avatars/default.png';
    }
} catch (PDOException $e) {
    // В случае ошибки используем значение по умолчанию
    $avatar = 'assets/images/avatars/default.png';
}

// Если аватар не установлен, используем дефолтный
if (!$avatar) {
    $avatar = 'assets/images/avatars/default.png';
    
    // Создаем директорию для аватаров, если она не существует
    if (!file_exists('assets/images/avatars/')) {
        mkdir('assets/images/avatars/', 0777, true);
    }
    
    // Копируем дефолтный аватар, если его нет
    if (!file_exists($avatar)) {
        copy('assets/images/placeholder.jpg', $avatar);
    }
}

// Подключаем шапку сайта
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
   <h1 class="text-3xl font-bold mb-6 text-white">Мой профиль</h1>
   
   <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
       <div class="md:col-span-2">
           <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
               <h2 class="text-xl font-bold mb-4 text-white">Личные данные</h2>
               
               <?php if ($error): ?>
                   <div class="bg-red-900/30 border border-red-800 text-red-200 px-4 py-3 rounded mb-4">
                       <?php echo $error; ?>
                   </div>
               <?php endif; ?>
               
               <?php if ($success): ?>
                   <div class="bg-green-900/30 border border-green-800 text-green-200 px-4 py-3 rounded mb-4">
                       <?php echo $success; ?>
                   </div>
               <?php endif; ?>
               
               <form method="post" action="profile.php" class="space-y-4">
                   <div class="space-y-2">
                       <label for="name" class="text-zinc-300">Имя</label>
                       <input
                           id="name"
                           name="name"
                           type="text"
                           value="<?php echo htmlspecialchars($user['name']); ?>"
                           required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                       >
                   </div>
                   
                   <div class="space-y-2">
                       <label for="email" class="text-zinc-300">Email</label>
                       <input
                           id="email"
                           name="email"
                           type="email"
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           required
                           class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                       >
                   </div>
                   
                   <div class="border-t border-zinc-800 pt-4 mt-4">
                       <h3 class="text-lg font-medium mb-4 text-white">Изменение пароля</h3>
                       
                       <div class="space-y-2">
                           <label for="current_password" class="text-zinc-300">Текущий пароль</label>
                           <input
                               id="current_password"
                               name="current_password"
                               type="password"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                           >
                       </div>
                       
                       <div class="space-y-2">
                           <label for="new_password" class="text-zinc-300">Новый пароль</label>
                           <input
                               id="new_password"
                               name="new_password"
                               type="password"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                           >
                       </div>
                       
                       <div class="space-y-2">
                           <label for="confirm_password" class="text-zinc-300">Подтвердите новый пароль</label>
                           <input
                               id="confirm_password"
                               name="confirm_password"
                               type="password"
                               class="w-full bg-zinc-800 border border-zinc-700 rounded p-2 text-white"
                           >
                       </div>
                   </div>
                   
                   <button type="submit" name="update_profile" class="w-full bg-white hover:bg-gray-200 text-black py-2 px-4 rounded-md">
                       Сохранить изменения
                   </button>
               </form>
           </div>
       </div>
       
       <div>
           <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
               <h2 class="text-xl font-bold mb-4 text-white">Аватар</h2>
               
               <div class="flex flex-col items-center space-y-4">
                   <div class="w-32 h-32 rounded-full overflow-hidden bg-zinc-800">
                       <img src="<?php echo $avatar; ?>" alt="Аватар" class="w-full h-full object-cover">
                   </div>
                   
                   <form method="post" action="profile.php" enctype="multipart/form-data" class="w-full">
                       <div class="space-y-4">
                           <div class="flex items-center justify-center">
                               <label for="avatar" class="cursor-pointer bg-zinc-800 hover:bg-zinc-700 text-white py-2 px-4 rounded-md transition-colors">
                                   Выбрать файл
                                   <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0].name">
                               </label>
                           </div>
                           <div id="file-name" class="text-center text-zinc-400 text-sm"></div>
                           <button type="submit" name="upload_avatar" class="w-full bg-white hover:bg-gray-200 text-black py-2 px-4 rounded-md">
                               Загрузить аватар
                           </button>
                       </div>
                   </form>
               </div>
           </div>
           
           <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6 mt-6">
               <h2 class="text-xl font-bold mb-4 text-white">Информация об аккаунте</h2>
               <div class="space-y-2">
                   <p class="text-zinc-300">
                       <span class="text-zinc-500">Роль:</span> 
                       <?php 
                           if ($user['role'] === 'admin') echo 'Администратор';
                           elseif ($user['role'] === 'seller') echo 'Продавец';
                           else echo 'Покупатель';
                       ?>
                   </p>
                   <p class="text-zinc-300">
                       <span class="text-zinc-500">Дата регистрации:</span> 
                       <?php echo date('d.m.Y', strtotime($user['created_at'] ?? 'now')); ?>
                   </p>
               </div>
           </div>
       </div>
   </div>
</div>

<?php
// Подключаем подвал сайта
include 'includes/footer.php';
?>

