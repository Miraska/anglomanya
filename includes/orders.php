<?php
require_once __DIR__ . '/../db/database.php';

// Создание заказа
function createOrder($userId, $courses) {
    beginTransaction();
    
    try {
        // Рассчитываем общую сумму
        $totalAmount = 0;
        foreach ($courses as $course) {
            $totalAmount += floatval($course['price']);
        }
        
        // Создаем заказ
        $sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
        $result = execute($sql, [$userId, $totalAmount]);
        
        if (!$result) {
            throw new Exception('Ошибка при создании заказа');
        }
        
        $orderId = lastInsertId();
        
        // Добавляем товары в заказ
        foreach ($courses as $course) {
            $sql = "INSERT INTO order_items (order_id, course_id, price) VALUES (?, ?, ?)";
            $result = execute($sql, [$orderId, $course['id'], floatval($course['price'])]);
            
            if (!$result) {
                throw new Exception('Ошибка при добавлении товаров в заказ');
            }
        }
        
        commit();
        return ['success' => true, 'order_id' => $orderId];
        
    } catch (Exception $e) {
        rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Получение заказа по ID
function getOrderById($orderId) {
    $sql = "SELECT o.*, u.email, u.name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    return queryOne($sql, [$orderId]);
}

// Получение товаров заказа
function getOrderItems($orderId) {
    $sql = "SELECT oi.*, c.title, c.description, c.image, cat.name as category_name
            FROM order_items oi 
            JOIN courses c ON oi.course_id = c.id 
            JOIN categories cat ON c.category_id = cat.id 
            WHERE oi.order_id = ?";
    return query($sql, [$orderId]);
}

// Обновление статуса заказа
function updateOrderStatus($orderId, $status, $paymentId = null) {
    if ($paymentId) {
        $sql = "UPDATE orders SET status = ?, payment_id = ?, updated_at = NOW() WHERE id = ?";
        return execute($sql, [$status, $paymentId, $orderId]);
    } else {
        $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        return execute($sql, [$status, $orderId]);
    }
}

// Завершение заказа и предоставление доступа к курсам
function completeOrder($orderId) {
    beginTransaction();
    
    try {
        // Получаем информацию о заказе
        $order = getOrderById($orderId);
        if (!$order) {
            throw new Exception('Заказ не найден');
        }
        
        // Получаем товары заказа
        $items = getOrderItems($orderId);
        
        if (empty($items)) {
            throw new Exception('В заказе нет товаров');
        }
        
        // Предоставляем доступ к курсам
        foreach ($items as $item) {
            // Проверяем, нет ли уже доступа
            $existing = queryOne(
                "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?",
                [$order['user_id'], $item['course_id']]
            );
            
            if (!$existing) {
                $sql = "INSERT INTO user_courses (user_id, course_id, purchased_at) VALUES (?, ?, NOW())";
                $result = execute($sql, [$order['user_id'], $item['course_id']]);
                
                if (!$result) {
                    throw new Exception('Ошибка при предоставлении доступа к курсу ID ' . $item['course_id']);
                }
            }
        }
        
        // Обновляем статус заказа на 'paid' (вместо 'completed')
        $result = updateOrderStatus($orderId, 'paid');
        if (!$result) {
            throw new Exception('Ошибка при обновлении статуса заказа');
        }
        
        commit();
        return true;
        
    } catch (Exception $e) {
        rollback();
        error_log("Ошибка в completeOrder: " . $e->getMessage());
        return false;
    }
}

// Получение заказов пользователя
function getUserOrders($userId) {
    $sql = "SELECT o.*, 
                   COUNT(oi.id) as items_count
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC";
    return query($sql, [$userId]);
}
?>