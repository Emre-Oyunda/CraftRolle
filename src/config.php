<?php
define('APP_NAME', 'CraftRolle');
define('BASE_URL', 'http://localhost/public/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'craftrolle');
define('DB_USER', 'root');  // MySQL sunucu kullanıcısı (XAMPP/WAMP için root)
define('DB_PASS', '');      // MySQL sunucu şifresi (XAMPP için genelde boş)

function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }
    }
    return $pdo;
}

session_start();
