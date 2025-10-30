-- MySQL 8.0 Kimlik Doğrulama Sorunu Çözümü

-- Kullanıcı için eski kimlik doğrulama yöntemini kullan
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';

-- Veya şifreliyse:
-- ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';

-- Yeni kullanıcı oluşturmak isterseniz (önerilen):
-- CREATE USER 'craftrolle_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'guvenli_sifre';
-- GRANT ALL PRIVILEGES ON craftrolle.* TO 'craftrolle_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Değişiklikleri uygula
FLUSH PRIVILEGES;
