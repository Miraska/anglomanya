<?php
requireAuth();
require_once __DIR__ . '/../includes/cart.php';
require_once __DIR__ . '/../includes/orders.php';
require_once __DIR__ . '/../includes/payment.php';

$cartCourses = getCartCourses();
$cartTotal = getCartTotal();

if (empty($cartCourses)) {
    header('Location: index.php?page=cart');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Создание заказа
    $orderResult = createOrder($_SESSION['user_id'], $cartCourses);
    
    if ($orderResult['success']) {
        $orderId = $orderResult['order_id'];
        
        // Создание платежа в ЮKassa
        $paymentResult = createYuKassaPayment(
            $orderId,
            $cartTotal,
            'Оплата курсов на ' . SITE_NAME
        );
        
        if ($paymentResult['success']) {
            // Очистка корзины
            clearCart();
            
            // Перенаправление на страницу оплаты
            header('Location: ' . $paymentResult['confirmation_url']);
            exit;
        } else {
            $message = '<div class="alert alert-danger">Ошибка при создании платежа: ' . $paymentResult['message'] . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">' . $orderResult['message'] . '</div>';
    }
}
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="heading-xl">Оформление заказа</h1>

        <?php echo $message; ?>

        <div class="checkout-layout">
            <div class="checkout-form">
                <h2 class="heading-lg">Ваши данные</h2>
                
                <form method="POST" style="margin-top: 24px;">
                    <div class="form-group">
                        <label class="form-label">Имя</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($currentUser['name']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                    </div>

                    <div class="payment-info">
                        <h3 class="heading-md">Способ оплаты</h3>
                        <p class="body-sm" style="color: var(--gray-600); margin-top: 12px;">
                            После нажатия кнопки "Оплатить" вы будете перенаправлены на защищенную страницу оплаты ЮKassa
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">
                        Перейти к оплате
                    </button>
                </form>
            </div>

            <div class="checkout-summary">
                <h2 class="heading-lg">Ваш заказ</h2>
                
                <div class="order-items" style="margin-top: 24px;">
                    <?php foreach ($cartCourses as $course): ?>
                    <div class="order-item">
                        <div>
                            <div class="body-md" style="font-weight: 600;"><?php echo htmlspecialchars($course['title']); ?></div>
                            <div class="body-sm" style="color: var(--gray-600); margin-top: 4px;">
                                <?php echo htmlspecialchars($course['category_name']); ?>
                            </div>
                        </div>
                        <div class="body-md" style="font-weight: 600; color: var(--primary-600);">
                            <?php echo number_format($course['price'], 0, ',', ' '); ?>₽
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary" style="margin-top: 24px; padding-top: 24px;">
                    <div class="summary-row" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-300);">
                        <span class="summary-total">Итого:</span>
                        <span class="summary-total"><?php echo number_format($cartTotal, 0, ',', ' '); ?>₽</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.checkout-section {
    padding: 60px 0;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 48px;
    margin-top: 48px;
}

.checkout-form, .checkout-summary {
    background: white;
    padding: 32px;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.order-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 16px;
    background: var(--gray-50);
    border-radius: var(--radius-md);
}


.payment-info {
    margin-top: 32px;
    padding: 20px;
    background: var(--gray-50);
    border-radius: var(--radius-md);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.summary-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-600);
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
}
</style>