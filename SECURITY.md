# Security Policy / Güvenlik Politikası

## English

AidatPro can store personal resident information, payment records and bank integration settings. Treat every installation as sensitive.

Please do not open public issues that contain:

- Production `.env` files
- Database dumps with real people or payment records
- Bank credentials, SOAP credentials or API tokens
- Server passwords, SSH keys or private certificates
- Screenshots that expose personal information

For public bug reports, replace sensitive values with placeholders.

Recommended production settings:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

Rotate credentials immediately if they were published by mistake.

## Türkçe

AidatPro gerçek sakin bilgileri, ödeme kayıtları ve banka entegrasyon ayarları saklayabilir. Her kurulumu hassas kabul edin.

Lütfen herkese açık issue veya pull request içine şunları eklemeyin:

- Üretim `.env` dosyaları
- Gerçek kişi veya ödeme kaydı içeren veritabanı dökümleri
- Banka bilgileri, SOAP bilgileri veya API token'ları
- Sunucu şifreleri, SSH anahtarları veya özel sertifikalar
- Kişisel veri gösteren ekran görüntüleri

Herkese açık hata bildirimlerinde hassas değerleri örnek değerlerle değiştirin.

Önerilen üretim ayarları:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

Yanlışlıkla gizli bilgi yayımlandıysa ilgili şifreleri ve anahtarları hemen yenileyin.
