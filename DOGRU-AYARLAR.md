# ⚠️ ÖNEMLİ: Doğru Ayarlar

## Karışıklık Düzeltmesi

### 🔴 YANLIŞ KULLANIM:
```php
// src/config.php
define('DB_USER', 'demo');      // ❌ YANLIŞ! Bu uygulama kullanıcısı
define('DB_PASS', 'password');  // ❌ YANLIŞ!
```

### ✅ DOĞRU KULLANIM:

#### src/config.php - MySQL Sunucu Bağlantısı
```php
define('DB_USER', 'root');    // ✅ MySQL sunucu kullanıcısı
define('DB_PASS', '');        // ✅ MySQL sunucu şifresi (XAMPP için boş)
```

#### Uygulama Girişi - Web Sayfasından
```
Kullanıcı Adı: demo
Şifre: password
```
Bu bilgiler **veritabanındaki users tablosunda** saklanıyor, config.php'de değil!

---

## 🎯 Hızlı Çözüm

### XAMPP/WAMP Kullanıyorsan:

**src/config.php**:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'craftrolle');
define('DB_USER', 'root');    // XAMPP/WAMP varsayılanı
define('DB_PASS', '');        // XAMPP/WAMP varsayılanı (boş)
```

### Canlı Sunucu veya MySQL 8.0:

**Seçenek 1: Root kullan (MySQL 8.0 düzeltmesi ile)**
```sql
mysql -u root -p
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
EXIT;
```

**Seçenek 2: Özel kullanıcı oluştur (Daha Güvenli)**
```sql
mysql -u root -p
CREATE USER 'craftrolle_db'@'localhost' IDENTIFIED WITH mysql_native_password BY 'craft123';
GRANT ALL PRIVILEGES ON craftrolle.* TO 'craftrolle_db'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Sonra **src/config.php** güncelle:
```php
define('DB_USER', 'craftrolle_db');
define('DB_PASS', 'craft123');
```

---

## 📋 İki Farklı Kullanıcı Var!

### 1️⃣ MySQL Sunucu Kullanıcısı (config.php)
- **Nerede**: `src/config.php`
- **Ne için**: Veritabanına bağlanmak için
- **Örnekler**: `root`, `craftrolle_db`
- **Nereden**: MySQL sunucusu

### 2️⃣ Uygulama Kullanıcısı (Web Giriş)
- **Nerede**: `users` tablosunda
- **Ne için**: Web sitesine giriş yapmak için
- **Örnekler**: `demo` (username: demo, password: password)
- **Nereden**: `database.sql` ile oluşturulan kayıt

---

## 🔧 Adım Adım Kurulum

### 1. Veritabanını Oluştur
```bash
mysql -u root -p < database.sql
```
Bu, veritabanını VE 'demo' **uygulama kullanıcısını** oluşturur.

### 2. src/config.php'yi Ayarla

**XAMPP/WAMP için**:
```php
define('DB_USER', 'root');
define('DB_PASS', '');
```

**Canlı sunucu için**:
```php
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

### 3. MySQL 8.0 Hatası Alırsan
```sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
```

### 4. Web Sitesine Giriş Yap
```
http://localhost/public/login.php

Kullanıcı Adı: demo
Şifre: password
```

---

## ✅ Test Et

1. Tarayıcıda aç: `http://localhost/public/index.php`
2. "Giriş" butonuna tıkla
3. Kullanıcı: `demo`, Şifre: `password`
4. Başarılı! 🎉

---

## 🆘 Hala Hata Alıyorsan?

### "SQLSTATE[HY000] [2054]" Hatası:
```bash
# MySQL terminalinde:
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
```

### "Access denied for user 'demo'@'localhost'":
❌ config.php'de yanlış kullanıcı! 'demo' MySQL kullanıcısı değil, uygulama kullanıcısı.
✅ config.php'yi `'root'` olarak değiştir.

### "Unknown database 'craftrolle'":
```bash
mysql -u root -p < database.sql
```

---

## 📝 Özet

| Dosya/Yer | Kullanıcı Tipi | Örnek | Amaç |
|-----------|---------------|-------|------|
| **src/config.php** | MySQL Kullanıcısı | `root` | Veritabanı bağlantısı |
| **login.php** | Uygulama Kullanıcısı | `demo` | Web giriş |
| **users tablosu** | Uygulama Kullanıcısı | `demo` | Kayıtlı kullanıcılar |

🎯 **Kural**: config.php'de MySQL kullanıcısı, web sayfasında uygulama kullanıcısı!
