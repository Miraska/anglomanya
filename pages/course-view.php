<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/courses.php';

// Получение ID курса из URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получение данных курса
$course = getCourseById($courseId);
if (!$course) {
    include '404.php';
    exit;
}

// Проверяем доступ к курсу
$hasAccess = false;
if ($currentUser) {
    $hasAccess = hasAccessToCourse($currentUser['id'], $courseId);
}

if (!$hasAccess) {
    header('Location: index.php?page=course&id=' . $courseId . '&error=no_access');
    exit;
}

// Получаем уроки курса
$lessons = getCourseLessons($courseId);

// Получаем прогресс пользователя
$progress = getCourseProgress($currentUser['id'], $courseId);

// Обработка отметки урока как пройденного
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_completed'])) {
    $lessonId = (int)$_POST['lesson_id'];
    markLessonCompleted($currentUser['id'], $lessonId);
    header("Location: ?page=course-view&id=$courseId");
    exit;
}

// Получаем информацию о покупке
$purchaseInfo = queryOne(
    "SELECT purchased_at FROM user_courses WHERE user_id = ? AND course_id = ?",
    [$currentUser['id'], $courseId]
);
?>

    <section class="course-view-section">
        <div class="container">
            <div class="course-view-layout">
                <!-- Боковая панель с уроками -->
                <div class="course-sidebar">
                    <div class="course-info-sidebar">
                        <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
                        <div class="progress-section">
                            <div class="progress-header">
                                <span>Прогресс курса</span>
                                <span class="progress-percent"><?= $progress['percent'] ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $progress['percent'] ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <nav class="lessons-nav">
                        <h3>Содержание курса</h3>
                        <div class="lessons-list">
                            <?php foreach ($lessons as $index => $lesson): 
                                $isCompleted = isLessonCompleted($currentUser['id'], $lesson['id']);
                            ?>
                            <a href="#lesson-<?= $lesson['id'] ?>" 
                               class="lesson-item <?= $isCompleted ? 'completed' : '' ?>"
                               data-lesson-id="<?= $lesson['id'] ?>">
                                <div class="lesson-number"><?= $index + 1 ?></div>
                                <div class="lesson-info">
                                    <div class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></div>
                                    <div class="lesson-meta">
                                        <?php if ($lesson['content_type'] == 'video'): ?>
                                            <i class="fas fa-play-circle"></i>
                                            <span>Видео • <?= $lesson['duration'] ?> мин</span>
                                        <?php elseif ($lesson['content_type'] == 'text'): ?>
                                            <i class="fas fa-file-alt"></i>
                                            <span>Текст • <?= $lesson['duration'] ?> мин</span>
                                        <?php elseif ($lesson['content_type'] == 'pdf'): ?>
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF • <?= $lesson['duration'] ?> мин</span>
                                        <?php elseif ($lesson['content_type'] == 'quiz'): ?>
                                            <i class="fas fa-question-circle"></i>
                                            <span>Тест • <?= $lesson['duration'] ?> мин</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($isCompleted): ?>
                                <div class="lesson-status">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </nav>

                    <?php if ($course['pdf_file']): ?>
                    <div class="materials-sidebar">
                        <h3>Дополнительные материалы</h3>
                        <a href="<?= htmlspecialchars($course['pdf_file']) ?>" class="download-material" download>
                            <i class="fas fa-download"></i>
                            <span>Скачать учебник (PDF)</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Основной контент курса -->
                <div class="course-content">
                    <?php if (!empty($lessons)): ?>
                        <?php foreach ($lessons as $index => $lesson): 
                            $isCompleted = isLessonCompleted($currentUser['id'], $lesson['id']);
                        ?>
                        <div id="lesson-<?= $lesson['id'] ?>" class="lesson-content">
                            <div class="lesson-header">
                                <h2><?= htmlspecialchars($lesson['title']) ?></h2>
                                <div class="lesson-actions">
                                    <?php if (!$isCompleted): ?>
                                    <form method="POST" class="mark-completed-form">
                                        <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                        <button type="submit" name="mark_completed" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i>
                                            Отметить как пройденный
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="completed-badge">
                                        <i class="fas fa-check"></i>
                                        Пройдено
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($lesson['description']): ?>
                            <div class="lesson-description">
                                <p><?= nl2br(htmlspecialchars($lesson['description'])) ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="lesson-media">
                                <?php if ($lesson['content_type'] == 'video' && $lesson['content_url']): ?>
                                    <div class="video-container">
                                        <iframe src="<?= htmlspecialchars($lesson['content_url'])?>" 
                                                frameborder="0" 
                                                allowfullscreen>
                                        </iframe>
                                    </div>
                                <?php elseif ($lesson['content_type'] == 'text'): ?>
                                    <div class="text-content">
                                        <div class="text-placeholder">
                                            <i class="fas fa-file-alt"></i>
                                            <h3>Текстовый материал</h3>
                                            <p>Этот урок содержит текстовые материалы для изучения.</p>
                                        </div>
                                        <!-- Здесь можно вывести текстовый контент из БД -->
                                        <?php if ($lesson['description']): ?>
                                            <div class="text-body">
                                                <?= nl2br(htmlspecialchars($lesson['description'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($lesson['content_type'] == 'pdf' && $lesson['content_url']): ?>
                                    <div class="pdf-container">
                                        <iframe src="<?= htmlspecialchars($lesson['content_url']) ?>" 
                                                width="100%" 
                                                height="600px">
                                        </iframe>
                                        <a href="<?= htmlspecialchars($lesson['content_url']) ?>" 
                                           class="btn btn-primary" 
                                           download>
                                            <i class="fas fa-download"></i>
                                            Скачать PDF
                                        </a>
                                    </div>
                                <?php elseif ($lesson['content_type'] == 'quiz'): ?>
                                    <div class="quiz-container">
                                        <div class="quiz-placeholder">
                                            <i class="fas fa-question-circle"></i>
                                            <h3>Тестирование</h3>
                                            <p>Этот урок содержит тест для проверки знаний.</p>
                                            <button class="btn btn-primary" onclick="startQuiz(<?= $lesson['id'] ?>)">
                                                Начать тест
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="lesson-navigation">
                                <?php if ($index < count($lessons) - 1): ?>
                                <a href="#lesson-<?= $lessons[$index+1]['id'] ?>" class="btn btn-primary">
                                    Следующий урок
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                                <?php else: ?>
                                <div class="course-completed">
                                    <h3>Поздравляем! 🎉</h3>
                                    <p>Вы завершили этот курс. Не забудьте оставить отзыв!</p>
                                    <a href="index.php?page=course&id=<?= $courseId ?>#reviews" class="btn btn-success">
                                        <i class="fas fa-star"></i>
                                        Оставить отзыв
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-lessons">
                            <i class="fas fa-book-open"></i>
                            <h2>Уроки скоро появятся</h2>
                            <p>Материалы для этого курса находятся в разработке.</p>
                            <?php if ($course['pdf_file']): ?>
                            <a href="<?= htmlspecialchars($course['pdf_file']) ?>" class="btn btn-primary" download>
                                <i class="fas fa-download"></i>
                                Скачать материалы курса
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <style>
    .course-view-section {
        padding: 0;
        background: var(--gray-50);
        min-height: 100vh;
    }

    .course-view-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        min-height: 100vh;
    }

    /* Боковая панель */
    .course-sidebar {
        background: white;
        border-right: 1px solid var(--gray-200);
        height: 100vh;
        position: sticky;
        top: 0;
        overflow-y: auto;
    }

    .course-info-sidebar {
        padding: 24px;
        border-bottom: 1px solid var(--gray-200);
    }

    .course-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 16px 0;
        color: var(--gray-900);
        line-height: 1.3;
    }

    .progress-section {
        margin-top: 16px;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
        color: var(--gray-600);
    }

    .progress-percent {
        font-weight: 600;
        color: var(--primary-600);
    }

    .progress-bar {
        height: 6px;
        background: var(--gray-200);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: var(--primary-500);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .lessons-nav {
        padding: 0;
    }

    .lessons-nav h3 {
        padding: 16px 24px;
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-900);
        border-bottom: 1px solid var(--gray-200);
    }

    .lessons-list {
        display: flex;
        flex-direction: column;
    }

    .lesson-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 24px;
        text-decoration: none;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-100);
        transition: all 0.2s ease;
        position: relative;
    }

    .lesson-item:hover {
        background: var(--gray-50);
        color: var(--gray-900);
    }

    .lesson-item.completed {
        background: var(--success-50);
        border-left: 3px solid var(--success-500);
    }

    .lesson-number {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        color: var(--gray-600);
        flex-shrink: 0;
    }

    .lesson-item.completed .lesson-number {
        background: var(--success-500);
        color: white;
    }

    .lesson-info {
        flex: 1;
        min-width: 0;
    }

    .lesson-title {
        font-weight: 500;
        margin-bottom: 4px;
        font-size: 14px;
        line-height: 1.3;
    }

    .lesson-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--gray-500);
    }

    .lesson-meta i {
        width: 12px;
    }

    .lesson-status {
        color: var(--success-500);
    }

    .materials-sidebar {
        padding: 24px;
        border-top: 1px solid var(--gray-200);
    }

    .materials-sidebar h3 {
        margin: 0 0 12px 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .download-material {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: var(--primary-50);
        color: var(--primary-700);
        text-decoration: none;
        border-radius: var(--radius-md);
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .download-material:hover {
        background: var(--primary-100);
    }

    /* Основной контент */
    .course-content {
        padding: 40px;
        overflow-y: auto;
        max-height: 100vh;
    }

    .lesson-content {
        margin-bottom: 80px;
    }

    .lesson-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 20px;
    }

    .lesson-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.2;
        flex: 1;
    }

    .lesson-actions {
        flex-shrink: 0;
    }

    .completed-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: var(--success-100);
        color: var(--success-700);
        border-radius: var(--radius-full);
        font-size: 14px;
        font-weight: 500;
    }

    .lesson-description {
        background: white;
        padding: 20px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        margin-bottom: 24px;
    }

    .lesson-description p {
        margin: 0;
        line-height: 1.6;
        color: var(--gray-700);
    }

    .lesson-media {
        margin-bottom: 32px;
    }

    .video-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        margin-bottom: 20px;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
    }

    .text-content {
        background: white;
        padding: 40px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
    }

    .text-placeholder {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray-500);
    }

    .text-placeholder i {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .text-placeholder h3 {
        margin: 0 0 8px 0;
        color: var(--gray-700);
    }

    .text-body {
        margin-top: 24px;
        line-height: 1.7;
        color: var(--gray-700);
    }

    .pdf-container {
        background: white;
        padding: 24px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
    }

    .pdf-container iframe {
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        margin-bottom: 16px;
    }

    .quiz-container {
        background: white;
        padding: 40px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        text-align: center;
    }

    .quiz-placeholder i {
        font-size: 48px;
        color: var(--primary-500);
        margin-bottom: 16px;
    }

    .quiz-placeholder h3 {
        margin: 0 0 8px 0;
        color: var(--gray-800);
    }

    .quiz-placeholder p {
        margin: 0 0 20px 0;
        color: var(--gray-600);
    }

    .lesson-navigation {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid var(--gray-200);
    }

    .course-completed {
        text-align: center;
        padding: 40px 20px;
        background: var(--success-50);
        border-radius: var(--radius-lg);
        border: 1px solid var(--success-200);
        margin-top: 40px;
    }

    .course-completed h3 {
        margin: 0 0 12px 0;
        color: var(--success-700);
    }

    .course-completed p {
        margin: 0 0 20px 0;
        color: var(--success-600);
    }

    .no-lessons {
        text-align: center;
        padding: 80px 20px;
        color: var(--gray-500);
    }

    .no-lessons i {
        font-size: 64px;
        margin-bottom: 24px;
    }

    .no-lessons h2 {
        margin: 0 0 12px 0;
        color: var(--gray-700);
    }

    .no-lessons p {
        margin: 0 0 24px 0;
        font-size: 18px;
    }

    /* Адаптивность */
    @media (max-width: 968px) {
        .course-view-layout {
            grid-template-columns: 1fr;
        }

        .course-sidebar {
            height: auto;
            position: static;
            border-right: none;
            border-bottom: 1px solid var(--gray-200);
        }

        .course-content {
            padding: 24px;
            max-height: none;
        }

        .lesson-header {
            flex-direction: column;
            align-items: stretch;
        }

        .lesson-actions {
            align-self: flex-end;
        }
    }

    @media (max-width: 768px) {
        .course-content {
            padding: 16px;
        }

        .lesson-header h2 {
            font-size: 24px;
        }

        .text-content,
        .quiz-container {
            padding: 24px;
        }
    }
    </style>

    <script>
    // Плавная прокрутка к урокам
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кликов по урокам в боковой панели
        const lessonLinks = document.querySelectorAll('.lesson-item');
        lessonLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Отслеживание активного урока при прокрутке
        const lessonSections = document.querySelectorAll('.lesson-content');
        const observerOptions = {
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Убираем активный класс у всех уроков
                    lessonLinks.forEach(link => link.classList.remove('active'));
                    
                    // Добавляем активный класс текущему уроку
                    const activeLink = document.querySelector(`.lesson-item[href="#${entry.target.id}"]`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        }, observerOptions);

        lessonSections.forEach(section => {
            observer.observe(section);
        });
    });

    function startQuiz(lessonId) {
        alert('Тест для урока ' + lessonId + ' будет запущен здесь!');
        // В будущем здесь можно реализовать логику тестирования
    }
    </script>