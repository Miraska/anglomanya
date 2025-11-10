<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/courses.php';
require_once __DIR__ . '/../includes/orders.php';
require_once __DIR__ . '/../includes/upload.php';

startSession();

// Проверка роли администратора
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Обработка форм
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Добавление курса
    if (isset($_POST['add_course'])) {
        $data = [
            'category_id' => $_POST['category_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'is_popular' => isset($_POST['is_popular']) ? 1 : 0,
            'is_active' => 1
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['path'];
            }
        }

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['pdf_file'], 'pdf');
            if ($uploadResult['success']) {
                $data['pdf_file'] = $uploadResult['path'];
            }
        }

        $result = addCourse($data);
        $message = $result['success'] ? 'Курс добавлен' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Обновление курса
    if (isset($_POST['update_course'])) {
        $courseId = $_POST['course_id'];
        $data = [
            'category_id' => $_POST['category_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'is_popular' => isset($_POST['is_popular']) ? 1 : 0
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image'] = $uploadResult['path'];
            }
        }

        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES['pdf_file'], 'pdf');
            if ($uploadResult['success']) {
                $data['pdf_file'] = $uploadResult['path'];
            }
        }

        $result = updateCourse($courseId, $data);
        $message = $result['success'] ? 'Курс обновлен' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Удаление курса
    if (isset($_POST['delete_course'])) {
        $id = $_POST['id'];
        $result = deleteCourse($id);
        $message = $result['success'] ? 'Курс удален' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Обновление пользователя
    if (isset($_POST['update_user'])) {
        $userId = $_POST['user_id'];
        $updateData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'role' => isset($_POST['is_admin']) ? 'admin' : 'student'
        ];

        if (!empty($_POST['password'])) {
            $updateData['password'] = $_POST['password']; // Hash in production
        }

        $result = updateProfile($userId, $updateData);
        $message = $result['success'] ? 'Пользователь обновлен' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Добавление категории
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $slug = !empty($_POST['slug']) ? strtolower(preg_replace('/[^a-z0-9-]/', '', $_POST['slug'])) : strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $name)));

        $result = addCategory($name, $description, $slug);
        $message = $result['success'] ? 'Категория добавлена' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Обновление категории
    if (isset($_POST['update_category'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $slug = !empty($_POST['slug']) ? strtolower(preg_replace('/[^a-z0-9-]/', '', $_POST['slug'])) : strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $name)));

        $result = updateCategory($id, $name, $description, $slug);
        $message = $result['success'] ? 'Категория обновлена' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Удаление категории
    if (isset($_POST['delete_category'])) {
        $id = $_POST['id'];
        $result = deleteCategory($id);
        $message = $result['success'] ? 'Категория удалена' : ($result['message'] ?? 'Ошибка');
        $messageType = $result['success'] ? 'success' : 'error';
    }

    // Обновление статуса заказа
    if (isset($_POST['update_order_status'])) {
        $orderId = $_POST['order_id'];
        $status = $_POST['status'];
        
        // Получаем информацию о заказе для получения user_id
        $order = getOrderById($orderId);
        if (!$order) {
            $message = 'Заказ не найден';
            $messageType = 'error';
        } else {
            $result = updateOrderStatus($orderId, $status);
            
            if ($result) {
                if ($status === 'paid') {
                    // Автоматически добавляем курсы пользователю при подтверждении оплаты
                    $orderItems = query("SELECT course_id FROM order_items WHERE order_id = ?", [$orderId]);
                    foreach ($orderItems as $item) {
                        // Проверяем, нет ли уже этого курса у пользователя
                        $existing = queryOne(
                            "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?", 
                            [$order['user_id'], $item['course_id']]
                        );
                        if (!$existing) {
                            query(
                                "INSERT INTO user_courses (user_id, course_id, purchased_at) VALUES (?, ?, NOW())", 
                                [$order['user_id'], $item['course_id']]
                            );
                        }
                    }
                    $message = 'Статус обновлен и курсы добавлены пользователю';
                } else if ($status === 'cancelled') {
                    // Удаляем курсы у пользователя при отмене заказа
                    query(
                        "DELETE uc FROM user_courses uc 
                        JOIN order_items oi ON uc.course_id = oi.course_id 
                        WHERE oi.order_id = ? AND uc.user_id = ?", 
                        [$orderId, $order['user_id']]
                    );
                    $message = 'Статус обновлен и курсы удалены у пользователя';
                } else {
                    $message = 'Статус обновлен';
                }
                $messageType = 'success';
            } else {
                $message = 'Ошибка при обновлении статуса';
                $messageType = 'error';
            }
        }
    }
}

// Получение статистики
$totalStudents = queryOne("SELECT COUNT(*) as count FROM users WHERE role = 'student'")['count'] ?? 0;
$activeCourses = queryOne("SELECT COUNT(*) as count FROM courses WHERE is_active = 1")['count'] ?? 0;
$totalRevenue = queryOne("SELECT SUM(total_amount) as sum FROM orders WHERE status = 'paid'")['sum'] ?? 0;

// Последние заказы
$recentOrders = query("
    SELECT o.id, o.total_amount, o.status, u.name as user_name, GROUP_CONCAT(c.title SEPARATOR ', ') as courses
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN courses c ON oi.course_id = c.id
    GROUP BY o.id
    ORDER BY o.created_at DESC LIMIT 5");

// Курсы
$courses = getCourses();

// Пользователи
$users = query("SELECT * FROM users ORDER BY created_at DESC");

// Заказы
$orders = query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");

// Категории
$categories = getCategories();
?>

<section class="admin-dashboard">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h1 class="display-md" style="margin-top: 30px;">Панель управления</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h2 class="stat-number"><?php echo number_format($totalStudents, 0, ',', ' '); ?></h2>
                <p class="stat-label">Всего студентов</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number"><?php echo $activeCourses; ?></h2>
                <p class="stat-label">Активных курсов</p>
            </div>
            <div class="stat-card">
                <h2 class="stat-number"><?php echo number_format($totalRevenue, 0, ',', ' '); ?>₽</h2>
                <p class="stat-label">Общий доход</p>
            </div>
        </div>

        <div class="section">
            <div class="">
                <h2 class="heading-md">Последние заказы</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Курсы</th>
                        <th>Студент</th>
                        <th>Цена</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray-600);">Нет заказов</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['courses']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo number_format($order['total_amount'], 0, ',', ' '); ?>₽</td>
                                <td><?php echo $order['status']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="">
                <h2 class="heading-md">Управление курсами</h2>
                <button class="btn btn-primary" onclick="openModal('addCourseModal')">Добавить курс</button>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Управление</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--gray-600);">Нет курсов</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo number_format($course['price'], 0, ',', ' '); ?>₽</td>
                                <td>
                                    <button class="btn btn-secondary" onclick="openEditCourseModal(<?php echo htmlspecialchars(json_encode($course), ENT_QUOTES, 'UTF-8'); ?>)">Редактировать</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить курс?');">
                                        <input type="hidden" name="delete_course" value="1">
                                        <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Удалить</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="">
                <h2 class="heading-md">Управление пользователями</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Управление</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray-600);">Нет пользователей</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['role'] === 'admin' ? 'Администратор' : 'Студент'; ?></td>
                                <td>
                                    <button class="btn btn-secondary" onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>)">Изменить</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="">
                <h2 class="heading-md">Управление заказами</h2>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Пользователь</th>
                        <th>Дата заказа</th>
                        <th>Управление</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--gray-600);">Нет заказов</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td>Заказ #<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="update_order_status" value="1">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status">
                                            <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary">Сохранить</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="">
                <h2 class="heading-md">Управление категориями</h2>
                <button class="btn btn-primary" style="margin-bottom: 10px;" onclick="openModal('addCategoryModal')">+ Добавить категорию</button>
            </div>
            <div class="categories-grid">
                <?php if (empty($categories)): ?>
                    <p style="text-align: center; color: var(--gray-600);">Нет категорий</p>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-actions">
                                <button class="icon-btn" onclick="openEditCategoryModal(<?php echo htmlspecialchars(json_encode($category), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-edit"></i></button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить категорию?');">
                                    <input type="hidden" name="delete_category" value="1">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" class="icon-btn"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                            <h3 class="heading-md"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="body-md" style="color: var(--gray-600); margin-top: 12px;"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Modals -->
<div id="addCourseModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addCourseModal')">&times;</span>
        <h2>Добавить курс</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_course" value="1">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="title" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" required class="form-input"></textarea>
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" name="price" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Загрузить файлы</label>
                <input type="file" name="image" accept="image/*" class="form-input">
                <input type="file" name="pdf_file" accept=".pdf" class="form-input" style="margin-top: 8px;">
            </div>
            <div class="form-group">
                <label>Категория курса</label>
                <select name="category_id" required class="form-input">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Популярный <input type="checkbox" name="is_popular"></label>
            </div>
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
    </div>
</div>

<div id="editCourseModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editCourseModal')">&times;</span>
        <h2>Редактировать курс</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_course" value="1">
            <input type="hidden" id="edit_course_id" name="course_id">
            <div class="form-group">
                <label>Название</label>
                <input type="text" id="edit_title" name="title" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea id="edit_description" name="description" required class="form-input"></textarea>
            </div>
            <div class="form-group">
                <label>Цена</label>
                <input type="number" id="edit_price" name="price" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Загрузить файлы</label>
                <input type="file" name="image" accept="image/*" class="form-input">
                <input type="file" name="pdf_file" accept=".pdf" class="form-input" style="margin-top: 8px;">
            </div>
            <div class="form-group">
                <label>Категория курса</label>
                <select id="edit_course_category_id" name="category_id" required class="form-input">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Популярный <input type="checkbox" id="edit_is_popular" name="is_popular"></label>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editUserModal')">&times;</span>
        <h2>Изменить пользователя</h2>
        <form method="POST">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" id="edit_user_id" name="user_id">
            <div class="form-group">
                <label>Имя</label>
                <input type="text" id="edit_user_name" name="name" required class="form-input" autocomplete="name">
            </div>
            <div class="form-group">
                <label>Почта</label>
                <input type="email" id="edit_user_email" name="email" required class="form-input" autocomplete="email">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-input" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Админ <input type="checkbox" id="edit_is_admin" name="is_admin"></label>
            </div>
            <button type="submit" class="btn btn-primary">Изменить</button>
        </form>
    </div>
</div>

<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addCategoryModal')">&times;</span>
        <h2>Добавить категорию</h2>
        <form method="POST">
            <input type="hidden" name="add_category" value="1">
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="name" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Slug (для URL, только буквы, цифры, -)</label>
                <input type="text" name="slug" class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" class="form-input"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
    </div>
</div>

<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editCategoryModal')">&times;</span>
        <h2>Редактировать категорию</h2>
        <form method="POST">
            <input type="hidden" name="update_category" value="1">
            <input type="hidden" id="edit_category_id" name="id">
            <div class="form-group">
                <label>Название</label>
                <input type="text" id="edit_category_name" name="name" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Slug (для URL, только буквы, цифры, -)</label>
                <input type="text" id="edit_category_slug" name="slug" required class="form-input" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea id="edit_category_description" name="description" class="form-input"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin: 32px 0;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-number {
    font-size: 42px;
    font-weight: 600;
    color: var(--gray-900);
}

.stat-label {
    font-size: 16px;
    color: var(--gray-600);
    margin-top: 8px;
}


.admin-table {
    width: 100%;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-top: 10px;
    margin-bottom: 40px;
}

.admin-table th {
    background: var(--gray-900);
    color: white;
    padding: 16px;
    text-align: left;
}

.admin-table td {
    padding: 16px;
    border-top: 1px solid var(--gray-200);
}

.btn-danger {
    background: var(--error);
    color: white;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

.category-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: relative;
}

.category-actions {
    position: absolute;
    top: 16px;
    right: 16px;
    display: flex;
    gap: 8px;
}

.icon-btn {
    background: none;
    border: none;
    color: var(--gray-600);
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    transition: color 0.2s;
}

.icon-btn:hover {
    color: var(--gray-900);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease; /* Плавное появление */
}

.modal.show {
    opacity: 1;
}

.modal-content {
    background: var(--gray-900);
    color: white;
    padding: 24px;
    border-radius: 16px;
    width: 400px;
    position: relative;
    transform: scale(0.95); /* Лёгкий scale для анимации */
    transition: transform 0.3s ease;
}

.modal.show .modal-content {
    transform: scale(1); /* Плавный zoom-in */
}

.close {
    position: absolute;
    top: 16px;
    right: 16px;
    color: white;
    font-size: 24px;
    cursor: pointer;
}

.form-input {
    background: var(--gray-100);
    border: none;
    border-radius: 8px;
    padding: 12px;
    margin-top: 8px;
    width: 100%;
    color: var(--gray-900);
}

.form-group label {
    color: white;
}

/* Стили для select — минималистичные, как на скрине */
select {
    appearance: none; /* Убираем дефолтный вид браузера */
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: var(--gray-100); /* Светлый фон, как в инпутах */
    border: none; /* Минимализм: без бордеров */
    border-radius: 8px;
    padding: 12px 32px 12px 12px; /* Пространство для стрелки */
    width: 100%; /* Полная ширина */
    color: var(--gray-900);
    font-size: 16px;
    cursor: pointer;
    transition: box-shadow 0.2s ease, background-color 0.2s ease; /* Плавные анимации */
    
    /* Кастомная стрелка (минималистичная, серая) */
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"><path fill="%23666666" d="M2 4l4 4 4-4z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
}

/* Ховер и фокус для плавности и красоты */
select:hover {
    background-color: var(--gray-200); /* Лёгкое затемнение */
}

select:focus {
    outline: none;
    box-shadow: 0 0 0 2px var(--primary); /* Subtle glow, как синий акцент на скрине */
    background-color: white; /* Чистый белый на фокус */
}

/* Для select в таблицах (например, статус заказа) — сделаем компактнее */
.admin-table select {
    width: auto; /* Не растягиваем на 100% в таблице */
    min-width: 120px;
    padding: 8px 28px 8px 8px; /* Меньше padding для минимализма */
    background-size: 10px; /* Меньше стрелка */
    border-radius: 6px; /* Чуть меньше радиус */
}

/* Общие улучшения для минимализма (как на скрине) */
.form-group {
    margin-bottom: 16px; /* Больше пространства */
}

.form-input, select {
    margin-top: 4px; /* Меньше margin для компактности */
}
</style>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'flex';
    setTimeout(() => { modal.classList.add('show'); }, 10); // Лёгкая задержка для анимации
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
    setTimeout(() => { modal.style.display = 'none'; }, 300); // Ждём окончания анимации
}

function openEditCourseModal(course) {
    document.getElementById('edit_course_id').value = course.id;
    document.getElementById('edit_course_category_id').value = course.category_id;
    document.getElementById('edit_title').value = course.title;
    document.getElementById('edit_description').value = course.description;
    document.getElementById('edit_price').value = course.price;
    document.getElementById('edit_is_popular').checked = course.is_popular === 1;
    openModal('editCourseModal');
}

function openEditUserModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_user_name').value = user.name;
    document.getElementById('edit_user_email').value = user.email;
    document.getElementById('edit_is_admin').checked = user.role === 'admin';
    openModal('editUserModal');
}

function openEditCategoryModal(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_category_name').value = category.name;
    document.getElementById('edit_category_slug').value = category.slug;
    document.getElementById('edit_category_description').value = category.description || '';
    openModal('editCategoryModal');
}

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            closeModal(modal.id);
        }
    });
}
</script>