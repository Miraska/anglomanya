<?php
require_once __DIR__ . '/../config.php';

// Функция для подключения к базе данных
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Функция для выполнения запросов SELECT
function query($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Функция для выполнения запросов SELECT (одна строка)
function queryOne($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Функция для выполнения INSERT, UPDATE, DELETE
function execute($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Функция для получения ID последней вставленной записи
function lastInsertId() {
    $pdo = getDBConnection();
    return $pdo->lastInsertId();
}

// Функция для начала транзакции
function beginTransaction() {
    $pdo = getDBConnection();
    return $pdo->beginTransaction();
}

// Функция для подтверждения транзакции
function commit() {
    $pdo = getDBConnection();
    return $pdo->commit();
}

// Функция для отката транзакции
function rollback() {
    $pdo = getDBConnection();
    return $pdo->rollBack();
}
?>