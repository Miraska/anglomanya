<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/orders.php';

// Создание платежа в ЮKassa
function createYuKassaPayment($orderId, $amount, $description) {
    $order = getOrderById($orderId);
    
    if (!$order) {
        return ['success' => false, 'message' => 'Заказ не найден'];
    }
    
    $url = 'https://api.yookassa.ru/v3/payments';
    
    $data = [
        'amount' => [
            'value' => number_format($amount, 2, '.', ''),
            'currency' => 'RUB'
        ],
        'confirmation' => [
            'type' => 'redirect',
            'return_url' => SITE_URL . '/index.php?page=payment_success&order_id=' . $orderId
        ],
        'capture' => true,
        'description' => $description,
        'metadata' => [
            'order_id' => $orderId
        ]
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Idempotence-Key: ' . uniqid('', true),
        'Authorization: Basic ' . base64_encode(YUKASSA_SHOP_ID . ':' . YUKASSA_SECRET_KEY)
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        
        // Сохранение ID платежа в заказе
        updateOrderStatus($orderId, 'pending', $result['id']);
        
        return [
            'success' => true,
            'payment_id' => $result['id'],
            'confirmation_url' => $result['confirmation']['confirmation_url']
        ];
    }
    
    return ['success' => false, 'message' => 'Ошибка при создании платежа'];
}

// Проверка статуса платежа
function checkYuKassaPayment($paymentId) {
    $url = 'https://api.yookassa.ru/v3/payments/' . $paymentId;
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(YUKASSA_SHOP_ID . ':' . YUKASSA_SECRET_KEY)
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return [
            'success' => true,
            'status' => $result['status'],
            'paid' => $result['paid']
        ];
    }
    
    return ['success' => false, 'message' => 'Ошибка при проверке платежа'];
}

// Обработка уведомления от ЮKassa (webhook)
function handleYuKassaNotification($data) {
    if (!isset($data['object']) || !isset($data['object']['metadata']['order_id'])) {
        return false;
    }
    
    $orderId = $data['object']['metadata']['order_id'];
    $paymentStatus = $data['object']['status'];
    $paymentId = $data['object']['id'];
    
    if ($paymentStatus === 'succeeded') {
        // Завершение заказа и предоставление доступа
        completeOrder($orderId);
        return true;
    } elseif ($paymentStatus === 'canceled') {
        // Отмена заказа
        updateOrderStatus($orderId, 'cancelled', $paymentId);
        return true;
    }
    
    return false;
}
?>