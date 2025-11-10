<?php
require_once __DIR__ . '/../db/database.php';

// Получение курсов в корзине пользователя с рейтингом
function getCartCourses() {
    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    
    $userId = $_SESSION['user_id'];
    
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                   COALESCE(AVG(r.rating), 0) as calculated_rating,
                   COUNT(r.id) as review_count,
                   MAX(ct.added_at) as latest_added_at
            FROM cart ct
            JOIN courses c ON ct.course_id = c.id
            JOIN categories cat ON c.category_id = cat.id
            LEFT JOIN reviews r ON c.id = r.course_id
            WHERE ct.user_id = ? AND c.is_active = 1
            GROUP BY c.id, cat.name, cat.slug
            ORDER BY latest_added_at DESC";
    
    return query($sql, [$userId]);
}

// Проверка доступности курса для покупки
function canPurchaseCourse($userId, $courseId) {
    // Проверяем, не куплен ли уже курс
    $purchased = queryOne(
        "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?",
        [$userId, $courseId]
    );
    
    return !$purchased;
}

// Получение общей суммы корзины
function getCartTotal() {
    $courses = getCartCourses();
    $total = 0;
    
    foreach ($courses as $course) {
        $total += $course['price'];
    }
    
    return $total;
}

// Получение количества товаров в корзине
function getCartCount() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    $userId = $_SESSION['user_id'];
    
    $result = queryOne(
        "SELECT COUNT(*) as count FROM cart WHERE user_id = ?",
        [$userId]
    );
    
    return $result ? $result['count'] : 0;
}

// Добавление курса в корзину
function addToCart($userId, $courseId) {
    // Проверяем, нет ли уже этого курса в корзине
    $existing = isInCart($courseId);
    
    if ($existing) {
        return ['success' => false, 'message' => 'Курс уже в корзине'];
    }
    
    // Проверяем, не куплен ли уже курс
    $purchased = queryOne(
        "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?",
        [$userId, $courseId]
    );
    
    if ($purchased) {
        return ['success' => false, 'message' => 'Курс уже куплен'];
    }
    
    $sql = "INSERT INTO cart (user_id, course_id) VALUES (?, ?)";
    $result = execute($sql, [$userId, $courseId]);
    
    if ($result) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при добавлении в корзину'];
}

// Удаление курса из корзины
function removeFromCart($courseId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    
    $sql = "DELETE FROM cart WHERE user_id = ? AND course_id = ?";
    return execute($sql, [$userId, $courseId]);
}

// Очистка корзины
function clearCart() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    
    $sql = "DELETE FROM cart WHERE user_id = ?";
    return execute($sql, [$userId]);
}

// Проверка, есть ли курс в корзине
function isInCart($courseId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    
    $result = queryOne(
        "SELECT id FROM cart WHERE user_id = ? AND course_id = ?",
        [$userId, $courseId]
    );
    
    return $result !== false;
}

// Функция для склонения существительных
function getNounPluralForm($number, $one, $two, $five) {
    $number = abs($number);
    $number %= 100;
    
    if ($number >= 5 && $number <= 20) {
        return $five;
    }
    
    $number %= 10;
    
    if ($number == 1) {
        return $one;
    }
    
    if ($number >= 2 && $number <= 4) {
        return $two;
    }
    
    return $five;
}
?>