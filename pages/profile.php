<?php
require_once __DIR__ . '/../includes/auth.php';
startSession();

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$currentUser = getCurrentUser();
$userCourses = getUserCourses($currentUser['id']);

// Обработка формы обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $updateData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        $result = updateProfile($currentUser['id'], $updateData);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        // Обновляем данные пользователя
        $currentUser = getCurrentUser();
    }
    
    // Обработка загрузки аватара
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadAvatar($_FILES['avatar']);
        if ($uploadResult['success']) {
            if (updateAvatar($currentUser['id'], $uploadResult['path'])) {
                $message = 'Аватар успешно обновлен';
                $messageType = 'success';
                $currentUser = getCurrentUser();
            } else {
                $message = 'Ошибка при обновлении аватара';
                $messageType = 'error';
            }
        } else {
            $message = $uploadResult['message'];
            $messageType = 'error';
        }
    }
    
    // Обработка смены пароля
    if (isset($_POST['change_password'])) {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword !== $confirmPassword) {
            $message = 'Пароли не совпадают';
            $messageType = 'error';
        } else {
            $result = changePassword($currentUser['id'], $oldPassword, $newPassword);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
    
    // Выход из системы
    if (isset($_POST['logout'])) {
        logoutUser();
        header('Location: index.php');
        exit;
    }
}
?>

<section class="profile-section">
    <div class="container">
        <!-- Сообщения -->
        <?php if (isset($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <div class="profile-layout">
            <!-- Боковая панель -->
            <div class="profile-sidebar">
                <div class="profile-header">
                    <form method="POST" enctype="multipart/form-data" class="avatar-form">
                        <input type="file" name="avatar" id="avatar-input" accept="image/*" class="avatar-input">
                        <label for="avatar-input" class="avatar-edit">
                            <i class="fas fa-image"></i>
                        </label>
                    </form>
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar">
                            <?php if ($currentUser['avatar']): ?>
                                <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" 
                                     alt="Аватар" class="avatar-image">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <img class="avatar-image" src="https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg" alt="">
                                </div>
                            <?php endif; ?>
                        </div>
                        <h3 class="profile-name"><?= htmlspecialchars($currentUser['name']) ?></h3>
                        <p class="profile-role">
                            <?= htmlspecialchars($currentUser['role'] === 'admin' ? 'Администратор' : 'Студент') ?>
                        </p>
                    </div>
                </div>
                
                <nav class="profile-nav">
                    <a href="#courses" class="profile-nav-item active" data-tab="courses">
                        <i class="fas fa-book"></i>
                        Мои курсы
                    </a>
                    <a href="#settings" class="profile-nav-item" data-tab="settings">
                        <i class="fas fa-cog"></i>
                        Настройки
                    </a>
                    <form method="POST" class="logout-form">
                        <button type="submit" name="logout" class="profile-nav-item logout-btn" 
                                onclick="return confirm('Вы уверены, что хотите выйти?')">
                            <i class="fas fa-sign-out-alt"></i>
                            Выход
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Основной контент -->
            <div class="profile-content">
                <!-- Вкладка "Мои курсы" -->
                <div id="courses-tab" class="tab-content active">
                    <div class="tab-header">
                        <h2>Мои курсы</h2>
                    </div>
                    
                    <?php if (empty($userCourses)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <h3>У вас пока нет курсов</h3>
                            <p>Начните обучение, выбрав курс из каталога</p>
                            <a href="index.php?page=catalog" class="btn btn-primary">
                                Перейти в каталог
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="my-courses-grid">
                            <?php foreach ($userCourses as $course): ?>
                            <div class="enrolled-course">
                                <div class="course-image">
                                    <?php if ($course['image']): ?>
                                        <img src="<?= htmlspecialchars($course['image']) ?>" 
                                             alt="<?= htmlspecialchars($course['title']) ?>">
                                    <?php else: ?>
                                        <div class="course-image-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="course-info">
                                    <h4><?= htmlspecialchars($course['title']) ?></h4>
                                    <p class="course-meta">Зачислен: <?= date('d.m.Y', strtotime($course['enrolled_at'])) ?></p>
                                </div>
                                <a href="index.php?page=course&id=<?= $course['id'] ?>" class="btn btn-primary course-action">
                                    <?= $course['progress'] > 0 ? 'Продолжить' : 'Начать' ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Вкладка "Настройки" -->
                <div id="settings-tab" class="tab-content">
                    <div class="tab-header">
                        <h2>Настройки профиля</h2>
                    </div>
                    
                    <!-- Форма обновления профиля -->
                    <div class="settings-card">
                        <h3>Основная информация</h3>
                        <form method="POST" class="settings-form">
                            <div class="form-group">
                                <label for="name">Имя</label>
                                <input type="text" id="name" name="name" 
                                       value="<?= htmlspecialchars($currentUser['name']) ?>" 
                                       required class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?= htmlspecialchars($currentUser['email']) ?>" 
                                       required class="form-input">
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                Сохранить изменения
                            </button>
                        </form>
                    </div>

                    <!-- Форма смены пароля -->
                    <div class="settings-card">
                        <h3>Смена пароля</h3>
                        <form method="POST" class="settings-form">
                            <div class="form-group">
                                <label for="old_password">Текущий пароль</label>
                                <input type="password" id="old_password" name="old_password" required class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Новый пароль</label>
                                <input type="password" id="new_password" name="new_password" required class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Подтвердите новый пароль</label>
                                <input type="password" id="confirm_password" name="confirm_password" required class="form-input">
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-primary">
                                Сменить пароль
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.profile-section {
    padding: 40px 0;
    background: var(--gray-50);
    min-height: 100vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.profile-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    align-items: start;
}

/* Боковая панель */
.profile-sidebar {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 100px;
}

.profile-header {
    text-align: center;
    margin-bottom: 24px;
}

.profile-avatar-wrapper {
    position: relative;
    display: inline-block;
}

.profile-avatar {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto 16px;
}

.avatar-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 24px;
}

.avatar-edit {
    position: absolute;
    top: 10px;
    right: 10px;
    color: black;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}


.avatar-input {
    display: none;
}

.profile-name {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 600;
    color: var(--gray-900);
}

.profile-role {
    margin: 0;
    color: var(--gray-600);
    font-size: 14px;
}

/* Навигация */
.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.profile-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: var(--gray-700);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.2s;
    font-weight: 500;
}

.profile-nav-item:hover {
    background: var(--gray-100);
}

.profile-nav-item.active {
    background: var(--primary);
}

.profile-nav-item i {
    width: 16px;
    text-align: center;
}

.logout-btn {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    font-family: inherit;
    font-size: inherit;
    cursor: pointer;
}

/* Основной контент */
.profile-content {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tab-header {
    margin-bottom: 32px;
}

.tab-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--gray-900);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Карточки курсов */
.my-courses-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.enrolled-course {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    transition: all 0.2s;
}

.enrolled-course:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
}

.course-image {
    width: 120px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.course-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-image-placeholder {
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.course-info {
    flex: 1;
}

.course-info h4 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-900);
}

.course-meta {
    margin: 0 0 12px;
    color: var(--gray-600);
    font-size: 14px;
}

.progress-section {
    margin-bottom: 8px;
}

.progress-text {
    margin: 0 0 6px;
    color: var(--gray-600);
    font-size: 14px;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: var(--gray-200);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.course-action {
    flex-shrink: 0;
}

/* Настройки */
.settings-card {
    background: var(--gray-50);
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 1px solid var(--gray-200);
}

.settings-card:last-child {
    margin-bottom: 0;
}

.settings-card h3 {
    margin: 0 0 20px;
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-900);
}

.settings-form {
    max-width: 400px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: var(--gray-700);
    font-size: 14px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--gray-300);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Пустое состояние */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    color: var(--gray-400);
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 12px;
    font-size: 20px;
    color: var(--gray-900);
}

.empty-state p {
    margin: 0 0 24px;
    color: var(--gray-600);
    font-size: 16px;
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

/* Адаптивность */
@media (max-width: 768px) {
    .profile-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .profile-sidebar {
        position: static;
    }
    
    .enrolled-course {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
    
    .course-info {
        width: 100%;
    }
}
</style>

<script>
// Переключение вкладок
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.profile-nav-item[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Обработка хеша URL при загрузке
    const hash = window.location.hash.substring(1) || 'courses';
    switchTab(hash);
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href')?.startsWith('#')) {
                e.preventDefault();
                const tab = this.getAttribute('href').substring(1);
                switchTab(tab);
                window.history.pushState(null, '', `#${tab}`);
            }
        });
    });
    
    function switchTab(tabName) {
        // Обновляем навигацию
        navItems.forEach(item => {
            item.classList.toggle('active', item.getAttribute('data-tab') === tabName);
        });
        
        // Показываем соответствующий контент
        tabContents.forEach(content => {
            content.classList.toggle('active', content.id === `${tabName}-tab`);
        });
    }
    
    // Обработка загрузки аватара
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                this.closest('form').submit();
            }
        });
    }
});

// Функция для получения инициалов (добавьте в PHP или оставьте здесь)
function getInitials(name) {
    return name.split(' ').map(word => word[0]).join('').toUpperCase();
}
</script>