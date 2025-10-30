# MySQL 8.0 Kimlik Doğrulama Hatası Çözümü

## Hata:
```
SQLSTATE[HY000] [2054] Sunucu, istemci tarafından bilinmeyen kimlik doğrulama yöntemi istedi
```

## Neden Oluyor?
MySQL 8.0, varsayılan olarak `caching_sha2_password` kullanıyor, ancak eski PHP sürümleri bunu desteklemiyor.

## Çözüm 1: MySQL Kullanıcı Kimlik Doğrulamasını Değiştir (ÖNERİLEN)

### Terminal veya MySQL Console'da çalıştır:

```sql
-- MySQL'e giriş yap
mysql -u root -p

-- Kimlik doğrulama yöntemini değiştir
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';

-- Eğer şifreniz varsa:
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';

-- Değişiklikleri uygula
FLUSH PRIVILEGES;

-- Çık
EXIT;
```

### Ya da hazır SQL dosyasını kullan:
```bash
mysql -u root -p < fix-mysql8-auth.sql
```

## Çözüm 2: Yeni Kullanıcı Oluştur (Daha Güvenli)

```sql
-- MySQL'e giriş yap
mysql -u root -p

-- Yeni kullanıcı oluştur
CREATE USER 'craftrolle_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'guvenli_sifre_123';

-- Veritabanı yetkilerini ver
GRANT ALL PRIVILEGES ON craftrolle.* TO 'craftrolle_user'@'localhost';

-- Değişiklikleri uygula
FLUSH PRIVILEGES;

-- Çık
EXIT;
```

Sonra `src/config.php` dosyasını güncelle:
```php
define('DB_USER', 'craftrolle_user');
define('DB_PASS', 'guvenli_sifre_123');
```

## Çözüm 3: XAMPP/WAMP Kullanıyorsanız

### XAMPP:
1. XAMPP Control Panel'i aç
2. MySQL'i durdur
3. Config butonuna tıkla → my.ini
4. Şu satırı ekle:
   ```ini
   [mysqld]
   default_authentication_plugin=mysql_native_password
   ```
5. MySQL'i yeniden başlat

### WAMP:
1. Sol alt köşedeki WAMP ikonuna tıkla
2. MySQL → MySQL Settings → my.ini
3. Yukarıdaki satırı ekle
4. Servisleri yeniden başlat

## Çözüm 4: PHP 7.4+ ve MySQL 8.0+ İçin

Eğer güncel PHP kullanıyorsanız, `mysqlnd` eklentisinin güncel olduğundan emin olun:
```bash
php -m | grep mysqlnd
```

## Test Et

Sorunu çözdükten sonra tarayıcıdan test edin:
```
http://localhost/public/index.php
```

## Hala Çalışmıyorsa?

1. **PHP Sürümünü Kontrol Et**:
   ```bash
   php -v
   ```
   En az PHP 7.4+ olmalı.

2. **MySQL Sürümünü Kontrol Et**:
   ```sql
   SELECT VERSION();
   ```

3. **Bağlantı Bilgilerini Kontrol Et**:
   - `src/config.php` dosyasındaki DB_HOST, DB_NAME, DB_USER, DB_PASS değerlerini kontrol et.

4. **Veritabanının Var Olduğundan Emin Ol**:
   ```sql
   SHOW DATABASES;
   ```

5. **Hata Loglarını İncele**:
   - Apache: `error.log`
   - MySQL: `mysql-error.log`

## Özet Komutlar (En Hızlı Çözüm)

```bash
# 1. MySQL'e giriş
mysql -u root -p

# 2. Bu komutu çalıştır
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
EXIT;

# 3. Tarayıcıyı yenile
```

✅ **Artık çalışmalı!**
