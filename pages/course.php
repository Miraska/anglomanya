<?php
// Получение ID курса из URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получение данных курса
$course = getCourseById($courseId);
if (!$course) {
    include '404.php';
    exit;
}

// Получение количества товаров в корзине (ДОБАВЬТЕ ЭТО В НАЧАЛО)
$cartCount = 0;
if ($currentUser) {
    $cartCount = getCartCount($currentUser['id']);
}

// Получение отзывов для курса
$reviews = getCourseReviews($courseId);

// В начале файла, после получения отзывов, добавить:
$userCanReview = false;
if ($currentUser && $hasAccess) {
    // Проверяем, оставлял ли пользователь уже отзыв
    $userReview = array_filter($reviews, function($review) use ($currentUser) {
        return $review['user_id'] == $currentUser['id'];
    });
    $userCanReview = empty($userReview);
}

// Проверяем, есть ли у пользователя доступ к курсу
$hasAccess = false;
$purchaseDate = null;
if ($currentUser) {
    $hasAccess = hasAccessToCourse($currentUser['id'], $courseId);
    // Получаем дату покупки если есть доступ
    if ($hasAccess) {
        $purchaseInfo = queryOne(
            "SELECT purchased_at FROM user_courses WHERE user_id = ? AND course_id = ?",
            [$currentUser['id'], $courseId]
        );
        $purchaseDate = $purchaseInfo ? $purchaseInfo['purchased_at'] : null;
    }
}

// Обработка добавления отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    if ($currentUser) {
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        
        if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
            $result = addCourseReview($courseId, $currentUser['id'], $rating, $comment);
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
                header("Location: ?page=course&id=$courseId");
                exit;
            }

            else{
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
}

// Обработка удаления отзыва
if (isset($_GET['delete_review'])) {
    if ($currentUser) {
        $reviewId = (int)$_GET['delete_review'];
        deleteCourseReview($reviewId, $currentUser['id']);
        header("Location: ?page=course&id=$courseId");
        exit;
    }
}

// Обработка добавления в корзину (только если нет доступа)
if (isset($_POST['add_to_cart']) && !$hasAccess) {
    if ($currentUser) {
        $result = addToCart($currentUser['id'], $courseId);
        $cartCount = getCartCount();
        
        $cartMessage = $result['success'] ? 'Курс добавлен в корзину!' : $result['message'];
        header("Location: ?page=course&id=$courseId");
        exit;
    } else {
        $cartMessage = 'Для добавления в корзину необходимо авторизоваться';
    }
}

// Получение среднего рейтинга
$courseRating = getCourseRating($courseId);
$averageRating = $courseRating['rating'];
$reviewCount = $courseRating['count'];
?>

<section class="course-detail-section">
    <div class="container">
        <!-- Сообщения -->
        <?php if (isset($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        <div class="course-detail">
            <!-- Хлебные крошки -->
            <nav class="breadcrumb">
                <a href="index.php">Главная</a>
                <span>/</span>
                <a href="index.php?page=catalog">Каталог курсов</a>
                <span>/</span>
                <span><?= htmlspecialchars($course['title']) ?></span>
            </nav>

            <div class="course-hero">
                <div class="course-hero-grid">
                    <div class="course-info">
                        <span class="course-category"><?= htmlspecialchars($course['category_name']) ?></span>
                        <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
                        <p class="course-description" style="font-weight: bold;"><?= htmlspecialchars($course['description']) ?></p>
                        <p class="course-description" id="full_d"><?= htmlspecialchars($course['full_description']) ?></p>
                        
                        <div class="course-meta-info">
                            <div class="course-rating">
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= round($averageRating) ? 'filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-text"><?= number_format($averageRating, 1) ?> (<?= $reviewCount ?> отзывов)</span>
                            </div>
                            
                            <?php if ($course['is_popular']): ?>
                            <div class="popular-badge">
                                <i class="fas fa-fire"></i>
                                Популярный курс
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Блок оформления или доступа -->
                    <?php if ($hasAccess): ?>
                    <!-- Блок для купленного курса -->
                    <div class="access-card">
                        <div class="access-header">
                            <?php if ($purchaseDate): ?>
                            <div class="purchase-date">
                                Приобретен: <?= date('d.m.Y', strtotime($purchaseDate)) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-image-main">
                            <img src="<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                        </div>
                        
                        <div class="access-actions">
                            <a href="index.php?page=course-view&id=<?= $courseId ?>" class="btn btn-primary btn-access">
                                <i class="fas fa-play-circle"></i>
                                Перейти к обучению
                            </a>
                            
                            <?php if ($course['pdf_file']): ?>
                            <a href="<?= htmlspecialchars($course['pdf_file']) ?>" class="btn btn-secondary" target="_blank" download>
                                <i class="fas fa-download"></i>
                                Скачать материалы
                            </a>
                            <?php endif; ?>
                            
                            <a href="#reviews" class="btn btn-outline" onclick="switchTab('reviews')">
                                <i class="fas fa-star"></i>
                                Оставить отзыв
                            </a>
                        </div>
                        
                        <div class="access-features">
                            <h4>Ваши преимущества:</h4>
                            <div class="features-list">
                                <div class="feature">
                                    <i class="fas fa-infinity"></i>
                                    <span>Пожизненный доступ</span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>Доступ с любого устройства</span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-sync"></i>
                                    <span>Обновления курса бесплатно</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Блок для некупленного курса -->
                    <div class="enroll-card">
                        <div class="course-image-main">
                            <img src="<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                        </div>
                        
                        <div class="price-section">
                            <div class="price-label">Цена курса</div>
                            <div class="price-main">
                                <?php if ($course['price'] == 0): ?>
                                    Бесплатно
                                <?php else: ?>
                                    <?= number_format($course['price'], 0, ',', ' ') ?> рублей
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (isset($cartMessage)): ?>
                        <div class="cart-message <?= !strpos($cartMessage, 'успешно') ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($cartMessage) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($currentUser && !isInCart($courseId)): ?>
                        <form method="POST" class="cart-form">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-add-to-cart">
                                <i class="fas fa-shopping-cart"></i>
                                Добавить в корзину
                            </button>
                        </form>
                        <?php elseif($currentUser && isInCart($courseId)): ?>
                            <a class="btn btn-primary btn-add-to-cart" href="?page=cart">
                                <i class="fas fa-shopping-cart"></i>
                                Перейти в корзину
                            </a>
                        <?php else: ?>
                        <a href="index.php?page=login" class="btn btn-primary btn-add-to-cart">
                            <i class="fas fa-sign-in-alt"></i>
                            Войти для покупки
                        </a>
                        <?php endif; ?>
                        
                        <div class="course-guarantees">
                            <div class="guarantee">
                                <i class="fas fa-shield-alt"></i>
                                <span>Гарантия возврата 30 дней</span>
                            </div>
                            <div class="guarantee">
                                <i class="fas fa-lock"></i>
                                <span>Безопасная оплата</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Вкладки -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab active" data-tab="description">Описание</button>
                    <button class="tab" data-tab="reviews">Отзывы (<?= $reviewCount ?>)</button>
                    <?php if ($course['pdf_file']): ?>
                    <button class="tab" data-tab="materials">Материалы</button>
                    <?php endif; ?>
                </div>

                <!-- Содержимое вкладки "Описание" -->
                <div class="tab-content active" id="description-content">
                    <div class="course-full-description">
                        <h3>О курсе</h3>
                        <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                        
                        <div class="course-features">
                            <h4>Что вас ждет:</h4>
                            <div class="features-grid">
                                <div class="feature-item">
                                    <i class="fas fa-video"></i>
                                    <span>Видеоуроки</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-tasks"></i>
                                    <span>Практические задания</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-certificate"></i>
                                    <span>Сертификат об окончании</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Пожизненный доступ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Содержимое вкладки "Отзывы" -->
                <div class="tab-content" id="reviews-content">
                    <h3>Отзывы студентов <span class="review-count">(<?= $reviewCount ?> отзывов)</span></h3>
                    
                    <!-- Форма добавления отзыва -->
                    <?php if ($currentUser && $hasAccess): ?>
                    <div class="add-review-form">
                        <h4>Оставить отзыв</h4>
                        <form method="POST" id="reviewForm">
                            <div class="form-group">
                                <label class="form-label">Ваша оценка</label>
                                <div class="rating-input-container">
                                    <div class="rating-stars-input" id="ratingStars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="rating-star" data-rating="<?= $i ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="selectedRating" value="0" required>
                                    <div class="rating-text" id="ratingText">Выберите оценку</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Комментарий</label>
                                <textarea class="form-input" name="comment" placeholder="Поделитесь вашим опытом..." rows="4" required></textarea>
                            </div>
                            <button type="submit" name="add_review" class="btn btn-primary">Опубликовать отзыв</button>
                        </form>
                    </div>
                    <?php elseif ($currentUser && !$hasAccess): ?>
                    <div class="access-prompt">
                        <p>Чтобы оставить отзыв, необходимо приобрести курс</p>
                    </div>
                    <?php else: ?>
                    <div class="login-prompt">
                        <p>Чтобы оставить отзыв, <a href="index.php?page=login">войдите в систему</a></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Список отзывов -->
                    <div class="reviews-list">
                        <?php if (empty($reviews)): ?>
                            <div class="no-reviews">
                                <p>Пока нет отзывов. Будьте первым!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <?if(isset($review['user_avatar'])):?>
                                        <img src="<?=$review['user_avatar']?>" class="avatar-image" alt="avatar">
                                    <?endif?>
                                    <div class="reviewer-info">
                                        <div class="reviewer-name"><?= htmlspecialchars($review['user_name']) ?></div>
                                        <div class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-comment">
                                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                </div>
                                <?php if ($currentUser && $currentUser['id'] == $review['user_id']): ?>
                                <div class="review-actions">
                                    <a href="?page=course&id=<?= $courseId ?>&delete_review=<?= $review['id'] ?>" 
                                       class="btn btn-ghost btn-sm"
                                       onclick="return confirm('Удалить отзыв?')">
                                        <i class="fas fa-trash"></i>
                                        Удалить
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Содержимое вкладки "Материалы" -->
                <?php if ($course['pdf_file']): ?>
                <div class="tab-content" id="materials-content">
                    <h3>Учебные материалы</h3>
                    <div class="materials-list">
                        <div class="material-item">
                            <i class="fas fa-file-pdf"></i>
                            <div class="material-info">
                                <h4>Учебное пособие</h4>
                                <p>PDF файл с материалами курса</p>
                            </div>
                            <a href="<?= htmlspecialchars($course['pdf_file']) ?>" class="btn btn-primary" download>
                                <i class="fas fa-download"></i>
                                Скачать
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Стили для страницы курса */
.course-detail-section {
    padding: 40px 0;
    background: var(--gray-50);
    min-height: 100vh;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 30px;
    font-size: 14px;
    color: var(--gray-600);
}

.breadcrumb a {
    color: var(--primary-500);
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.course-hero {
    background: white;
    border-radius: var(--radius-lg);
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-md);
}

.course-hero-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 60px;
    align-items: start;
}

.course-category {
    display: inline-block;
    background: var(--primary-100);
    color: var(--primary-600);
    padding: 6px 12px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 16px;
}

.course-title {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: var(--gray-900);
    line-height: 1.2;
}

.course-description {
    font-size: 18px;
    line-height: 1.6;
    color: var(--gray-600);
    margin-bottom: 24px;
}

.course-meta-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.course-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rating-stars {
    display: flex;
    gap: 2px;
}

.rating-stars-input {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.rating-star {
    font-size: 32px;
    color: var(--gray-300);
    cursor: pointer;
    transition: all 0.2s ease;
    line-height: 1;
}

.rating-star:hover,
.rating-star.active {
    color: #ffc107;
    transform: scale(1.1);
}

.rating-star.active ~ .rating-star {
    color: var(--gray-300);
    transform: scale(1);
}

/* Hover эффект для контейнера рейтинга */
.rating-stars-input:hover .rating-star {
    color: #ffc107;
}

.rating-stars-input .rating-star:hover ~ .rating-star {
    color: var(--gray-300);
}

.star {
    font-size: 16px;
    color: var(--gray-300);
}

.star.filled {
    color: #ffc107;
}

.rating-text {
    font-size: 14px;
    color: var(--gray-600);
    min-height: 20px;
}

.popular-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--warning);
    color: white;
    padding: 6px 12px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
}

/* Блок оформления для некупленного курса */
.enroll-card {
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    padding: 24px;
    border: 1px solid var(--gray-200);
    position: sticky;
    top: 100px;
}

/* Блок доступа для купленного курса */
.access-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: var(--radius-lg);
    padding: 24px;
    border: 2px solid var(--primary-200);
    position: sticky;
    top: 100px;
}

.access-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--primary-100);
}

.purchase-date {
    font-size: 14px;
    color: var(--gray-600);
}

.course-image-main {
    width: 100%;
    height: 200px;
    border-radius: var(--radius-md);
    overflow: hidden;
    margin-bottom: 20px;
}

.course-image-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Действия для купленного курса */
.access-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.btn-access {
    background: var(--success);
    border-color: var(--success);
}

.btn-access:hover {
    background: var(--primary-600);
    border-color: var(--primary-600);
}

.access-features {
    background: white;
    padding: 16px;
    border-radius: var(--radius-md);
    border: 1px solid var(--primary-100);
}

.access-features h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: var(--gray-700);
}

.features-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-600);
}

.feature i {
    color: var(--success);
    width: 16px;
}

/* Блок цены для некупленного курса */
.price-section {
    margin-bottom: 20px;
}

.price-label {
    font-size: 14px;
    color: var(--gray-600);
    margin-bottom: 8px;
}

.price-main {
    font-size: 28px;
    font-weight: 700;
    color: var(--gray-900);
}

.cart-message {
    padding: 12px;
    border-radius: var(--radius-md);
    margin-bottom: 16px;
    font-size: 14px;
}

.cart-message.success {
    background: var(--success);
    color: white;
}

.cart-message.error {
    background: var(--error);
    color: white;
}

.btn-add-to-cart {
    width: 100%;
    margin-bottom: 12px;
}

.course-guarantees {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 16px;
}

.guarantee {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-600);
}

.guarantee i {
    color: var(--primary-500);
    width: 16px;
}

/* Вкладки */
.tabs-container {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.tabs {
    display: flex;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}

.tab {
    padding: 16px 24px;
    background: none;
    border: none;
    font-size: 16px;
    font-weight: 500;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.tab:hover {
    color: var(--primary-500);
    background: var(--gray-100);
}

.tab.active {
    color: var(--primary-600);
    border-bottom-color: var(--primary-500);
    background: white;
}

.tab-content {
    display: none;
    padding: 40px;
}

.tab-content.active {
    display: block;
}

/* Сообщения */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
    border: 1px solid transparent;
}

.alert-success {
    background: #f0f9ff;
    color: #0369a1;
    border-color: #bae6fd;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
}



/* Содержимое вкладок */
.course-full-description h3 {
    margin-bottom: 20px;
    color: var(--gray-900);
}

.course-full-description p {
    line-height: 1.7;
    color: var(--gray-600);
    margin-bottom: 30px;
}

.course-features {
    margin-top: 30px;
}

.course-features h4 {
    margin-bottom: 20px;
    color: var(--gray-800);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--gray-50);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.feature-item i {
    color: var(--primary-500);
    font-size: 18px;
    width: 20px;
}

/* Форма отзыва */
.add-review-form {
    background: var(--gray-50);
    padding: 24px;
    border-radius: var(--radius-lg);
    margin-bottom: 30px;
    border: 1px solid var(--gray-200);
}

.add-review-form h4 {
    margin-bottom: 20px;
}

.rating-input-container {
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--gray-700);
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-500);
}

.form-input textarea {
    resize: vertical;
    min-height: 100px;
}

.login-prompt {
    text-align: center;
    padding: 40px;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    margin-bottom: 30px;
}

.login-prompt a {
    color: var(--primary-500);
    text-decoration: none;
}

.login-prompt a:hover {
    text-decoration: underline;
}

/* Список отзывов */
.review-count {
    color: var(--gray-600);
    font-size: 16px;
    font-weight: normal;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-item {
    padding: 24px;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    background: var(--gray-50);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.reviewer-info {
    flex: 1;
}

.reviewer-name {
    font-weight: 600;
    margin-bottom: 4px;
}

.review-date {
    font-size: 14px;
    color: var(--gray-600);
}

.review-rating {
    display: flex;
    gap: 2px;
}

.review-comment p {
    line-height: 1.6;
    color: var(--gray-700);
    margin: 0;
}

.review-actions {
    margin-top: 12px;
    text-align: right;
}

/* Материалы */
.materials-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.material-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    background: var(--gray-50);
}

.material-item i {
    font-size: 24px;
    color: var(--error);
    width: 40px;
}

.material-info {
    flex: 1;
}

.material-info h4 {
    margin: 0 0 4px 0;
    color: var(--gray-800);
}

.material-info p {
    margin: 0;
    color: var(--gray-600);
    font-size: 14px;
}

.no-reviews {
    text-align: center;
    padding: 40px;
    color: var(--gray-600);
}

/* Сообщения */
.access-prompt {
    text-align: center;
    padding: 20px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: var(--radius-md);
    margin-bottom: 20px;
    color: #856404;
}


.avatar-image {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
    border: 3px solid var(--primary);
}

/* Адаптивность */
@media (max-width: 968px) {
    .course-hero-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .enroll-card,
    .access-card {
        position: static;
    }
}

@media (max-width: 768px) {
    .course-hero {
        padding: 24px;
    }
    
    .course-title {
        font-size: 28px;
    }
    
    .tab-content {
        padding: 24px;
    }
    
    .tabs {
        flex-wrap: wrap;
    }
    
    .tab {
        flex: 1;
        min-width: 120px;
        text-align: center;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .access-header {
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .course-detail-section {
        padding: 20px 0;
    }
    
    .course-hero {
        padding: 20px;
    }
    
    .tab-content {
        padding: 20px;
    }
    
    .review-header {
        flex-direction: column;
        gap: 8px;
    }
    
    .material-item {
        flex-direction: column;
        text-align: center;
    }
    
    .access-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// JavaScript для системы рейтинга
class RatingSystem {
    constructor() {
        this.ratingStars = document.getElementById('ratingStars');
        this.selectedRating = document.getElementById('selectedRating');
        this.ratingText = document.getElementById('ratingText');
        
        if (this.ratingStars) {
            this.init();
        }
    }
    
    init() {
        const stars = this.ratingStars.querySelectorAll('.rating-star');
        
        // Обработчик клика по звезде
        stars.forEach(star => {
            star.addEventListener('click', (e) => {
                const rating = parseInt(star.getAttribute('data-rating'));
                this.setRating(rating);
            });
        });
        
        // Обработчики hover
        this.ratingStars.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains('rating-star')) {
                this.hoverRating(parseInt(e.target.getAttribute('data-rating')));
            }
        });
        
        this.ratingStars.addEventListener('mouseout', () => {
            this.resetHover();
        });
    }
    
    setRating(rating) {
        const stars = this.ratingStars.querySelectorAll('.rating-star');
        
        // Обновляем визуальное отображение
        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            if (starRating <= rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
        
        // Обновляем скрытое поле
        this.selectedRating.value = rating;
        
        // Обновляем текст
        this.updateRatingText(rating);
    }
    
    hoverRating(rating) {
        const stars = this.ratingStars.querySelectorAll('.rating-star');
        
        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            if (starRating <= rating) {
                star.style.color = '#ffc107';
            } else {
                star.style.color = 'var(--gray-300)';
            }
        });
    }
    
    resetHover() {
        const stars = this.ratingStars.querySelectorAll('.rating-star');
        const currentRating = parseInt(this.selectedRating.value);
        
        if (currentRating === 0) {
            // Если оценка не выбрана, сбрасываем все к серому
            stars.forEach(star => {
                star.style.color = '';
            });
        } else {
            // Если оценка выбрана, показываем выбранный рейтинг
            this.setRating(currentRating);
        }
    }
    
    updateRatingText(rating) {
        const texts = {
            1: 'Плохо',
            2: 'Неудовлетворительно',
            3: 'Удовлетворительно',
            4: 'Хорошо',
            5: 'Отлично'
        };
        
        this.ratingText.textContent = texts[rating] || 'Выберите оценку';
    }
}

// JavaScript для переключения вкладок
function switchTab(tabName) {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Убираем активный класс у всех вкладок и содержимого
    tabs.forEach(t => t.classList.remove('active'));
    tabContents.forEach(c => c.classList.remove('active'));
    
    // Добавляем активный класс текущей вкладке и соответствующему содержимому
    const targetTab = document.querySelector(`.tab[data-tab="${tabName}"]`);
    const targetContent = document.getElementById(tabName + '-content');
    
    if (targetTab && targetContent) {
        targetTab.classList.add('active');
        targetContent.classList.add('active');
    }
}

// Валидация формы отзыва
function validateReviewForm() {
    const form = document.getElementById('reviewForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const rating = parseInt(document.getElementById('selectedRating').value);
        const comment = form.querySelector('textarea[name="comment"]').value.trim();
        
        if (rating === 0) {
            e.preventDefault();
            alert('Пожалуйста, выберите оценку');
            return false;
        }
        
        if (comment.length < 10) {
            e.preventDefault();
            alert('Комментарий должен содержать минимум 10 символов');
            return false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация системы рейтинга
    new RatingSystem();
    
    // Обработчики для вкладок
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            switchTab(targetTab);
        });
    });
    
    // Валидация формы
    validateReviewForm();
    
    // Плавная прокрутка к отзывам при клике на кнопку
    document.querySelectorAll('a[href="#reviews"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            switchTab('reviews');
            setTimeout(() => {
                document.getElementById('reviews-content').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        });
    });

    let full_d = document.getElementById('full_d');
    let text = full_d.textContent;
    let maxLength = 500;

    if (text.length > maxLength) {
        // Сохраняем оригинальный текст
        let originalText = text;
        let shortenedText = text.slice(0, maxLength) + "...";
        
        // Устанавливаем сокращенный текст
        full_d.textContent = shortenedText;
        
        // Создаем кнопку
        let toggleButton = document.createElement('button');
        toggleButton.textContent = 'Раскрыть больше';
        toggleButton.style.cssText = `
            display: block;
            margin-bottom: 10px;
            color: black;
            border: none;
            background: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        `;
        
        // Добавляем кнопку после текста
        full_d.parentNode.insertBefore(toggleButton, full_d.nextSibling);
        
        // Обработчик клика на кнопку
        toggleButton.addEventListener('click', function() {
            if (full_d.textContent === shortenedText) {
                // Показываем полный текст
                full_d.textContent = originalText;
                toggleButton.textContent = 'Скрыть';
            } else {
                // Показываем сокращенный текст
                full_d.textContent = shortenedText;
                toggleButton.textContent = 'Раскрыть больше';
            }
        });
    }

});
</script>