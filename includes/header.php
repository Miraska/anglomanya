<?
require_once 'auth.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['logout'])) {
        logoutUser();
        header('Location: index.php');
        exit;
    }
}

?>

<!-- Header -->
<header class="header">
    <div class="container">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="index.php#">
                    <img src="assets/media/images/logo/logo.svg" alt="МИКАВА" class="logo">
                    <h1>Англомания</h1>
                </a>
            </div>
            <? if($_SESSION['user_role'] !== 'admin'): ?>
                <ul class="nav-menu">
                    <li class="nav-item dropdown">
                        <a href="index.php#" class="nav-link">Главная</i></a>
                        <ul class="dropdown-menu">
                            <li><a href="index.php#why-us">Почему мы?</a></li>
                            <li><a href="index.php#popular-courses">Популярные курсы</a></li>
                            <li><a href="index.php#about-us">О нас</a></li>
                            <li><a href="index.php#faq">Часто задаваемые вопросы</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a href="index.php?page=catalog" class="nav-link">Каталог</i></a>
                        <ul class="dropdown-menu">
                            <?
                            foreach(getCategories() as $category):
                            ?>
                                <li><a href="index.php?page=catalog&category=<?=$category['slug']?>"><?echo $category['name']?></a></li>
                            <?
                            endforeach
                            ?>
                        </ul>
                    </li>
                </ul>
                <div class="nav-actions">
                    <?php if ($currentUser): ?>
                        <a href="index.php?page=cart" style="color: black; font-size: 18px;">
                            <i class="fa-solid fa-cart-shopping"></i> <?php if ($cartCount > 0) echo "($cartCount)"; ?>
                        </a>
                        <a href="index.php?page=profile" class="btn btn-primary">
                            <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($currentUser['name']); ?>
                        </a>
                        <!-- <a href="index.php?page=logout" class="btn btn-primary">Выйти</a> -->
                    <?php else: ?>
                        <a class="btn btn-primary" href="index.php?page=login">Войти</a>
                        <!-- <a class="btn btn-primary" href="index.php?page=register">Регистрация</a> -->
                    <?php endif; ?>
                </div>
            <? else: ?>
                <form action="" method="post">
                    <button name="logout" id="logout" type="submit" class="btn btn-primary">Выйти</button>
                </form>
            <? endif ?>
        </nav>
    </div>
</header>