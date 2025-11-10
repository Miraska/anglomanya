<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/orders.php';

// Создание фейкового платежа в ЮKassa
function createYuKassaPayment($orderId, $amount, $description) {
    // Генерируем фейковый ID платежа
    $paymentId = 'fake_payment_' . uniqid();
    
    // Сохраняем ID платежа в заказе
    updateOrderStatus($orderId, 'pending', $paymentId);
    
    return [
        'success' => true,
        'payment_id' => $paymentId,
        'confirmation_url' => SITE_URL . '/index.php?page=payment_process&order_id=' . $orderId . '&payment_id=' . $paymentId
    ];
}

// Обработка фейкового платежа
function processFakePayment($orderId, $paymentId) {
    $order = getOrderById($orderId);
    if (!$order || $order['status'] !== 'pending') {
        return ['success' => false, 'message' => 'Заказ не найден или уже обработан'];
    }

    // Имитируем успешную оплату
    sleep(2);
    
    // 1. Обновляем статус на 'paid'
    updateOrderStatus($orderId, 'paid', $paymentId);
    
    // 2. СРАЗУ даем доступ к курсам
    $orderItems = query("SELECT course_id FROM order_items WHERE order_id = ?", [$orderId]);
    foreach ($orderItems as $item) {
        $existing = queryOne(
            "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?", 
            [$order['user_id'], $item['course_id']]
        );
        if (!$existing) {
            query(
                "INSERT INTO user_courses (user_id, course_id, purchased_at) VALUES (?, ?, NOW())", 
                [$order['user_id'], $item['course_id']]
            );
        }
    }
    
    return ['success' => true, 'message' => 'Оплата прошла успешно! Курсы добавлены в ваш аккаунт.'];
}

// Проверка статуса платежа
function checkPaymentStatus($orderId) {
    $order = getOrderById($orderId);
    if (!$order) {
        return ['success' => false, 'message' => 'Заказ не найден'];
    }
    
    return [
        'success' => true,
        'status' => $order['status'],
        'payment_id' => $order['payment_id']
    ];
}
?>