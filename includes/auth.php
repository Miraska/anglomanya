<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/database.php';


// Запуск сессии
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Проверка авторизации
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Проверка роли администратора
function isAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Получение текущего пользователя
function getCurrentUser() {
    startSession();
    if (!isLoggedIn()) {
        return null;
    }
    
    $user = queryOne(
        "SELECT id, email, name, avatar, role FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    return $user;
}

// Регистрация пользователя
function registerUser($email, $password, $name) {
    // Проверка существования email
    $existing = queryOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        return ['success' => false, 'message' => 'Пользователь с таким email уже существует'];
    }
    
    // Хеширование пароля
    $hashedPassword = $password;
    
    // Вставка пользователя
    $result = execute(
        "INSERT INTO users (email, password, name) VALUES (?, ?, ?)",
        [$email, $hashedPassword, $name]
    );
    
    if ($result) {
        return ['success' => true, 'message' => 'Регистрация успешна'];
    }
    
    return ['success' => false, 'message' => 'Ошибка при регистрации'];
}



// Авторизация пользователя
function loginUser($email, $password) {
    $user = queryOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Неверный email'];
    }
    
    if ($password != $user['password']) {
        return ['success' => false, 'message' => 'Неверный пароль'];
    }
    
    // Установка сессии
    startSession();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'];
    
    return ['success' => true, 'message' => 'Авторизация успешна'];
}

// Выход из системы
function logoutUser() {
    startSession();
    session_unset();
    session_destroy();
}

// Обновление профиля пользователя
function updateProfile($userId, $data) {
    $updates = [];
    $params = [];
    
    if (!empty($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
    }
    
    if (!empty($data['email'])) {
        // Проверка уникальности email
        $existing = queryOne(
            "SELECT id FROM users WHERE email = ? AND id != ?",
            [$data['email'], $userId]
        );
        if ($existing) {
            return ['success' => false, 'message' => 'Email уже используется'];
        }
        $updates[] = "email = ?";
        $params[] = $data['email'];
    }
    
    if (!empty($data['avatar'])) {
        $updates[] = "avatar = ?";
        $params[] = $data['avatar'];
    }
    
    if (empty($updates)) {
        return ['success' => false, 'message' => 'Нет данных для обновления'];
    }
    
    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $result = execute($sql, $params);
    
    if ($result) {
        // Обновление сессии
        startSession();
        if (!empty($data['name'])) {
            $_SESSION['user_name'] = $data['name'];
        }
        if (!empty($data['email'])) {
            $_SESSION['user_email'] = $data['email'];
        }
        if (!empty($data['avatar'])) {
            $_SESSION['user_avatar'] = $data['avatar'];
        }
        
        return ['success' => true, 'message' => 'Профиль обновлен'];
    }
    
    return ['success' => false, 'message' => 'Ошибка при обновлении профиля'];
}

// Изменение пароля
function changePassword($userId, $oldPassword, $newPassword) {
    $user = queryOne("SELECT password FROM users WHERE id = ?", [$userId]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Пользователь не найден'];
    }
    
    if ($oldPassword != $user['password']) {
        return ['success' => false, 'message' => 'Неверный текущий пароль'];
    }
    
    $hashedPassword = $newPassword;
    $result = execute(
        "UPDATE users SET password = ? WHERE id = ?",
        [$hashedPassword, $userId]
    );
    
    if ($result) {
        return ['success' => true, 'message' => 'Пароль изменен'];
    }
    
    return ['success' => false, 'message' => 'Ошибка при изменении пароля'];
}

// Защита страницы (требуется авторизация)
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
}

// Защита страницы (требуется роль администратора)
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}


// Получение курсов пользователя
// function getUserCourses($userId) {
//     return query(
//         "SELECT c.*, e.enrolled_at, e.progress 
//          FROM enrollments e 
//          JOIN courses c ON e.course_id = c.id 
//          WHERE e.user_id = ? 
//          ORDER BY e.enrolled_at DESC",
//         [$userId]
//     );
// }

// Обновление аватара
function updateAvatar($userId, $avatarPath) {
    $result = execute(
        "UPDATE users SET avatar = ? WHERE id = ?",
        [$avatarPath, $userId]
    );
    
    
    if ($result) {
        if (file_exists($_SESSION['user_avatar']))
            unlink($_SESSION['user_avatar']);
        startSession();
        $_SESSION['user_avatar'] = $avatarPath;
        return true;
    }
    
    return false;
}

// Загрузка аватара
function uploadAvatar($file) {
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Ошибка загрузки файла'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Допустимые форматы: JPG, PNG, GIF'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Максимальный размер файла: 2MB'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'path' => 'uploads/avatars/' . $fileName];
    }
    
    return ['success' => false, 'message' => 'Ошибка при сохранении файла'];
}
?>



