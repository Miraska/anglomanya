<?php
// Получение популярных курсов
$popularCourses = getPopularCourses(3);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span>🎯 Лучший подход к студенту</span>
            </div>
            <h1 class="display-xl">Английский, который<br><span class="primary-500">работает</span> в реальной жизни</h1>
            <p class="body-xl" style="margin-top: 24px; opacity: 0.9;">
                Интерактивная платформа для изучения английского с персональной
                программой обучения. Достигайте целей в 3 раза быстрее.
            </p>
            <div class="hero-actions">
                <a href="?page=catalog" class="btn btn-outline-white btn-lg">
                    📚 Смотреть курсы
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="why-us">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Почему выбирают нас</span>
            <h2 class="heading-xl">Обучение нового поколения</h2>
            <p class="body-lg" style="color: var(--gray-600); margin-top: 16px;">
                Современные методики, проверенные результатами тысяч студентов
            </p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <img src="./assets/media/images/icons/1.png" class="feature-icon" alt="Интерфейс"/>
                <h3 class="heading-md">Удобный и понятный интерфейс</h3>
                <p class="body-md" style="color: var(--gray-600); margin-top: 12px;">
                    На нашем сайте современный и интуитивно понятный интерфейс
                </p>
            </div>
            <div class="feature-card">
                <img src="./assets/media/images/icons/2.png" class="feature-icon" alt="Курсы"/>
                <h3 class="heading-md">Профессиональные курсы</h3>
                <p class="body-md" style="color: var(--gray-600); margin-top: 12px;">
                    Наши курсы одни из лучших, потому что составлялись опытными преподавателями
                </p>
            </div>
            <div class="feature-card">
                <img src="./assets/media/images/icons/3.png" class="feature-icon" alt="Геймификация"/>
                <h3 class="heading-md">Геймификация</h3>
                <p class="body-md" style="color: var(--gray-600); margin-top: 12px;">
                    Игровые элементы делают обучение увлекательным и повышают мотивацию
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Courses -->

<section class="courses" id="popular-courses">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Начните сегодня</span>
            <h2 class="heading-xl">Популярные курсы</h2>
        </div>
        <div class="courses-flex">
            <?php foreach ($popularCourses as $course): 
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
    </div>
</section>

<!-- Stats Section -->
<section class="stats" id="about-us">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10K+</div>
                <div class="body-md" style="color: var(--gray-400);">Довольных студентов</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">96%</div>
                <div class="body-md" style="color: var(--gray-400);">Достигают целей</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4.9/5</div>
                <div class="body-md" style="color: var(--gray-400);">Средний рейтинг</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="body-md" style="color: var(--gray-400);">Поддержка</div>
            </div>
        </div>
    </div>
</section>



<!-- TEACHERS -->
<section class="teachers">
    <div class="teachers-header">
        <p class="section-subtitle">Представляем наш сайт и преподавателей</p>
        <p class="body-lg">кто делал курсы</p>
        <h2 class="heading-xl">О нас и наши преподаватели</h2>
    </div>

    <div class="teachers-slider">
        <button class="nav-btn prev">&#8592;</button>
        <div class="slider-container">
            <div class="slider-track">
                <!-- Слайды будут добавлены динамически через JS -->
            </div>
        </div>
        <button class="nav-btn next">&#8594;</button>
    </div>

    <div class="slider-dots">
        <!-- Точки будут добавлены динамически через JS -->
    </div>
</section>



<section class="faq" id="faq">
    <h3 style="text-align: center;" class="heading-xl">Часто Задаваемые Вопросы</h3>

    <div class="container">
        <div class="faq-inner">
            <div class="faq">
                <button class="accordion">
                    Сколько времени в среднем длится один курс?
                </button>
                <div class="panel">
                <p>Обычно продолжительность одного курса по английскому языку составляет от одного до шести месяцев, в зависимости от целей и формата обучения. Например, базовый курс для начинающих часто длится около двух месяцев, включающих примерно 20–30 занятий по 1–1,5 часа каждое. Такой курс помогает заложить фундаментальные знания по грамматике, лексике и навыкам коммуникации.</p>
                </div>
            </div>
        
            <div class="faq">
                <button class="accordion">
                    Какая комиссия при оплате?</button>
                </button>
                <div class="panel">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                </div>
            </div>
        
            <div class="faq">
                <button class="accordion">
                    Есть ли скидки на курсы?</button>
                </button>
                <div class="panel">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                </div>
            </div>
        </div>
    </div>
</section>