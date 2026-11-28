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

## خطة المحتوى المعتمدة V3.0

المستهدف 178 صفحة محتوى تحريرية (لا يشمل صفحات الترقيم والتصنيفات التقنية). العدد النهائي يُراجع حسب عدم التكرار وملاءمة نطاق الخدمات. المراحل:

| الإصدار | النطاق |
|---|---|
| V2.3.0 | الهيكل والتنقل والرئيسية |
| V2.4.0 | الخدمات الست عشرة والمعارض |
| V2.5.0 | المساحات وأنواع العقارات |
| V2.6.0 | التصميم والخامات والمقارنات |
| V2.7.0 | الحلول وأدلة المشاريع |
| V2.8.0 | مركز المعرفة والمقالات |
| V2.9.0 | الأدوات وتجربة العميل |
| V3.0.0 | التدقيق والاعتماد والنشر |

لا تُعتمد مرحلة قبل نجاح البناء والروابط واختبارات المتصفح ومطابقة revision.txt. الصور الأصلية لا تستبدل، ولا تضاف ادعاءات أو أسعار غير موثقة.

### V2.8 content counting
The export logs published page/post routes separately from category archives, guide pagination and search. The target 178 is editorial, not raw export routes; preserving existing useful pages takes precedence over deleting pages to match the target. The knowledge center preserves 16 existing specialist article URLs and adds 48 unique articles under 8 editorial topics, while retaining specialty categories.
