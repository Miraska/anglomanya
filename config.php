<?php
// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'anglomaniya');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Конфигурация сайта
define('SITE_URL', 'http://localhost');
define('SITE_NAME', 'Англомания');

// Конфигурация ЮKassa (фейковые данные для тестирования)
define('YUKASSA_SHOP_ID', 'test_shop_123');
define('YUKASSA_SECRET_KEY', 'test_key_456');

// Настройки загрузки файлов
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'gif']);

// Настройки безопасности
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 дней
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Установите 1 для HTTPS

// Часовой пояс
date_default_timezone_set('Europe/Moscow');
?>