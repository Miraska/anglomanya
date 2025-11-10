<?php
requireAuth();
require_once 'includes/cart.php';

// Обработка удаления из корзины
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    removeFromCart($removeId);
    header('Location: index.php?page=cart');
    exit;
}

// Обработка очистки корзины
if (isset($_POST['clear_cart'])) {
    clearCart();
    header('Location: index.php?page=cart');
    exit;
}

$cartCourses = getCartCourses();
$cartTotal = getCartTotal();
$cartCount = count($cartCourses);
?>

<section class="cart-section">
    <div class="container">
        <div class="cart-page">
            <div class="cart-header">
                <h1>Корзина</h1>
                <?php if ($cartCount > 0): ?>
                <div class="cart-count"><?= $cartCount ?> <?= getNounPluralForm($cartCount, 'курс', 'курса', 'курсов') ?></div>
                <?php endif; ?>
            </div>

            <?php if (empty($cartCourses)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Корзина пуста</h3>
                    <p>Добавьте курсы из каталога, чтобы начать обучение</p>
                    <a href="index.php?page=catalog" class="btn btn-primary">
                        <i class="fas fa-book"></i>
                        Перейти в каталог
                    </a>
                </div>
            <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                        <?php foreach ($cartCourses as $course):
                            $courseRating = getCourseRatingFromReviews($course['id']);
                        ?>
                            <div class="cart-item" style="cursor: pointer;" onclick="location.href='?page=course&id=<?= $course['id'] ?>'">
                                <div class="course-image">
                                    <img src="<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                                </div>
                                <div class="course-info">
                                    <h4><?= htmlspecialchars($course['title']) ?></h4>
                                    <p class="course-category"><?= htmlspecialchars($course['category_name']) ?></p>
                                    <div class="course-meta">
                                        <?php if ($courseRating['count'] > 0): ?>
                                            <div>
                                                <?php echo number_format($courseRating['rating'], 1); ?> ⭐
                                                <span style="font-size: 12px; color: var(--gray-600); margin-left: 4px;">
                                                    (<?php echo $courseRating['count']; ?>)
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--gray-400);">Нет отзывов</span>
                                        <?php endif; ?>
                                        <?php if ($course['is_popular']): ?>
                                        <span class="popular-badge">Популярный</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="course-actions">
                                    <div class="course-price"><?= $course['price'] == 0 ? 'Бесплатно' : number_format($course['price'], 0, ',', ' ') . '₽' ?></div>
                                    <a href="?page=cart&remove=<?= $course['id'] ?>" class="btn btn-ghost btn-remove">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Кнопка очистки корзины -->
                        <form method="POST" class="clear-cart-form">
                            <button type="submit" name="clear_cart" class="btn btn-ghost" onclick="return confirm('Очистить всю корзину?')">
                                <i class="fas fa-broom"></i>
                                Очистить корзину
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (!empty($cartCourses)): ?>
                <div class="cart-summary">
                    <h3>Итог заказа</h3>
                    
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Количество курсов:</span>
                            <span><?= $cartCount ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Промежуточный итог:</span>
                            <span><?= number_format($cartTotal, 0, ',', ' ') ?>₽</span>
                        </div>
                        <div class="summary-row total">
                            <span class="summary-total">Итого:</span>
                            <span class="summary-total"><?= number_format($cartTotal, 0, ',', ' ') ?>₽</span>
                        </div>
                    </div>

                    <div class="summary-actions">
                        <a href="index.php?page=checkout" class="btn btn-primary btn-checkout">
                            <i class="fas fa-credit-card"></i>
                            Перейти к оформлению
                        </a>
                        <a href="index.php?page=catalog" class="btn btn-ghost">
                            <i class="fas fa-arrow-left"></i>
                            Продолжить покупки
                        </a>
                    </div>

                    <div class="cart-features">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Безопасная оплата</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Пожизненный доступ</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-certificate"></i>
                            <span>Сертификат об окончании</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.cart-section {
    padding: 40px 0;
    width: 100%;
    background: var(--gray-50);
    min-height: 100vh;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    flex-wrap: wrap;
    gap: 16px;
}

.cart-header h1 {
    margin: 0;
}

.cart-count {
    background: var(--primary-500);
    color: white;
    padding: 8px 16px;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 14px;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    align-items: start;
}

.cart-items {
    background: white;
    border-radius: var(--radius-lg);
    padding: 24px;
    width: 100%;
    box-shadow: var(--shadow-md);
}

.empty-cart {
    text-align: center;
    padding: 60px 40px;
}

.empty-cart-icon {
    width: 80px;
    height: 80px;
    background: var(--gray-200);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    color: var(--gray-600);
    font-size: 32px;
}

.empty-cart h3 {
    margin-bottom: 12px;
    color: var(--gray-800);
}

.empty-cart p {
    color: var(--gray-600);
    margin-bottom: 24px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 20px;
    padding: 20px;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    margin-bottom: 16px;
    background: var(--gray-50);
    transition: all 0.3s ease;
}

.cart-item:hover {
    box-shadow: var(--shadow-md);
    border-color: var(--primary-200);
}

.course-image {
    width: 100px;
    height: 80px;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.course-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-info h4 {
    margin: 0 0 8px 0;
    color: var(--gray-900);
    font-size: 18px;
}

.course-category {
    color: var(--primary-600);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}

.course-meta {
    display: flex;
    gap: 12px;
    align-items: center;
}

.course-rating {
    color: var(--warning);
    font-size: 14px;
    font-weight: 500;
}

.popular-badge {
    background: var(--warning);
    color: white;
    padding: 2px 8px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
}

.course-actions {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-end;
}

.course-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 12px;
}

.btn-remove {
    padding: 6px 12px;
    font-size: 14px;
    color: var(--error);
}

.btn-remove:hover {
    background: var(--error);
    color: white;
}

.clear-cart-form {
    margin-top: 20px;
    text-align: center;
}

.cart-summary {
    background: white;
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 100px;
}

.cart-summary h3 {
    margin: 0 0 24px 0;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--gray-200);
}

.summary-details {
    margin-bottom: 24px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-200);
}

.summary-row.discount {
    color: var(--success);
}

.summary-row.total {
    border-bottom: none;
    padding-top: 16px;
    margin-top: 8px;
    /* border-top: 2px solid var(--gray-300); */
}

.summary-total {
    font-size: 20px;
    font-weight: 700;
    color: var(--gray-900);
}

.summary-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-checkout {
    width: 100%;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
}

.cart-features {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--gray-200);
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    color: var(--gray-600);
    font-size: 14px;
}

.feature-item i {
    color: var(--primary-500);
    width: 16px;
}

.recommended-courses {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid var(--gray-200);
}

.recommended-courses h3 {
    margin-bottom: 24px;
    text-align: center;
}

.recommended-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.recommended-course {
    background: white;
    border-radius: var(--radius-lg);
    padding: 20px;
    border: 1px solid var(--gray-200);
    text-align: center;
    transition: all 0.3s ease;
}

.recommended-course:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.recommended-course .course-image {
    width: 100%;
    height: 120px;
    margin-bottom: 16px;
}

.recommended-course h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: var(--gray-800);
}

.recommended-course .course-price {
    margin-bottom: 16px;
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900);
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

.review-count {
    font-size: 12px;
    color: var(--gray-500);
    margin-left: 4px;
}

/* Адаптивность */
@media (max-width: 968px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 16px;
    }
    
    .course-image {
        margin: 0 auto;
    }
    
    .course-actions {
        align-items: center;
        flex-direction: row;
        justify-content: space-between;
    }
    
    .cart-header {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .cart-section {
        padding: 20px 0;
    }
    
    .cart-items,
    .cart-summary {
        padding: 16px;
    }
    
    .recommended-grid {
        grid-template-columns: 1fr;
    }
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Подтверждение удаления
    const removeButtons = document.querySelectorAll('.btn-remove');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Удалить курс из корзины?')) {
                e.preventDefault();
            }
        });
    });
});
</script>