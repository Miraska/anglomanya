<?php
requireAuth();
require_once __DIR__ . '/../includes/orders.php';
require_once __DIR__ . '/../includes/payment.php';

if (!isset($_GET['order_id']) || !isset($_GET['payment_id'])) {
    header('Location: index.php?page=courses');
    exit;
}

$orderId = $_GET['order_id'];
$paymentId = $_GET['payment_id'];

// Проверяем, что заказ принадлежит текущему пользователю
$order = getOrderById($orderId);
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: index.php?page=courses');
    exit;
}

// Обрабатываем платеж
$paymentResult = processFakePayment($orderId, $paymentId);

if ($paymentResult['success']) {
    header('Location: index.php?page=payment_success&order_id=' . $orderId);
    exit;
} else {
    header('Location: index.php?page=payment_failed&order_id=' . $orderId . '&error=' . urlencode($paymentResult['message']));
    exit;
}
?>