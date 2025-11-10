<?php
require_once 'config.php';
require_once 'includes/auth.php';
require_once 'includes/courses.php';
require_once 'includes/cart.php';

startSession();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Разрешенные страницы
$allowedPages = [
    'home', 'catalog', 'login', 'register', 'profile', 'cart', 
    'checkout', 'course', 'my_courses', 'payment_success',
    'logout', 'admin', 'download', '404', 'payment_process', 'course-view', 'admin'
];

// Проверка существования страницы
if (!in_array($page, $allowedPages)) {
    $page = 'home';
}

if ($page === 'logout') {
    logoutUser();
    header('Location: index.php');
    exit;
}

// Получение текущего пользователя
$currentUser = getCurrentUser();
$cartCount = getCartCount();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Англомания - изучение английского языка</title>
    <link rel="icon" type="image/svg+xml" href="./assets/media/images/logo/logo.svg">
    <link rel="shortcut icon" type="image/svg+xml" href="./assets/media/images/logo/logo.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/media/images/logo/logo.svg">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?
        include "./includes/header.php";
    ?>

    <main>
        <?php
        $pagePath = "pages/{$page}.php";
        if (file_exists($pagePath) && $_SESSION['user_role'] != 'admin') {
            include $pagePath;
        } 
        elseif(file_exists($pagePath) && $_SESSION['user_role'] == 'admin'){
            include "pages/admin.php";
        }
        else {
            include 'pages/404.php';
        }
        ?>
    </main>

    <?
        include "./includes/footer.php";
    ?>

    <script src="js/main.js"></script>
</body>
</html>