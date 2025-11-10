<?php
// Если пользователь уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Валидация
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    }
    
    if (empty($errors)) {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $message = '<div class="alert alert-danger">' . $result['message'] . '</div>';
        }
    }
}
?>

<section class="auth-section">
    <div class="container">
        <div style="max-width: 480px; margin: 80px auto; padding: 40px; background: white; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
            <h2 style="text-align: center; margin-bottom: 8px;">Вход в аккаунт</h2>
            <p style="text-align: center; color: var(--gray-600); margin-bottom: 32px;">Войдите в свой аккаунт</p>
            
            <!-- Вывод сообщений об ошибках -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $message ?>
            
            <!-- Добавлен action и method к форме -->
            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <!-- Добавлены атрибуты name и value -->
                    <input type="email" class="form-input" placeholder="your@email.com" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <!-- Добавлен атрибут name -->
                    <input type="password" class="form-input" placeholder="Введите ваш пароль" name="password">
                </div>
                
                <!-- Добавлен type="submit" для кнопки -->
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 16px;">Войти</button>
            </form>
            
            <a href="?page=register" class="btn btn-ghost" style="width: 100%; margin-top: 16px;">Нет аккаунта? Зарегистрироваться</a>
        </div>
    </div>
</section>