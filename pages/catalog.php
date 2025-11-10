<?php
// Получение параметров фильтрации
$filters = [
    'category' => $_GET['category'] ?? '',
    'price_min' => isset($_GET['price_min']) ? (int)$_GET['price_min'] : null,
    'price_max' => isset($_GET['price_max']) ? (int)$_GET['price_max'] : null,
    'rating' => isset($_GET['rating']) ? (float)$_GET['rating'] : '',
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? '',
    'price_range' => $_GET['price_range'] ?? ''
];

// Обработка price_range для установки price_min и price_max
if (!empty($filters['price_range'])) {
    switch ($filters['price_range']) {
        case 'free':
            $filters['price_min'] = 0;
            $filters['price_max'] = 0;
            break;
        case '0-1000':
            $filters['price_min'] = 0;
            $filters['price_max'] = 1000;
            break;
        case '1000-2000':
            $filters['price_min'] = 1000;
            $filters['price_max'] = 2000;
            break;
        case '2000-3000':
            $filters['price_min'] = 2000;
            $filters['price_max'] = 3000;
            break;
    }
}

// Получение курсов с фильтрацией
$courses = getCourses($filters);
$categories = getCategories();


?>

<section class="catalog-section">
    <div class="catalog-header">
        <div class="container">
            <h1 style="font-size: 48px; font-weight: 700; margin-bottom: 16px;">Каталог курсов</h1>
            <p style="font-size: 18px; opacity: 0.9;">Выберите подходящий курс для ваших целей</p>

            <!-- Форма поиска по центру -->
            <form method="GET" action="" style="position: relative; max-width: 400px; margin: 20px auto;">
                <input type="hidden" name="page" value="catalog">
                <div style="position: relative;">
                    <input type="text" 
                           name="search" 
                           placeholder="Поиск по курсам..." 
                           value="<?= htmlspecialchars($filters['search']) ?>"
                           style="width: 100%; 
                                  padding: 12px 45px 12px 16px; 
                                  border: 2px solid #e1e5e9; 
                                  border-radius: 25px; 
                                  font-size: 16px;
                                  transition: all 0.3s ease;
                                  border: none;
                                  user-select: none;"
                           onblur="this.style.borderColor='#e1e5e9'">
                    <button type="submit" 
                            style="position: absolute; 
                                   right: 12px; 
                                   top: 50%; 
                                   transform: translateY(-50%); 
                                   background: none; 
                                   border: none; 
                                   cursor: pointer;
                                   color: #6c757d;
                                   font-size: 18px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <!-- Форма фильтров -->
        <form method="GET" action="" id="filter-form">
            <input type="hidden" name="page" value="catalog">
            <input type="hidden" name="search" value="<?= htmlspecialchars($filters['search']) ?>">
            
            <div class="catalog-grid">
                <!-- Filters -->
                <div>
                    <div class="filters-sidebar">
                        <!-- Фильтр по цене -->
                        <div class="filter-group">
                            <div class="filter-title">Цена</div>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="price_range" 
                                           value="free" 
                                           <?= ($_GET['price_range'] ?? '') === 'free' ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    Бесплатные
                                </label>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="price_range" 
                                           value="0-1000" 
                                           <?= ($_GET['price_range'] ?? '') === '0-1000' ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    С 0₽ до 1 000₽
                                </label>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="price_range" 
                                           value="1000-2000" 
                                           <?= ($_GET['price_range'] ?? '') === '1000-2000' ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    С 1000₽ до 2 000₽
                                </label>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="price_range" 
                                           value="2000-3000" 
                                           <?= ($_GET['price_range'] ?? '') === '2000-3000' ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    С 2000₽ до 3 000₽
                                </label>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="price_range" 
                                           value="" 
                                           <?= empty($_GET['price_range'] ?? '') ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    Любая цена
                                </label>
                            </div>
                        </div>

                        <!-- Фильтр по категориям -->
                        <div class="filter-group">
                            <div class="filter-title">Категория</div>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="category" 
                                           value="" 
                                           <?= empty($filters['category']) ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    Все категории
                                </label>
                                <?php foreach ($categories as $category): ?>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="category" 
                                           value="<?= htmlspecialchars($category['slug']) ?>" 
                                           <?= $filters['category'] == $category['slug'] ? 'checked' : '' ?>
                                           onchange="document.getElementById('filter-form').submit()">
                                    <?= htmlspecialchars($category['name']) ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Кнопка сброса фильтров -->
                        <button type="button" 
                                onclick="window.location.href='?page=catalog'" 
                                style="width: 100%; 
                                       padding: 10px; 
                                       background: #f8f9fa; 
                                       border: 1px solid #dee2e6; 
                                       border-radius: 8px; 
                                       cursor: pointer;
                                       margin-top: 20px;">
                            Сбросить фильтры
                        </button>
                    </div>
                </div>
                
                <!-- Список курсов -->
                <div class="catalog-content">
                    <!-- Индикатор активных фильтров -->
                    <?php if (!empty($filters['search']) || !empty($filters['category']) || !empty($filters['price_range']) || !empty($filters['rating'])): ?>
                    <div style="margin-bottom: 20px; padding: 10px; background: #e7f3ff; border-radius: 8px;">
                        <strong>Активные фильтры:</strong>
                        <?php if (!empty($filters['search'])): ?>
                            <span style="background: #007bff; color: white; padding: 4px 8px; border-radius: 4px; margin-left: 8px;">
                                Поиск: "<?= htmlspecialchars($filters['search']) ?>"
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['category'])): ?>
                            <?php 
                            $category_name = '';
                            foreach ($categories as $cat) {
                                if ($cat['slug'] == $filters['category']) {
                                    $category_name = $cat['name'];
                                    break;
                                }
                            }
                            ?>
                            <span style="background: #ffc107; color: black; padding: 4px 8px; border-radius: 4px; margin-left: 8px;">
                                Категория: <?= htmlspecialchars($category_name) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['price_range'])): ?>
                            <?php 
                            $price_labels = [
                                'free' => 'Бесплатные',
                                '0-1000' => 'С 0₽ до 1 000₽',
                                '1000-2000' => 'С 1000₽ до 2 000₽',
                                '2000-3000' => 'С 2000₽ до 3 000₽'
                            ];
                            ?>
                            <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; margin-left: 8px;">
                                Цена: <?= htmlspecialchars($price_labels[$filters['price_range']] ?? '') ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['rating'])): ?>
                            <span style="background: #6f42c1; color: white; padding: 4px 8px; border-radius: 4px; margin-left: 8px;">
                                Рейтинг: <?= htmlspecialchars($filters['rating']) ?>+
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($courses)): ?>
                        <div class="empty-state">
                            <h3 class="heading-md">Курсы не найдены</h3>
                            <p class="body-md" style="color: var(--gray-600); margin-top: 12px;">
                                Попробуйте изменить параметры фильтрации
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="courses-flex">
                            <?php foreach ($courses as $course): 
                                $courseRating = getCourseRatingFromReviews($course['id']);
                            ?>
                            <div class="course-card">
                                <div class="course-image">
                                    <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <?php if ($course['is_popular']): ?>
                                        <div class="popular-badge">Популярный</div>
                                    <?php endif; ?>
                                </div>
                                <div class="course-content">
                                    <span class="course-category"><?php echo htmlspecialchars($course['category_name']); ?></span>
                                    <h3 class="heading-sm"><?php echo htmlspecialchars($course['title']); ?></h3>
                                    <p class="body-sm" style="color: var(--gray-600); margin-top: 12px;">
                                        <?php echo htmlspecialchars(mb_substr($course['description'], 0, 100)); ?>...
                                    </p>
                                    <div class="course-meta">
                                        <div class="course-price">
                                            <?php echo $course['price'] == 0 ? 'Бесплатно' : number_format($course['price'], 0, ',', ' ') . '₽'; ?>
                                        </div>
                                        <div class="course-rating">
                                            <?php if ($courseRating['count'] > 0): ?>
                                                <?php echo number_format($courseRating['rating'], 1); ?> ⭐
                                                <span style="font-size: 12px; color: var(--gray-600); margin-left: 4px;">
                                                    (<?php echo $courseRating['count']; ?>)
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--gray-400);">Нет отзывов</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="index.php?page=course&id=<?php echo $course['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.popular-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--warning);
    color: white;
    padding: 4px 8px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 600;
}

.course-image {
    position: relative;
}
</style>