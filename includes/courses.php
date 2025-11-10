<?php
require_once __DIR__ . '/../db/database.php';

// Получение рейтинга курса на основе отзывов
function getCourseRating($courseId) {
    $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
            FROM reviews 
            WHERE course_id = ?";
    $result = queryOne($sql, [$courseId]);
    
    if ($result && $result['review_count'] > 0) {
        return [
            'rating' => round($result['avg_rating'], 1),
            'count' => $result['review_count']
        ];
    }
    
    return [
        'rating' => 0,
        'count' => 0
    ];
}

// Получение популярных курсов (обновленная версия)
function getPopularCourses($limit = 3) {
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                   COALESCE(AVG(r.rating), 0) as calculated_rating,
                   COUNT(r.id) as review_count
            FROM courses c 
            JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN reviews r ON c.id = r.course_id
            WHERE c.is_active = 1 AND c.is_popular = 1 
            GROUP BY c.id
            ORDER BY calculated_rating DESC 
            LIMIT ?";
    
    return query($sql, [$limit]);
}


// Функция для получения рейтинга курса на основе отзывов
function getCourseRatingFromReviews($courseId) {
    $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
            FROM reviews 
            WHERE course_id = ?";
    $result = queryOne($sql, [$courseId]);
    
    if ($result && $result['review_count'] > 0) {
        return [
            'rating' => round($result['avg_rating'], 1),
            'count' => $result['review_count']
        ];
    }
    
    return [
        'rating' => 0,
        'count' => 0
    ];
}


// Получение всех курсов с фильтрацией
function getCourses($filters = []) {
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug,
                   COALESCE(AVG(r.rating), 0) as calculated_rating,
                   COUNT(r.id) as review_count
            FROM courses c 
            JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN reviews r ON c.id = r.course_id
            WHERE c.is_active = 1";
    $params = [];
    
    // Фильтр по категории
    if (!empty($filters['category'])) {
        $sql .= " AND cat.slug = ?";
        $params[] = $filters['category'];
    }
    
    // Фильтр по цене (минимальная)
    if (isset($filters['price_min']) && $filters['price_min'] !== '') {
        $sql .= " AND c.price >= ?";
        $params[] = $filters['price_min'];
    }
    
    // Фильтр по цене (максимальная)
    if (isset($filters['price_max']) && $filters['price_max'] !== '') {
        $sql .= " AND c.price <= ?";
        $params[] = $filters['price_max'];
    }
    
    // Фильтр по рейтингу
    if (!empty($filters['rating'])) {
        $sql .= " HAVING calculated_rating >= ?";
        $params[] = $filters['rating'];
    }
    
    // Поиск
    if (!empty($filters['search'])) {
        $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Группировка для агрегатных функций
    $sql .= " GROUP BY c.id";
    
    // Сортировка
    $orderBy = " ORDER BY c.created_at DESC";
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_asc':
                $orderBy = " ORDER BY c.price ASC";
                break;
            case 'price_desc':
                $orderBy = " ORDER BY c.price DESC";
                break;
            case 'rating':
                $orderBy = " ORDER BY calculated_rating DESC";
                break;
            case 'popular':
                $orderBy = " ORDER BY c.is_popular DESC, calculated_rating DESC";
                break;
        }
    }
    
    $sql .= $orderBy;
    
    return query($sql, $params);
}

// Получение курса по ID
function getCourseById($id) {
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug 
            FROM courses c 
            JOIN categories cat ON c.category_id = cat.id 
            WHERE c.id = ?";
    
    return queryOne($sql, [$id]);
}

// Проверка доступа пользователя к курсу
function hasAccessToCourse($userId, $courseId) {
    $result = queryOne(
        "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?",
        [$userId, $courseId]
    );
    
    return $result !== false;
}

// Получение купленных курсов пользователя
function getUserCourses($userId) {
    $sql = "SELECT c.*, cat.name as category_name, uc.purchased_at 
            FROM user_courses uc 
            JOIN courses c ON uc.course_id = c.id 
            JOIN categories cat ON c.category_id = cat.id 
            WHERE uc.user_id = ? 
            ORDER BY uc.purchased_at DESC";
    
    return query($sql, [$userId]);
}

// Добавление курса (админ)
function addCourse($data) {
    $sql = "INSERT INTO courses (category_id, title, description, price, image, pdf_file, rating, is_popular, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $data['category_id'],
        $data['title'],
        $data['description'],
        $data['price'],
        $data['image'] ?? null,
        $data['pdf_file'] ?? null,
        $data['rating'] ?? 0.0,
        $data['is_popular'] ?? 0,
        $data['is_active'] ?? 1  // Добавлено
    ];
    
    $result = execute($sql, $params);
    
    if ($result) {
        return ['success' => true, 'id' => lastInsertId()];
    }
    
    return ['success' => false, 'message' => 'Ошибка при добавлении курса'];
}

// Обновление курса (админ)
function updateCourse($id, $data) {
    $updates = [];
    $params = [];
    
    if (isset($data['category_id'])) {
        $updates[] = "category_id = ?";
        $params[] = $data['category_id'];
    }
    
    if (isset($data['title'])) {
        $updates[] = "title = ?";
        $params[] = $data['title'];
    }
    
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
    }
    
    if (isset($data['price'])) {
        $updates[] = "price = ?";
        $params[] = $data['price'];
    }
    
    if (isset($data['image'])) {
        $updates[] = "image = ?";
        $params[] = $data['image'];
    }
    
    if (isset($data['pdf_file'])) {
        $updates[] = "pdf_file = ?";
        $params[] = $data['pdf_file'];
    }
    
    if (isset($data['rating'])) {
        $updates[] = "rating = ?";
        $params[] = $data['rating'];
    }
    
    if (isset($data['is_popular'])) {
        $updates[] = "is_popular = ?";
        $params[] = $data['is_popular'];
    }
    
    if (isset($data['is_active'])) {
        $updates[] = "is_active = ?";
        $params[] = $data['is_active'];
    }
    
    if (empty($updates)) {
        return ['success' => false, 'message' => 'Нет данных для обновления'];
    }
    
    $params[] = $id;
    $sql = "UPDATE courses SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $result = execute($sql, $params);
    
    if ($result) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при обновлении курса'];
}

// Удаление курса (админ)
function deleteCourse($id) {
    $result = execute("DELETE FROM courses WHERE id = ?", [$id]);
    
    if ($result) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при удалении курса'];
}

// Получение всех категорий
function getCategories() {
    return query("SELECT * FROM categories ORDER BY name ASC");
}

// Получение категории по ID
function getCategoryById($id) {
    return queryOne("SELECT * FROM categories WHERE id = ?", [$id]);
}

// Получение категории по slug
function getCategoryBySlug($slug) {
    return queryOne("SELECT * FROM categories WHERE slug = ?", [$slug]);
}

// Добавление категории (админ)
function addCategory($name, $description, $slug) {
    // Проверка уникальности slug
    $existing = queryOne("SELECT id FROM categories WHERE slug = ?", [$slug]);
    if ($existing) {
        return ['success' => false, 'message' => 'Категория с таким slug уже существует'];
    }
    
    $result = execute(
        "INSERT INTO categories (name, description, slug) VALUES (?, ?, ?)",
        [$name, $description, $slug]
    );
    
    if ($result) {
        return ['success' => true, 'id' => lastInsertId()];
    }
    
    return ['success' => false, 'message' => 'Ошибка при добавлении категории'];
}

// Обновление категории (админ)
function updateCategory($id, $name, $description, $slug) {
    // Проверка уникальности slug
    $existing = queryOne(
        "SELECT id FROM categories WHERE slug = ? AND id != ?",
        [$slug, $id]
    );
    if ($existing) {
        return ['success' => false, 'message' => 'Категория с таким slug уже существует'];
    }
    
    $result = execute(
        "UPDATE categories SET name = ?, description = ?, slug = ? WHERE id = ?",
        [$name, $description, $slug, $id]
    );
    
    if ($result) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при обновлении категории'];
}

// Удаление категории (админ)
function deleteCategory($id) {
    // Проверка наличия курсов в категории
    $courses = queryOne("SELECT COUNT(*) as count FROM courses WHERE category_id = ?", [$id]);
    if ($courses['count'] > 0) {
        return ['success' => false, 'message' => 'Невозможно удалить категорию с курсами'];
    }
    
    $result = execute("DELETE FROM categories WHERE id = ?", [$id]);
    
    if ($result) {
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при удалении категории'];
}


// Получение отзывов для курса
function getCourseReviews($courseId) {
    $sql = "SELECT r.*, u.email as user_email, u.name as user_name, u.avatar as user_avatar 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.course_id = ? 
            ORDER BY r.created_at DESC";
    return query($sql, [$courseId]);
}

// Добавление отзыва к курсу
function addCourseReview($courseId, $userId, $rating, $comment) {
    // Проверяем, не оставлял ли пользователь уже отзыв
    $existing = queryOne(
        "SELECT id FROM reviews WHERE course_id = ? AND user_id = ?",
        [$courseId, $userId]
    );
    
    if ($existing) {
        return ['success' => false, 'message' => 'Вы уже оставляли отзыв для этого курса'];
    }
    
    $sql = "INSERT INTO reviews (course_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $result = execute($sql, [$courseId, $userId, $rating, $comment]);
    
    if ($result) {
        // Обновляем средний рейтинг курса
        updateCourseRating($courseId);
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при добавлении отзыва'];
}



// Удаление отзыва
function deleteCourseReview($reviewId, $userId) {
    $sql = "DELETE FROM reviews WHERE id = ? AND user_id = ?";
    $result = execute($sql, [$reviewId, $userId]);
    
    if ($result) {
        // Получаем course_id для обновления рейтинга
        $review = queryOne("SELECT course_id FROM reviews WHERE id = ?", [$reviewId]);
        if ($review) {
            updateCourseRating($review['course_id']);
        }
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Ошибка при удалении отзыва'];
}

// Обновление рейтинга курса
function updateCourseRating($courseId) {
    $ratingInfo = queryOne(
        "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE course_id = ?",
        [$courseId]
    );
    
    if ($ratingInfo) {
        execute(
            "UPDATE courses SET rating = ? WHERE id = ?",
            [$ratingInfo['avg_rating'] ?? 0, $courseId]
        );
    }
}

// Получение среднего рейтинга курса
function getCourseAverageRating($courseId) {
    $result = queryOne(
        "SELECT AVG(rating) as avg_rating FROM reviews WHERE course_id = ?",
        [$courseId]
    );
    return $result['avg_rating'] ?? 0;
}


// Получение уроков курса
function getCourseLessons($courseId) {
    $sql = "SELECT * FROM course_lessons 
            WHERE course_id = ? 
            ORDER BY sort_order ASC, created_at ASC";
    return query($sql, [$courseId]);
}

// Проверка, пройден ли урок
function isLessonCompleted($userId, $lessonId) {
    $result = queryOne(
        "SELECT id FROM user_lesson_progress 
         WHERE user_id = ? AND lesson_id = ?",
        [$userId, $lessonId]
    );
    return $result !== false;
}

// Отметить урок как пройденный
function markLessonCompleted($userId, $lessonId) {
    // Проверяем, не пройден ли уже урок
    if (!isLessonCompleted($userId, $lessonId)) {
        $sql = "INSERT INTO user_lesson_progress (user_id, lesson_id) VALUES (?, ?)";
        return execute($sql, [$userId, $lessonId]);
    }
    return false;
}

// Получение прогресса по курсу
function getCourseProgress($userId, $courseId) {
    // Общее количество уроков в курсе
    $totalLessons = queryOne(
        "SELECT COUNT(*) as count FROM course_lessons WHERE course_id = ?",
        [$courseId]
    );
    
    // Количество пройденных уроков
    $completedLessons = queryOne(
        "SELECT COUNT(*) as count 
         FROM user_lesson_progress ulp 
         JOIN course_lessons cl ON ulp.lesson_id = cl.id 
         WHERE ulp.user_id = ? AND cl.course_id = ?",
        [$userId, $courseId]
    );
    
    $total = $totalLessons['count'] ?? 0;
    $completed = $completedLessons['count'] ?? 0;
    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
    
    return [
        'completed' => $completed,
        'total' => $total,
        'percent' => $percent
    ];
}

?>