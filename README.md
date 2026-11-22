# ديكورات الرياض

موقع عربي لخدمات الدهانات والتشطيبات في الرياض. ملفات القالب هي مصدر المحتوى الذي تُبنى منه صفحات العرض العامة.

## النشر في GitHub Pages

- يبني GitHub Actions نسخة المعاينة في فرع `web-preview` عند الدفع إلى `main` أو عند تشغيل **Publish Riyadh Decor** يدويًا. تفتح النسخة من [معاينة الموقع](https://raw.githack.com/SulaimanAlsanani/riyadh-decor/web-preview/index.html).
- من إعدادات المستودع، اختر **Settings → Pages → Source → GitHub Actions** مرة واحدة.
- تستخدم العملية WordPress وPHP في مرحلة البناء. يستضيف GitHub Pages ملفات HTML وCSS وJavaScript والأصول الناتجة.
- تظل عملية البحث وعارض الصور وتحميل المقالات وتجهيز طلب واتساب متاحة في النسخة المنشورة. لوحة تحرير WordPress تعمل عند تثبيت القالب على WordPress. تحديث نسخة GitHub يبدأ من ملفات المستودع.

## تعديل المحتوى

- الخدمات ونصوص المقالات الأولية والصور المرتبطة في `riyadh-decor/inc/catalog.php`.
- إعداد الصفحات والتصنيفات في `riyadh-decor/inc/setup.php`.
- الهاتف ورقم واتساب الافتراضي في `riyadh-decor/functions.php`، والشعار في `riyadh-decor/assets/logo.png`.
- الواجهات في ملفات القالب، والتنسيق في `riyadh-decor/style.css`.

## البناء محليًا

يتطلب Node.js 22 أو أحدث و`curl` و`unzip`.

```sh
npm ci
npm run build -- http://localhost:8080/
node scripts/check-export.mjs
python3 -m http.server 8080 --directory dist
```

الموقع المعروض في المتصفح المحلي يستخدم المنفذ 8080. لتوليد العناوين الأصلية على نطاق الإنتاج، مرّر عنوان GitHub Pages النهائي إلى الأمر `npm run build`.
