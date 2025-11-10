<?php
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Валидация
    if (empty($name)) {
        $errors[] = 'Имя обязательно для заполнения';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Имя должно содержать минимум 2 символа';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Пароли не совпадают';
    }

    // Убрали ошибочное условие с isset

    if (empty($errors)) {
        $result = registerUser($email, $password, $name);
        
        if ($result['success']) {
            loginUser($email, $password);
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
            <h2 style="text-align: center; margin-bottom: 8px;">Регистрация</h2>
            <p style="text-align: center; color: var(--gray-600); margin-bottom: 32px;">Создайте аккаунт для начала обучения</p>
            
            <!-- Вывод сообщений об ошибках -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $message ?>
            
            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">Имя и фамилия</label>
                    <input type="text" class="form-input" placeholder="Иван Петров" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" placeholder="your@email.com" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <input type="password" class="form-input" placeholder="Минимум 6 символов" name="password">
                </div>

                <div class="form-group">
                    <label class="form-label">Повторите Пароль</label>
                    <input type="password" class="form-input" placeholder="Минимум 6 символов" name="confirm_password">
                </div>
                
                <button class="btn btn-primary" style="width: 100%; margin-bottom: 16px;" name="submit">Создать аккаунт</button>
            </form>
            
            <a href="?page=login" class="btn btn-ghost" style="width: 100%;">Уже есть аккаунт? Войти</a>
        </div>
    </div>
</section>