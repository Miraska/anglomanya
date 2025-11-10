<?php
requireAuth();
require_once __DIR__ . '/../includes/orders.php';

if (!isset($_GET['order_id'])) {
    header('Location: index.php?page=courses');
    exit;
}

$orderId = $_GET['order_id'];
$order = getOrderById($orderId);
$orderItems = getOrderItems($orderId);

// Проверяем, что заказ принадлежит текущему пользователю
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: index.php?page=courses');
    exit;
}

// Проверяем статус заказа
$statusMessage = '';
$statusClass = '';
switch ($order['status']) {
    case 'paid':
        $statusMessage = 'Оплата подтверждена! Курсы добавлены в ваш аккаунт.';
        $statusClass = 'status-completed';
        break;
    case 'pending':
        $statusMessage = 'Оплата прошла успешно! Ожидайте подтверждения администратора.';
        $statusClass = 'status-pending';
        break;
    case 'cancelled':
        $statusMessage = 'Заказ отменен. Деньги будут возвращены.';
        $statusClass = 'status-cancelled';
        break;
}
?>

<section class="payment-success-section">
    <div class="container">
        <div class="status-info <?php echo $statusClass; ?>" style="margin: 20px 0; padding: 15px; border-radius: 8px; text-align: center;">
            <?php echo $statusMessage; ?>
        </div>
        <div class="success-card">
            <div class="success-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="32" fill="#10B981"/>
                    <path d="M44 24L28.8 40L20 31.2727" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <h1 class="heading-xl" style="text-align: center; margin-top: 24px;">Оплата прошла успешно!</h1>
            
            <div class="success-info">
                <p class="body-lg" style="text-align: center; color: var(--gray-600);">
                    Спасибо за ваш заказ! Теперь у вас есть доступ к выбранным курсам.
                </p>
                
                <div class="order-details">
                    <h3 class="heading-md">Детали заказа</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Номер заказа:</span>
                            <span class="detail-value">#<?php echo $orderId; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Дата:</span>
                            <span class="detail-value"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Сумма:</span>
                            <span class="detail-value"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?>₽</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Статус:</span>
                            <span class="detail-value <?php echo $statusClass; ?>">
                                <?php 
                                echo $order['status'] === 'paid' ? 'Оплачен' : 
                                    ($order['status'] === 'pending' ? 'Ожидает подтверждения' : 'Отменен');
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="purchased-courses">
                    <h3 class="heading-md">Купленные курсы</h3>
                    <div class="courses-list">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="course-item">
                            <div class="course-info">
                                <h4 class="body-md" style="font-weight: 600;"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="body-sm" style="color: var(--gray-600); margin-top: 4px;">
                                    <?php echo htmlspecialchars($item['category_name']); ?>
                                </p>
                            </div>
                            <div class="course-price">
                                <?php echo number_format($item['price'], 0, ',', ' '); ?>₽
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="index.php?page=profile" class="btn btn-primary">
                        Перейти к моим курсам
                    </a>
                    <a href="index.php?page=courses" class="btn btn-secondary">
                        Продолжить обучение
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.payment-success-section {
    padding: 80px 0;
}

.success-card {
    max-width: 600px;
    margin: 0 auto;
    background: white;
    padding: 48px;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    text-align: center;
}

.success-icon {
    display: flex;
    justify-content: center;
    align-items: center;
}

.order-details, .purchased-courses {
    margin-top: 32px;
    text-align: left;
}

.details-grid {
    display: grid;
    gap: 12px;
    margin-top: 16px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-200);
}

.detail-label {
    color: var(--gray-600);
}

.detail-value {
    font-weight: 600;
}

.status-completed {
    color: var(--success-600);
    background: var(--success-50);
    padding: 4px 8px;
    border-radius: 4px;
}

.status-pending {
    color: var(--warning-600);
    background: var(--warning-50);
    padding: 4px 8px;
    border-radius: 4px;
}

.status-cancelled {
    color: var(--error-600);
    background: var(--error-50);
    padding: 4px 8px;
    border-radius: 4px;
}

.courses-list {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.course-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 5px;
    padding: 16px;
    background: var(--gray-50);
    border-radius: var(--radius-md);
}

.course-price {
    font-weight: 600;
    color: var(--primary-600);
}

.action-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-top: 32px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .success-card {
        padding: 32px 24px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}
</style>