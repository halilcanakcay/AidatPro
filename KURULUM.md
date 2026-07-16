# AidatPro Kurulum Rehberi

Bu rehber AidatPro'yu yerel geliştirme ortamında, paylaşımlı hostingde veya klasik VPS/sunucu ortamında çalıştırmak için gereken adımları anlatır.

Demo adresi: [https://aidat.halilcan.dev](https://aidat.halilcan.dev)

English setup guide: [INSTALLATION.md](INSTALLATION.md)

## Gereksinimler

- PHP 8.3 veya üzeri
- PHP eklentileri: BCMath, Ctype, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer ve XML
- MySQL 8+, MariaDB 10.6+ veya SQLite 3
- Apache `mod_rewrite` veya eşdeğer Nginx yönlendirmesi
- Cron görevi oluşturma yetkisi
- Üretim ortamında geçerli SSL sertifikası
- Kaynak koddan geliştirme için Composer 2, Node.js ve npm

`vendor` ve `public/build` klasörleri GitHub deposuna dahil edilmez. Sunucuda veya yerelde `composer install` ve `npm run build` komutlarını çalıştırmanız önerilir. ZIP dağıtım paketiniz bu klasörleri içeriyorsa hızlı kurulumda Composer/Node çalıştırmadan da başlayabilirsiniz.

## Dosya Yerleşimi

Önerilen belge kökü proje içindeki `public` klasörüdür:

```text
/home/kullanici/aidatpro
/home/kullanici/aidatpro/public  -> alan adının belge kökü
```

Paylaşımlı hostingde belge kökünü `public` olarak değiştiremiyorsanız paket kök dizinindeki `index.php` ve `.htaccess` dosyaları Apache üzerinde kökten çalışmayı destekler.

## Hızlı SQLite Kurulumu

SQLite küçük apartmanlar, demo ortamları ve hızlı testler için uygundur.

1. Dosyaları sunucuya yükleyin.
2. `.env.example` dosyasını `.env` olarak kopyalayın.
3. `.env` içindeki temel alanları düzenleyin:

```env
APP_NAME=AidatPro
APP_ENV=production
APP_DEBUG=false
APP_URL=https://alanadiniz.com
APP_TIMEZONE=Europe/Istanbul

DB_CONNECTION=sqlite
DB_DATABASE=/tam/sunucu/yolu/database/aidatpro.sqlite
```

4. Uygulama anahtarı üretin:

```bash
php artisan key:generate
```

5. SQLite dosyasının yazılabilir olduğundan emin olun:

```bash
chmod 664 database/aidatpro.sqlite
```

6. Laravel bağlantılarını ve önbelleği hazırlayın:

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
php artisan optimize:clear
php artisan optimize
```

## MySQL / MariaDB Kurulumu

Orta ve büyük ölçekli kullanımlar için MySQL veya MariaDB önerilir.

1. Boş bir veritabanı ve kullanıcı oluşturun.
2. `.env` dosyasındaki veritabanı alanlarını düzenleyin:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aidatpro
DB_USERNAME=aidatpro
DB_PASSWORD=guclu_veritabani_sifresi
```

3. Örnek verilerle kurulum:

```bash
php artisan migrate --seed --force
php artisan optimize
```

4. Boş kurulum istiyorsanız `--seed` parametresini kullanmayın:

```bash
php artisan migrate --force
php artisan optimize
```

## Varsayılan Demo Hesabı

Örnek verilerle kurulumda aşağıdaki yönetici hesabı oluşur:

```text
E-posta: admin@aidat.local
Şifre: Admin123!
```

İlk girişten sonra bu şifreyi değiştirin. Üretim ortamında demo kullanıcılarını, örnek sakinleri ve örnek finansal kayıtları temizleyin.

## E-posta Ayarları

Duyuru e-postaları için `.env` içindeki `MAIL_*` değerlerini hosting sağlayıcınızın SMTP bilgileriyle doldurun:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=mail@example.com
MAIL_PASSWORD=gizli_sifre
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mail@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Değişiklikten sonra:

```bash
php artisan config:cache
```

## Zamanlayıcı

Laravel zamanlayıcısının çalışması için sunucunun cron alanına dakikada bir çalışan görev ekleyin:

```cron
* * * * * cd /tam/proje/yolu && php artisan schedule:run >> /dev/null 2>&1
```

Zamanlayıcı; aktif banka entegrasyonları, planlı işler ve kuyrukla ilişkili bakım görevleri için gereklidir.

## Kuyruk

Basit kurulumlarda `QUEUE_CONNECTION=database` yeterlidir. Uzun süren e-posta veya entegrasyon işlemleri için ayrı bir queue worker çalıştırın:

```bash
php artisan queue:work --tries=3 --timeout=120
```

Process manager kullanıyorsanız worker'ı Supervisor veya hosting panelinizdeki servis yöneticisi ile kalıcı hale getirin.

## VakıfBank Entegrasyonu

Panelde VakıfBank işlem izleme, ayarlar ve manuel eşleştirme ekranları bulunur. Gerçek hareketleri çekebilmek için:

- VakıfBank Online Hesap Hareketleri hizmetinin kurum hesabınız için açılmış olması gerekir.
- Servis URL'si, müşteri numarası, kullanıcı bilgileri ve kurumunuza verilen diğer SOAP bilgileri panelde tanımlanmalıdır.
- Demo paket gerçek banka hesabına bağlanmaz.
- Canlıya geçmeden önce test hesabı veya düşük riskli bir ortamda deneme yapın.

## Dosya İzinleri

Üretimde sadece gerekli klasörlere yazma izni verin:

```bash
chmod -R 775 storage bootstrap/cache
```

Kaynak kod, yapılandırma ve uygulama dosyalarına web sunucusu tarafından yazma izni vermeyin.

## Güvenlik Kontrol Listesi

- `APP_ENV=production` olmalıdır.
- `APP_DEBUG=false` olmalıdır.
- `APP_URL` gerçek HTTPS adresini göstermelidir.
- `.env` dosyası web üzerinden erişilebilir olmamalıdır.
- `admin@aidat.local` şifresi değiştirilmelidir.
- Üretim veritabanı şifresi güçlü ve benzersiz olmalıdır.
- Gerçek kişisel veriler GitHub'a veya herkese açık demo ortamlarına yüklenmemelidir.
- Düzenli veritabanı ve dosya yedeği alınmalıdır.
- SSL sertifikası aktif olmalıdır.

## Geliştirme Kurulumu

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

Geliştirme sunucusu:

```bash
composer run dev
```

## Yayınlama Sonrası

Kod değişikliklerinden sonra genellikle şu komutlar yeterlidir:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

## Sorun Giderme

- Beyaz ekran veya 500 hatası: `storage/logs/laravel.log` dosyasını kontrol edin.
- Stil dosyaları gelmiyorsa: `npm run build` ve `php artisan optimize:clear` çalıştırın.
- Giriş sonrası oturum düşüyorsa: `APP_URL`, `SESSION_DOMAIN` ve `SESSION_SECURE_COOKIE` değerlerini kontrol edin.
- SQLite yazma hatası: `database/aidatpro.sqlite` dosya iznini ve klasör sahibini kontrol edin.
- E-posta gitmiyorsa: SMTP bilgilerini ve hosting sağlayıcısının çıkış portu kısıtlarını kontrol edin.
