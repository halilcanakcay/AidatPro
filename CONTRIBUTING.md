# Contributing / Katkı Rehberi

Thank you for helping improve AidatPro. Bug reports, documentation improvements, translations, UI fixes and new features are welcome.

AidatPro'yu geliştirmeye destek olduğunuz için teşekkürler. Hata bildirimleri, dokümantasyon iyileştirmeleri, çeviriler, arayüz düzeltmeleri ve yeni özellikler memnuniyetle karşılanır.

## English

1. Open an issue before large changes so the scope can be discussed.
2. Keep pull requests focused on one topic.
3. Do not commit `.env`, secrets, real resident data, production databases or bank credentials.
4. Follow the existing Laravel structure and naming style.
5. Run the relevant checks before opening a pull request:

```bash
composer install
npm install
php artisan test
npm run build
```

6. Update documentation when behavior, installation steps or configuration values change.
7. Describe the problem, the solution and manual testing notes in the pull request.

## Türkçe

1. Büyük değişikliklerden önce kapsamı konuşmak için issue açın.
2. Pull request'leri tek konuya odaklı tutun.
3. `.env`, gizli anahtar, gerçek sakin verisi, üretim veritabanı veya banka bilgisi commit etmeyin.
4. Mevcut Laravel yapısına ve isimlendirme düzenine uyun.
5. Pull request açmadan önce ilgili kontrolleri çalıştırın:

```bash
composer install
npm install
php artisan test
npm run build
```

6. Davranış, kurulum adımı veya yapılandırma değeri değişiyorsa dokümantasyonu güncelleyin.
7. Pull request açıklamasında problemi, çözümü ve manuel test notlarını belirtin.

## Development Branches

Use short branch names:

```text
fix/payment-filter
feature/email-announcement-log
docs/installation-guide
```

## License

By contributing, you agree that your contribution will be released under the MIT License.
