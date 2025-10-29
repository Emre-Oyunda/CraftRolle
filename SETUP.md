# CraftRolle Kurulum Rehberi

## Hızlı Başlangıç 🚀

### 1. Veritabanını Kur
```bash
mysql -u root -p < database.sql
```

### 2. Yapılandırma
`src/config.php` dosyasını düzenle:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'craftrolle');
define('DB_USER', 'root');
define('DB_PASS', 'SİFRENİZ');
define('BASE_URL', 'http://localhost/public/');
```

### 3. Klasör İzinleri
```bash
chmod -R 755 uploads/
```

### 4. Test Et
Tarayıcıda aç: `http://localhost/public/index.php`

## Varsayılan Giriş Bilgileri

- **Kullanıcı Adı**: demo
- **Şifre**: password

## 3D Kitap Görüntüleyici

3D kitap görüntüleyici: `public/3d/view_book.php?id=1`

### Kontroller:
- **Klavye**: ← ve → ok tuşları
- **Fare**: Butonlar veya fareyi kitap üzerinde hareket ettir
- **Mobil**: Sağa/sola kaydır (swipe)

## Proje Yapısı

```
craftrolle/
├── public/              # Web root
│   ├── 3d/             # 3D Kitap Görüntüleyici ⭐
│   │   └── view_book.php
│   ├── index.php       # Ana sayfa
│   └── ...             # Diğer sayfalar
├── src/                # Backend
├── assets/             # CSS, JS
├── uploads/            # Yüklemeler
└── database.sql        # Veritabanı
```

## Apache Yapılandırması

### .htaccess
Zaten `public/.htaccess` dosyası mevcut.

### VirtualHost (Opsiyonel)
```apache
<VirtualHost *:80>
    ServerName craftrolle.local
    DocumentRoot "/path/to/craftrolle/public"
    
    <Directory "/path/to/craftrolle/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Nginx Yapılandırması (Opsiyonel)

```nginx
server {
    listen 80;
    server_name craftrolle.local;
    root /path/to/craftrolle/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

## Sorun Giderme

### Veritabanı Bağlantı Hatası
- `src/config.php` dosyasında DB bilgilerini kontrol et
- MySQL servisinin çalıştığından emin ol

### 404 Hatası
- `.htaccess` dosyasının `public/` klasöründe olduğundan emin ol
- Apache'de `mod_rewrite` modülü aktif mi kontrol et

### Upload Klasör Hatası
```bash
chmod -R 755 uploads/
```

### 3D Görüntüleyici Çalışmıyor
- CSS dosyası yolu doğru mu: `../../assets/css/style.css`
- JavaScript hataları için tarayıcı konsolunu kontrol et

## Özellikler

✅ **3D Kitap Görüntüleyici** - Gerçekçi sayfa çevirme
✅ **Kitap Yönetimi** - Oluştur, düzenle, sil
✅ **Not Sistemi** - Kitaplarınız için notlar
✅ **PDF Export** - Yazdır veya PDF indir
✅ **Kapak Tasarımı** - Özel kapaklar oluştur
✅ **Harita Tasarımı** - Hikaye haritaları çiz
✅ **Mobil Uyumlu** - Responsive tasarım
✅ **Güvenli** - CSRF koruması, güvenli şifreler

## Destek

Sorunlarınız için:
- README.md dosyasını okuyun
- Proje yapısını kontrol edin
- Hata loglarını inceleyin

---

**CraftRolle** © 2025 - Hikayelerinizi gerçekçi 3D görüntüleyici ile okuyun! 🌸📚
