# راهنمای آپلود روی هاست

## ۱. آپلود
تمام محتویات این زیپ را در پوشه‌ی `public_html` هاست خود آپلود کنید (نه خود پوشه — محتویاتش).
ساختار باید این‌طور شود:

```
public_html/
├── index.html
├── product.html  cart.html  checkout.html  panel.html  tracking.html
├── .htaccess     manifest.json  robots.txt  sitemap.xml
├── favicon.ico   favicon.svg    apple-touch-icon.png  favicon-32.png
└── assets/
```

> اگر `.htaccess` را نمی‌بینید، در فایل‌منیجر cPanel گزینه‌ی
> **Show Hidden Files (dotfiles)** را روشن کنید.

## ۲. سه جای «example.com» را عوض کنید
- `robots.txt` → خط `Sitemap:`
- `sitemap.xml` → همه‌ی `<loc>`ها
- برای پیش‌نمایش لینک در تلگرام و واتساپ، در `<head>` هر شش صفحه
  `content="assets/img/og.jpg"` را به آدرس کامل تغییر دهید:
  `content="https://your-domain.com/assets/img/og.jpg"`

## ۳. SSL
از cPanel گواهی رایگان **Let's Encrypt** را فعال کنید، سپس این را به بالای `.htaccess` اضافه کنید:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## ۴. فونت
فونت **Dana Variable** روی خود سایت میزبانی می‌شود (`assets/fonts/DanaVF.woff2`) —
هیچ درخواستی به گوگل زده نمی‌شود، پس برای کاربران ایرانی سریع و بدون مشکل بالا می‌آید.

اگر سرور فایل فونت را با هدر درست سرو نکرد، این را به `.htaccess` اضافه کنید:

```apache
AddType font/woff2 .woff2
AddType font/ttf   .ttf
```

> پروانه‌ی فونت دانا را برای استفاده‌ی تجاری خودتان بررسی کنید.

## ۵. نکته‌های فنی
- سایت **کاملاً استاتیک** است؛ به PHP، دیتابیس یا Node نیاز ندارد.
- سبد خرید در `localStorage` مرورگر کاربر ذخیره می‌شود (فقط شمارنده — سمت سرور نیست).
- فرم‌ها (ثبت‌نام خبرنامه، چک‌اوت، جستجو) نمایشی‌اند و به بک‌اند وصل نیستند.
- برای ویرایش منو یا آیکون‌ها، فایل‌های `assets/appshell.html` و `assets/icons.html`
  را تغییر داده و `python build.py` را اجرا کنید.
- **بعد از هر تغییر در CSS یا JS**، عدد `?v=2` را در `<head>` صفحه‌ها یک واحد زیاد کنید
  (`?v=3`) تا مرورگر کاربران نسخه‌ی جدید را بگیرد؛ وگرنه به‌خاطر کش ۷ روزه‌ی `.htaccess`
  نسخه‌ی قدیمی را می‌بینند.

## ۶. تست بعد از آپلود
- `https://your-domain.com/` باز شود
- روی موبایل: نوار پایین، منو، جستجو و افزودن به سبد کار کند
- لینک را در تلگرام بفرستید و پیش‌نمایش تصویر را ببینید
- در کروم موبایل: منو ← **Add to Home screen** (به‌خاطر manifest نصب می‌شود)
