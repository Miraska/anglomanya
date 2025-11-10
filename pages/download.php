<?php
require_once '../db/database.php';
require_once '../includes/auth.php';

startSession();
$currentUser = getCurrentUser();

if (!$currentUser) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

$filename = $_GET['file'] ?? '';
if (empty($filename)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Безопасное получение пути к файлу
$safeFilename = basename($filename);
$filepath = __DIR__ . '/../assets/files/' . $safeFilename;

if (!file_exists($filepath)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Проверяем доступ пользователя к курсу, к которому относится файл
$course = queryOne("SELECT id FROM courses WHERE pdf_file = ?", [$safeFilename]);
if ($course && !hasAccessToCourse($currentUser['id'], $course['id'])) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Отправка файла
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;

?>