-- MySQL Kullanıcısı Oluşturma (Opsiyonel - Daha Güvenli)

-- Yöntem 1: Özel MySQL kullanıcısı oluştur (ÖNERİLEN)
CREATE USER 'craftrolle_db'@'localhost' IDENTIFIED WITH mysql_native_password BY 'craft123';
GRANT ALL PRIVILEGES ON craftrolle.* TO 'craftrolle_db'@'localhost';
FLUSH PRIVILEGES;

-- Sonra src/config.php dosyasını güncelle:
-- define('DB_USER', 'craftrolle_db');
-- define('DB_PASS', 'craft123');


-- Yöntem 2: Root kullanıcısını düzelt (MySQL 8.0 için)
-- ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
-- FLUSH PRIVILEGES;
