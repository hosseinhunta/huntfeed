# HuntFeed – GitHub Push Guide

تمام فایل‌های لازم برای پوش شدن بر روی GitHub آماده است.

## مراحل نهایی:

### 1. تنظیم اطلاعات Git (اگر هنوز انجام نشده است)
```powershell
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### 2. در دایرکتوری پروژه، فایل‌های لازم را stage کنید:
```powershell
cd d:\Project\huntfeed

# تمام فایل‌های تغییر یافته را بررسی کنید
git status

# تمام فایل‌ها را اضافه کنید
git add .

# یا انتخاب‌گر فایل‌های خاص
git add composer.json .gitignore .gitattributes .github/workflows/php.yml docs/
```

### 3. یک commit محلی درست کنید:
```powershell
git commit -m "Improve: Add modern responsive docs template, CI workflow, and gitignore/gitattributes"
```

یا برای commit دقیق‌تر:
```powershell
git commit -m "docs: Add modern HuntFeed website template with responsive design

- Implement fully responsive HTML/CSS/JS template
- Add Bootstrap 5.3 integration with smooth scrolling
- Enhance typography with clamp() for mobile scaling
- Improve accessibility (focus states, semantic HTML)
- Add smooth transitions and hover effects
- Optimize layout for mobile, tablet, and desktop
- Add GitHub Actions CI workflow for PHP 8.1-8.3 testing"
```

### 4. اگر هنوز repository را initialize نکردید:
```powershell
git init
git branch -M main
```

### 5. آدرس ریموت را اضافه کنید (اگر هنوز نشده است):
```powershell
# جایگزین USERNAME و REPO-NAME را با مقادیر واقعی‌تان کنید
git remote add origin https://github.com/hosseinhunta/huntfeed.git

# یا اگر SSH استفاده می‌کنید:
git remote add origin git@github.com:hosseinhunta/huntfeed.git
```

### 6. تغییرات را push کنید:
```powershell
# برای اولین بار:
git push -u origin main

# دفعات بعدی:
git push
```

## فایل‌های آماده شده:

✅ `.github/workflows/php.yml` - CI workflow برای PHPUnit
✅ `.gitignore` - Ignore vendor/, logs, env files
✅ `.gitattributes` - Line ending normalization
✅ `docs/index.html` - Modern responsive template
✅ `docs/css/style.css` - Enhanced responsive styles with clamp()
✅ `docs/js/main.js` - Smooth scrolling & Bootstrap integration
✅ `composer.json` - Package metadata (PHP 8.1+)

## آزمایش محلی (اختیاری):

```powershell
# صحت composer.json را بررسی کنید
composer validate

# فایل‌های PHP را صحیح‌سنجی کنید
php -l src/

# یا استفاده از اپزیشن:
php -r "echo 'PHP syntax OK';"
```

## نکات مهم:

- اطمینان حاصل کنید که `composer.lock` در `.gitignore` وجود دارد (برای library)
- شاخه پیش‌فرض `main` است
- تمام commits قبل از پوش محلی ساخته شوند
- اگر خطایی در workflow رخ داد، GitHub Actions logs را بررسی کنید

هر سوالی یا نیاز به راهنمایی بیشتر دارید؟
