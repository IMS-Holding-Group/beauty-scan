<?php
declare(strict_types=1);

function db(): PDO {
 static $pdo = null;
 if ($pdo instanceof PDO) { return $pdo; }
 $host = env('DB_HOST', '127.0.0.1');
 $port = env('DB_PORT', '3306');
 $name = env('DB_NAME', 'beauty_scan');
 $user = env('DB_USER');
 $pass = env('DB_PASSWORD');
 if ($user === '' || !preg_match('/^[A-Za-z0-9_]+$/', $name)) { throw new RuntimeException('db config'); }
 $options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
  PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
 ];
 $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
 try {
  $pdo = new PDO($dsn, $user, $pass, $options); }
 catch (PDOException $error) {
  $server = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, $options);
  $server->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
  $pdo = new PDO($dsn, $user, $pass, $options); }
 $pdo->exec("SET time_zone = '+00:00'");
 return $pdo; }

function db_all(string $sql, array $params = []): array {
 $stmt = db()->prepare($sql);
 $stmt->execute($params);
 $rows = $stmt->fetchAll();
 return is_array($rows) ? $rows : []; }

function db_one(string $sql, array $params = []): ?array {
 $stmt = db()->prepare($sql);
 $stmt->execute($params);
 $row = $stmt->fetch();
 return $row === false ? null : $row; }

function db_exec(string $sql, array $params = []): int {
 $stmt = db()->prepare($sql);
 $stmt->execute($params);
 return $stmt->rowCount(); }

function db_insert(string $sql, array $params = []): string {
 db_exec($sql, $params);
 return db()->lastInsertId(); }

function init_db(): void {
 $statements = [
  "CREATE TABLE IF NOT EXISTS users (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   username VARCHAR(30) NOT NULL UNIQUE,
   display_name VARCHAR(60) NOT NULL,
   email VARCHAR(120) NULL,
   password_hash VARCHAR(255) NOT NULL,
   notice_days INT NOT NULL DEFAULT 30,
   onboarding_done TINYINT NOT NULL DEFAULT 0,
   is_admin TINYINT NOT NULL DEFAULT 0,
   created_at CHAR(25) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS sessions (
   id CHAR(64) PRIMARY KEY,
   user_id INT UNSIGNED NOT NULL,
   csrf_token VARCHAR(128) NOT NULL,
   created_at CHAR(25) NOT NULL,
   last_seen_at CHAR(25) NOT NULL,
   CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS login_attempts (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   username VARCHAR(30) NOT NULL,
   ip VARCHAR(45) NOT NULL,
   attempted_at CHAR(25) NOT NULL,
   INDEX idx_attempts (username, ip, attempted_at)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS products (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   barcode VARCHAR(13) NOT NULL UNIQUE,
   name VARCHAR(300) NULL,
   brand VARCHAR(200) NULL,
   category VARCHAR(200) NULL,
   ingredients_text TEXT NULL,
   source VARCHAR(32) NOT NULL,
   is_verified TINYINT NOT NULL DEFAULT 0,
   fetched_at CHAR(25) NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS ingredients (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   inci_name VARCHAR(160) NOT NULL UNIQUE,
   arabic_name VARCHAR(160) NOT NULL,
   function_ar VARCHAR(255) NOT NULL,
   notes_ar VARCHAR(400) NULL,
   synonyms VARCHAR(400) NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS user_allergens (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   user_id INT UNSIGNED NOT NULL,
   name VARCHAR(80) NOT NULL,
   normalized_name VARCHAR(80) NOT NULL,
   ingredient_id INT UNSIGNED NULL,
   UNIQUE KEY uniq_user_allergen (user_id, normalized_name),
   CONSTRAINT fk_allergens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
   CONSTRAINT fk_allergens_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE SET NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS user_products (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   user_id INT UNSIGNED NOT NULL,
   product_id INT UNSIGNED NULL,
   custom_name VARCHAR(120) NULL,
   barcode VARCHAR(13) NULL,
   expiry_date CHAR(10) NOT NULL,
   opened_date CHAR(10) NULL,
   pao_months INT NULL,
   effective_expiry_date CHAR(10) NOT NULL,
   notes VARCHAR(500) NULL,
   created_at CHAR(25) NOT NULL,
   INDEX idx_user_products (user_id),
   CONSTRAINT fk_user_products_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
   CONSTRAINT fk_user_products_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
  "CREATE TABLE IF NOT EXISTS notifications (
   id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
   user_id INT UNSIGNED NOT NULL,
   user_product_id INT UNSIGNED NULL,
   type VARCHAR(20) NOT NULL,
   message VARCHAR(500) NOT NULL,
   dedupe_key VARCHAR(80) NOT NULL UNIQUE,
   is_read TINYINT NOT NULL DEFAULT 0,
   created_at CHAR(25) NOT NULL,
   INDEX idx_notifications_user (user_id, is_read),
   CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
   CONSTRAINT fk_notifications_item FOREIGN KEY (user_product_id) REFERENCES user_products(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
 ];
 foreach ($statements as $sql) { db()->exec($sql); }
 ensure_admin_column();
 seed_if_needed(); }

function ensure_admin_column(): void {
 $rows = db()->query('SHOW COLUMNS FROM users')->fetchAll();
 foreach ($rows as $col) {
  if (($col['Field'] ?? '') === 'is_admin') { return; } }
 db()->exec('ALTER TABLE users ADD COLUMN is_admin TINYINT NOT NULL DEFAULT 0'); }
