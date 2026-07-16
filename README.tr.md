# AidatPro

AidatPro; apartmanlar, siteler ve toplu yaşam alanları için geliştirilen ücretsiz ve açık kaynaklı aidat yönetim panelidir. Laravel, Vite ve Tabler altyapısı üzerine kuruludur.

Demo: [https://aidat.halilcan.dev](https://aidat.halilcan.dev)

Demo girişi:

```text
E-posta: skulloger@gmail.com
Şifre: asdasdasd
```

Dil seçenekleri: [Türkçe](README.tr.md) | [English](README.en.md)

## Amaç

AidatPro, apartman ve site yöneticilerinin daireleri, sakinleri, aidat tahakkuklarını, ödemeleri, giderleri, duyuruları, raporları ve banka hareketlerini tek panelden yönetebilmesi için hazırlanmıştır. MIT lisansı ile yayımlandığı için herkes projeyi ücretsiz kullanabilir, geliştirebilir ve kendi ihtiyacına göre uyarlayabilir.

## Özellikler

- Birden fazla site, blok, daire ve sakin yönetimi
- Aidat tahakkuku, ödeme alma, kısmi ödeme ve borç takibi
- Gelir, gider, tahsilat, bekleyen borç ve genel durum özetleri
- PDF makbuz ve yönetim raporları
- Site, blok, yıl, ay ve ödeme kaynağı filtreleri
- Duyuru hazırlama, e-posta gönderimi ve WhatsApp Web aktarımı
- Süreli daire sahibi rapor bağlantıları
- Yetkili kullanıcı hesapları
- VakıfBank işlem izleme ekranı, manuel eşleştirme ve zamanlayıcıya hazır entegrasyon altyapısı
- Telegram, e-posta ve korumalı paket indirme ayarları

## Teknoloji

- PHP 8.3+
- Laravel 13
- Vite 8
- Tabler UI
- MySQL/MariaDB veya SQLite
- Dompdf ile PDF çıktıları

## Hızlı Başlangıç

Ayrıntılı kurulum adımları için [KURULUM.md](KURULUM.md) dosyasını okuyun.

Paketle gelen örnek SQLite veritabanı:

```text
database/aidatpro.sqlite
```

Örnek yönetici hesabı:

```text
E-posta: skulloger@gmail.com
Şifre: asdasdasd
```

İlk girişten sonra yönetici şifresini mutlaka değiştirin ve gerçek kullanıma geçmeden önce demo verilerini temizleyin.

## Geliştirme

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan test
```

Yerel geliştirme sunucusu için:

```bash
composer run dev
```

## VakıfBank Entegrasyonu

Arayüz, ayarlar, manuel eşleştirme ve zamanlayıcı altyapısı hazırdır. Gerçek hesap hareketlerinin çekilebilmesi için bankanın Online Hesap Hareketleri hizmetinin kurum hesabı için açılması ve SOAP servis bilgilerinin sisteme girilmesi gerekir. Demo ortamı gerçek banka hesabına bağlanmaz.

## Katkı

Hata bildirimi, özellik önerisi ve pull request göndermekten çekinmeyin. Başlamadan önce [CONTRIBUTING.md](CONTRIBUTING.md) dosyasını okuyun.

## Güvenlik

Gerçek kişi verilerini, banka bilgilerini, üretim `.env` dosyalarını veya gizli anahtarları paylaşmayın. Ayrıntılar [SECURITY.md](SECURITY.md) dosyasındadır.

## Lisans

AidatPro MIT lisansı ile yayımlanır. Ayrıntılar [LICENSE](LICENSE) dosyasındadır.
