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
    // Проверяем, что заказ существует и ожидает оплаты
    $order = getOrderById($orderId);
    if (!$order) {
        return ['success' => false, 'message' => 'Заказ не найден'];
    }
    
    if ($order['status'] !== 'pending') {
        return ['success' => false, 'message' => 'Заказ уже обработан. Статус: ' . $order['status']];
    }
    
    if ($order['payment_id'] !== $paymentId) {
        return ['success' => false, 'message' => 'Неверный ID платежа'];
    }
    
    // Имитируем обработку платежа
    sleep(2);
    
    // Завершаем заказ
    $result = completeOrder($orderId);
    
    if ($result) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Ошибка при завершении заказа'];
    }
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