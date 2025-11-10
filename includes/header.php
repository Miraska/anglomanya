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
            
            <!-- Бургер-меню для мобильных -->
            <div class="burger-menu" id="burgerMenu">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <? if($_SESSION['user_role'] !== 'admin'): ?>
                <ul class="nav-menu" id="navMenu">
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
                <div class="nav-actions" id="navActions">
                    <?php if ($currentUser): ?>
                        <a href="index.php?page=cart" class="cart-mobile" style="color: black; font-size: 18px;">
                            <i class="fa-solid fa-cart-shopping"></i> <?php if ($cartCount > 0) echo "($cartCount)"; ?>
                        </a>
                        <a href="index.php?page=profile" class="btn btn-primary profile-mobile">
                            <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($currentUser['name']); ?>
                        </a>
                    <?php else: ?>
                        <a class="btn btn-primary login-mobile" href="index.php?page=login">Войти</a>
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const navMenu = document.getElementById('navMenu');
    const navActions = document.getElementById('navActions');
    const body = document.body;

    // Создаем оверлей
    const overlay = document.createElement('div');
    overlay.className = 'mobile-overlay';
    document.body.appendChild(overlay);

    burgerMenu.addEventListener('click', function() {
        // Переключаем активное состояние бургера
        this.classList.toggle('active');
        
        // Переключаем видимость меню
        if (navMenu) navMenu.classList.toggle('mobile-open');
        if (navActions) navActions.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        
        // Блокируем прокрутку body при открытом меню
        body.style.overflow = this.classList.contains('active') ? 'hidden' : '';
    });

    // Закрытие меню при клике на оверлей
    overlay.addEventListener('click', function() {
        burgerMenu.classList.remove('active');
        if (navMenu) navMenu.classList.remove('mobile-open');
        if (navActions) navActions.classList.remove('mobile-open');
        this.classList.remove('active');
        body.style.overflow = '';
    });

    // Обработка выпадающих меню на мобильных
    if (navMenu) {
        const dropdowns = navMenu.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            const link = dropdown.querySelector('.nav-link');
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 420) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        });
    }

    // Закрытие меню при клике на ссылку (кроме выпадающих)
    if (navMenu) {
        const allLinks = navMenu.querySelectorAll('a:not(.dropdown .nav-link)');
        allLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 420) {
                    burgerMenu.classList.remove('active');
                    if (navMenu) navMenu.classList.remove('mobile-open');
                    if (navActions) navActions.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    body.style.overflow = '';
                }
            });
        });
    }

    // Адаптация при изменении размера окна
    window.addEventListener('resize', function() {
        if (window.innerWidth > 420) {
            // На больших экранах убираем мобильные стили
            burgerMenu.classList.remove('active');
            if (navMenu) navMenu.classList.remove('mobile-open');
            if (navActions) navActions.classList.remove('mobile-open');
            overlay.classList.remove('active');
            body.style.overflow = '';
            
            // Сбрасываем активные dropdown
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});
</script>