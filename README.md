# CraftRolle - 3D Kitap Görüntüleyici

Gerçekçi 3D sayfa çevirme efekti ile hikayelerinizi yazın ve okuyun! 🌸📚

## ✨ Özellikler

- 📖 **Gerçekçi 3D Kitap Görüntüleyici**: Fiziksel bir kitabı taklit eden animasyonlar ve gölgelerle sayfa çevirme
- 📚 **Kitap Yönetimi**: Kitap oluşturma, düzenleme ve yönetme
- 📝 **Not Sistemi**: Kitaplarınız için notlar alın
- 🎨 **Kapak Tasarım Aracı**: Kitaplarınız için özel kapaklar tasarlayın
- 🗺️ **Harita Tasarım Aracı**: Hikayeleriniz için haritalar oluşturun
- 📄 **PDF/Yazdırma**: Kitaplarınızı PDF olarak indirin veya yazdırın
- ⌨️ **Klavye Desteği**: Ok tuşları ile sayfa çevirme
- 📱 **Mobil Uyumlu**: Kaydırma (swipe) hareketleri ile mobil desteği
- 🖱️ **Fare İzleme**: Fareyi hareket ettirerek kitabı hafifçe döndürme

## 🚀 Kurulum

### Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu

### Adımlar

1. **Veritabanını Oluşturun**
   ```bash
   mysql -u root -p < database.sql
   ```

2. **Veritabanı Ayarlarını Yapılandırın**
   
   `src/config.php` dosyasını düzenleyin:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'craftrolle');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('BASE_URL', 'http://localhost/public/');
   ```

3. **Web Sunucusunu Yapılandırın**
   
   Apache için `.htaccess` veya Nginx için uygun yapılandırmayı kullanın. 
   Document root olarak `public/` klasörünü ayarlayın.

4. **Klasör İzinlerini Ayarlayın**
   ```bash
   chmod -R 755 uploads/
   ```

5. **Tarayıcıda Açın**
   
   `http://localhost/public/index.php` adresine gidin

## 📖 3D Kitap Görüntüleyici Kullanımı

### Klavye Kontrolleri
- **←** (Sol Ok): Önceki sayfa
- **→** (Sağ Ok): Sonraki sayfa

### Fare Kontrolleri
- **Butonlar**: "◀ Önceki Sayfa" ve "Sonraki Sayfa ▶" butonlarını kullanın
- **Fare Hareketi**: Fareyi kitap üzerinde hareket ettirerek 3D perspektifi değiştirin

### Mobil Kontroller
- **Kaydırma**: Sola kaydırarak ileri, sağa kaydırarak geri gidin

## 🎨 3D Görüntüleyici Özellikleri

- **Gerçekçi Sayfa Çevirme**: Smooth cubic-bezier animasyonlar
- **Kitap Sırtı**: Orta kısımda gerçekçi kitap sırtı
- **Kağıt Dokusu**: Sayfalarda ince kağıt dokusu efekti
- **Gölgelendirme**: Derinlik hissi veren dinamik gölgeler
- **Sayfa Numaraları**: Her sayfanın alt kısmında numara
- **Otomatik Sayfalama**: İçerik otomatik olarak sayfalara bölünür
- **Responsive Tasarım**: Mobil, tablet ve masaüstü uyumlu

## 📁 Proje Yapısı

```
craftrolle/
├── public/              # Genel erişilebilir dosyalar
│   ├── index.php       # Ana sayfa
│   ├── view_book.php   # 3D Kitap görüntüleyici
│   └── ...
├── src/                # Backend kod
│   ├── config.php     # Veritabanı ve genel ayarlar
│   ├── helpers.php    # Yardımcı fonksiyonlar
│   ├── auth.php       # Kimlik doğrulama
│   └── csrf.php       # CSRF koruması
├── assets/            # Statik dosyalar
│   └── css/
│       └── style.css  # 3D görüntüleyici dahil tüm stiller
├── uploads/           # Kullanıcı yüklemeleri
└── database.sql       # Veritabanı şeması
```

## 🔐 Varsayılan Giriş Bilgileri

- **Kullanıcı Adı**: demo
- **Şifre**: password

## 🛠️ Teknolojiler

- **Backend**: PHP 7.4+
- **Veritabanı**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **3D Efektler**: CSS3 Transforms, Perspective, Transitions

## 📝 3D Görüntüleyici Kodu

3D kitap görüntüleyici aşağıdaki teknolojileri kullanır:

- **CSS Transform**: `rotateY()` ile gerçekçi sayfa çevirme
- **CSS Perspective**: 2000px perspektif derinliği
- **Transform-Origin**: Sayfa dönüş noktası kontrolü
- **Z-Index**: Doğru sayfa sıralaması için dinamik z-index
- **Cubic-Bezier**: Smooth sayfa çevirme animasyonu
- **Backface Visibility**: Sayfa arka yüzü gizleme

## 🤝 Katkıda Bulunma

1. Fork edin
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit edin (`git commit -m 'Add some amazing feature'`)
4. Push edin (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 🌟 Özellikler Yakında

- [ ] Sesli okuma
- [ ] Yer imleri
- [ ] Tema seçenekleri (gece modu)
- [ ] Sayfa arka plan renk/doku seçenekleri
- [ ] PDF'den içe aktarma
- [ ] Çoklu dil desteği

---

© 2025 CraftRolle - Gerçekçi 3D Kitap Görüntüleyici 🌸
