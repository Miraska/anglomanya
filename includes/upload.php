<?php
require_once __DIR__ . '/../config.php';

// Загрузка файла
function uploadFile($file, $type = 'pdf') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Ошибка при загрузке файла'];
    }
    
    // Проверка размера файла
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Размер файла превышает допустимый'];
    }
    
    // Получение расширения файла
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Проверка расширения
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Недопустимый тип файла'];
    }
    
    // Создание уникального имени файла
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Определение директории для загрузки
    $uploadDir = UPLOAD_DIR . $type . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filepath = $uploadDir . $filename;
    
    // Перемещение файла
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => 'uploads/' . $type . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Ошибка при сохранении файла'];
}

// Удаление файла
function deleteFile($path) {
    $fullPath = __DIR__ . '/../' . $path;
    
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

// Загрузка изображения с ресайзом
function uploadImage($file, $maxWidth = 800, $maxHeight = 600) {
    $result = uploadFile($file, 'images');
    
    if (!$result['success']) {
        return $result;
    }
    
    $filepath = __DIR__ . '/../' . $result['path'];
    
    // Получение информации об изображении
    list($width, $height, $type) = getimagesize($filepath);
    
    // Проверка необходимости ресайза
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return $result;
    }
    
    // Расчет новых размеров
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Создание нового изображения
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Загрузка исходного изображения
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filepath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filepath);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filepath);
            break;
        default:
            return $result;
    }
    
    // Ресайз изображения
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Сохранение изображения
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $filepath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $filepath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $filepath);
            break;
    }
    
    // Освобождение памяти
    imagedestroy($newImage);
    imagedestroy($source);
    
    return $result;
}

// Скачивание PDF файла (только для авторизованных пользователей с доступом)
function downloadPDF($courseId, $userId) {
    // Проверка доступа
    if (!hasAccessToCourse($userId, $courseId)) {
        return ['success' => false, 'message' => 'У вас нет доступа к этому курсу'];
    }
    
    // Получение информации о курсе
    $course = getCourseById($courseId);
    
    if (!$course || !$course['pdf_file']) {
        return ['success' => false, 'message' => 'PDF файл не найден'];
    }
    
    $filepath = __DIR__ . '/../' . $course['pdf_file'];
    
    if (!file_exists($filepath)) {
        return ['success' => false, 'message' => 'Файл не существует'];
    }
    
    // Отправка файла на скачивание
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
}
?>