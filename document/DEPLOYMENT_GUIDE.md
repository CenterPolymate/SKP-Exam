# คู่มือการติดตั้งและนำระบบขึ้นเซิร์ฟเวอร์จริง (Deployment & Production Guide)
## ระบบรับสมัครงานและทดสอบออนไลน์ SKP (SKP-Exam)

---

## 1. ข้อกำหนดสภาพแวดล้อมฝั่งเซิร์ฟเวอร์ (Server Prerequisites)

ระบบนี้พัฒนาด้วย **Laravel 13** จึงมีข้อกำหนดขั้นต่ำสำหรับเซิร์ฟเวอร์ Production ดังนี้:

- **ระบบปฏิบัติการ (OS)**: Ubuntu 22.04 LTS / 24.04 LTS หรือ Rocky Linux 9 / AlmaLinux 9 หรือ Windows Server
- **เว็บเซิร์ฟเวอร์ (Web Server)**: Nginx (แนะนำ) หรือ Apache 2.4
- **ภาษาและรันไทม์ (Runtime)**: **PHP >= 8.4.1**
  - ส่วนขยาย PHP (Extensions) ที่ต้องเปิดใช้งาน:
    - `bcmath`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- **ระบบจัดการฐานข้อมูล (Database)**: MySQL 8.0+ หรือ MariaDB 10.6+
- **เครื่องมือจัดการแพ็กเกจ (Package Managers)**:
  - **Composer 2.x**
  - **Node.js 20.x LTS** และ **npm** — ใช้บน **เครื่อง dev** เพื่อ build CSS/JS เท่านั้น **ไม่จำเป็นบนเซิร์ฟเวอร์ production** (ไฟล์ที่ build แล้วมากับ git)
- **ใบรับรองความปลอดภัย (SSL/TLS)**: บังคับใช้ HTTPS (เช่น Let's Encrypt Certbot) เนื่องจากระบบเกี่ยวข้องกับรหัสผ่านและเอกสารส่วนบุคคลของผู้สมัคร

---

## 2. ขั้นตอนการนำระบบขึ้นเซิร์ฟเวอร์ (Deployment Steps)

### 2.1 ดาวน์โหลดโปรเจกต์ (Clone Source Code)
```bash
cd /var/www
git clone https://github.com/CenterPolymate/SKP-Exam.git skp-exam
cd /var/www/skp-exam
```

### 2.2 ติดตั้ง Dependencies ทั้งหมด
```bash
# ติดตั้ง PHP Dependencies (ไม่รวมชุด Dev และทำ Autoload ให้เหมาะสมกับ Production)
composer install --no-dev --optimize-autoloader

# Frontend Assets (CSS/JS) ถูก build บนเครื่อง dev แล้วเก็บใน git (โฟลเดอร์ public/build)
# ไม่ต้องรัน npm บนเซิร์ฟเวอร์ — ดูกติกาในหัวข้อ 5.0–5.1
```

### 2.3 ตั้งค่าคอนฟิกสภาพแวดล้อม (`.env`)
```bash
cp .env.example .env
nano .env
```
ปรับแต่งค่าตัวแปรสำคัญใน `.env`:
```ini
APP_NAME="SKP-Exam"
APP_ENV=production
APP_KEY=                        # จะถูกสร้างในขั้นตอนถัดไป
APP_DEBUG=false                 # ห้ามเปิดเป็น true ใน Production เด็ดขาด
APP_URL=https://exam.grandsk.com # URL จริงของระบบ

# ฐานข้อมูล Production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skp_exam_prod
DB_USERNAME=skp_user
DB_PASSWORD=your_strong_password

# การตั้งค่า Session & Queue
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
```

สร้าง Application Key:
```bash
php artisan key:generate --force
```

### 2.4 ตั้งค่าสิทธิ์โฟลเดอร์และการจัดเก็บไฟล์ (Critical Storage Permissions)
ระบบจัดเก็บเอกสารประจำตัวผู้สมัคร (เช่น บัตรประชาชน, เรซูเม) ไว้ใน `storage/app/private/` ซึ่ง Web Server ต้องมีสิทธิ์เขียน-อ่านอย่างถูกต้อง:

```bash
# กำหนดสิทธิ์ให้เว็บเซิร์ฟเวอร์ (สมมติใช้ www-data)
sudo chown -R www-data:www-data /var/www/skp-exam
# โฟลเดอร์ 775 / ไฟล์ 664 (ห้ามใช้ chmod -R 775 กับทั้งโฟลเดอร์: จะทำให้ไฟล์ทุกไฟล์มี execute bit
# และ git แสดงไฟล์ใน storage/ และ bootstrap/cache/ เป็น "modified" เพราะ mode เปลี่ยน 100644 → 100755)
sudo find /var/www/skp-exam/storage /var/www/skp-exam/bootstrap/cache -type d -exec chmod 775 {} \;
sudo find /var/www/skp-exam/storage /var/www/skp-exam/bootstrap/cache -type f -exec chmod 664 {} \;

# ให้ git ไม่นับการเปลี่ยน file mode บนเซิร์ฟเวอร์นี้ (deploy.sh ตั้งค่านี้ให้อัตโนมัติ)
git -C /var/www/skp-exam config core.fileMode false

# สร้างและตรวจสอบความพร้อมของ Private Storage
mkdir -p /var/www/skp-exam/storage/app/private
sudo chown -R www-data:www-data /var/www/skp-exam/storage/app/private
```

### 2.5 รัน Database Migration และ Seeder
```bash
# รันการสร้างตารางและใส่ข้อมูลเริ่มต้น (Admin และ ตำแหน่งงาน)
php artisan migrate --force --seed
```

### 2.6 เพิ่มประสิทธิภาพความเร็วด้วย Cache (Production Optimizations)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 3. ตัวอย่างการตั้งค่าเว็บเซิร์ฟเวอร์ (Web Server Configuration)

### 3.1 ตัวอย่าง Nginx Server Block (`/etc/nginx/sites-available/skp-exam.conf`)
> **สำคัญมาก**: `root` ต้องชี้ไปที่โฟลเดอร์ `/public` เท่านั้น เพื่อไม่ให้ไฟล์ระบบภายนอกถูกเข้าถึงได้โดยตรง

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name exam.grandsk.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name exam.grandsk.com;

    root /var/www/skp-exam/public;
    index index.php index.html;

    # SSL Certificates (ตัวอย่าง Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/exam.grandsk.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/exam.grandsk.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # กำหนดขนาดไฟล์สูงสุดที่อนุญาตให้อัปโหลด (สำหรับเอกสารแนบผู้สมัคร)
    client_max_body_size 25M;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # ปิดการเข้าถึงไฟล์ซ่อน (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. การตั้งค่า Cron Job (Task Scheduling)

ในระบบ Laravel มี Task Scheduler ที่คอยตรวจสอบและดูแลระบบ เช่น การตัดสิทธิ์ข้อสอบที่หมดเวลา ให้เพิ่มคำสั่งนี้ใน Crontab ของผู้ใช้ `www-data`:

```bash
sudo crontab -u www-data -e
```
เพิ่มบรรทัดนี้ลงไป:
```bash
* * * * * cd /var/www/skp-exam && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. ขั้นตอนการอัปเดตโค้ดเมื่อมีเวอร์ชันใหม่ (Deploy)

ใช้สคริปต์ [`deploy.sh`](../deploy.sh) ทุกครั้ง **ห้ามพิมพ์คำสั่งทีละบรรทัดเอง** เพราะการข้ามขั้นตอนเป็นต้นเหตุของปัญหาที่เคยเจอ
(route cache เก่า → `Route [...] not defined`, ลืม build/commit ไฟล์ frontend → CSS เก่า หน้าเว็บเพี้ยน, ไม่ได้ migrate → ฐานข้อมูลไม่ตรงกับโค้ด)

### 5.0 สรุปการอัปเดตตามปกติ (อ่านส่วนนี้ส่วนเดียวก็พอ)
1. **เครื่อง dev:** commit และ push ตามปกติ
   - ถ้าแก้หน้าเว็บหรือสไตล์ (view, Tailwind, JS) ให้รัน `npm run check:build` และ `git add public/build` ก่อน commit
2. **เซิร์ฟเวอร์:** `git pull --ff-only origin main` เท่านั้น (เมื่อตั้ง hook ตามหัวข้อ 5.3.1 แล้ว `deploy.sh` จะรันต่อให้อัตโนมัติ)
   - ถ้ายังไม่ได้ตั้ง hook ให้รัน `./deploy.sh` ต่อจาก `git pull`

### 5.0.1 ฝั่งเครื่อง dev ก่อน push (ทุกครั้งที่แก้หน้าเว็บ/สไตล์) (ทุกครั้งที่แก้หน้าเว็บ/สไตล์)
เซิร์ฟเวอร์ไม่มี Node จึงไม่สามารถ build CSS/JS เองได้ ต้อง build บนเครื่อง dev แล้ว commit ไฟล์ใน `public/build/` ไปกับโค้ด:
```bash
npm run check:build      # build ใหม่ แล้วตรวจว่าไฟล์ใน public/build ตรงกับที่ commit ไว้
git add public/build     # ถ้า check:build แจ้งว่ามีไฟล์ build เปลี่ยน/เพิ่ม
git commit               # ใส่ไปพร้อมกับ commit ของฟีเจอร์ (หรือแยก commit "build assets")
git push origin main
```
ถ้าลืมขั้นตอนนี้ เซิร์ฟเวอร์จะได้ CSS เก่า และหน้าที่ใช้คลาสสไตล์ใหม่จะเพี้ยน (เคยเกิดกับ modal นโยบาย PDPA)

### 5.1 กติกา
1. **ห้ามแก้ไฟล์โค้ดบนเซิร์ฟเวอร์** แก้ที่เครื่อง dev แล้ว push เท่านั้น ค่าตั้งค่าเฉพาะเซิร์ฟเวอร์อยู่ใน `.env` (git ไม่ยุ่งกับไฟล์นี้)
2. ไฟล์ใน `public/build/` (CSS/JS) **ถูก build บนเครื่อง dev และเก็บใน git** ห้าม build หรือแก้ไฟล์เหล่านี้บนเซิร์ฟเวอร์ (จะทำให้ `git pull` ชน) ดูขั้นตอนในหัวข้อ 5.0
3. **สำรองฐานข้อมูลก่อน deploy ทุกครั้งที่มี migration ใหม่** (เช่น `mysqldump skp_exam_prod > backup-$(date +%F).sql`)
4. Deploy นอกเวลาทำการหรือช่วงที่มีผู้สอบน้อย เพราะเว็บจะถูกปิดปรับปรุงชั่วคราว

### 5.2 วิธีรัน
```bash
cd /var/www/skp-exam      # โฟลเดอร์ติดตั้งจริงของคุณ
./deploy.sh
```
ปรับค่าได้ด้วยตัวแปร เช่น `PHP_FPM_SERVICE=php8.3-fpm ./deploy.sh` (ดูรายการในหัวไฟล์ `deploy.sh`)

สคริปต์ทำตามลำดับ: ตรวจสถานะ git → ปิดเว็บ (`artisan down`) → `git pull --ff-only` → `composer install` → ตรวจว่าไฟล์ใน `public/build` ครบ
→ `migrate --force` → `optimize:clear` + `optimize` → reload PHP-FPM → เปิดเว็บ (`artisan up`)

- `migrate --force` เพิ่ม/แก้โครงสร้างตามไฟล์ migration ใหม่เท่านั้น ไม่รัน `migrate:fresh` และไม่ลบข้อมูล
- ถ้ามีขั้นตอนล้ม สคริปต์จะ **คงโหมดปิดปรับปรุงไว้** และแจ้งสาเหตุ ให้แก้ปัญหาแล้วรัน `./deploy.sh` ซ้ำ (รันซ้ำได้ทุกขั้นตอน)
  หรือเปิดเว็บกลับเองด้วย `php artisan up` หากต้องการ

### 5.3 ตั้งค่าครั้งแรก
```bash
chmod +x deploy.sh        # ครั้งเดียว ถ้าไฟล์ยังไม่มีสิทธิ์รัน
git config --global --add safe.directory /var/www/skp-exam   # ถ้า git แจ้ง "dubious ownership"
```
ผู้ที่รันสคริปต์ต้องเขียนไฟล์ในโปรเจกต์ได้ และสั่ง `sudo systemctl reload <php-fpm>` ได้ (ถ้าไม่ได้ สคริปต์จะเตือนให้รันเอง)

### 5.3.1 ให้ `git pull` อย่างเดียวรัน deploy ต่อเอง (ตั้งครั้งเดียวต่อเซิร์ฟเวอร์)
สร้าง git hook `post-merge` บนเซิร์ฟเวอร์ (ไฟล์อยู่ใน `.git/hooks` ไม่ถูกนับเป็นโค้ดโปรเจกต์ และไม่อยู่ใน git จึงต้องตั้งซ้ำเมื่อย้ายเซิร์ฟเวอร์หรือ clone ใหม่):
```bash
cd <โฟลเดอร์โปรเจกต์>
cat > .git/hooks/post-merge <<'EOF'
#!/usr/bin/env bash
cd "$(git rev-parse --show-toplevel)"
PHP_FPM_SERVICE="" ./deploy.sh
EOF
chmod +x .git/hooks/post-merge
```
- ตั้ง `PHP_FPM_SERVICE=""` เพื่อข้ามการ reload PHP-FPM (ต้องใช้รหัสผ่าน sudo ซึ่งอาจค้างใน hook) ถ้าอัปเดตแล้วยังเห็นของเก่า ให้รัน `sudo systemctl reload php8.4-fpm` เอง
- ตรวจว่าตั้งแล้ว: `ls -l .git/hooks/post-merge` ต้องมีสิทธิ์ `x`
- ถ้า deploy ล้มกลางทาง เว็บจะค้างในโหมดปิดปรับปรุง แก้ตามข้อความแล้วรัน `./deploy.sh` ซ้ำ หรือ `php artisan up`

### 5.4 ครั้งแรกบนเซิร์ฟเวอร์ที่ยังไม่มี `deploy.sh`
เซิร์ฟเวอร์เก่ายังไม่มีไฟล์ `deploy.sh` ให้ทำด้วยมือครั้งเดียว แล้วต่อไปใช้สคริปต์:
```bash
cd <โฟลเดอร์โปรเจกต์>
git config core.fileMode false          # ไม่นับการเปลี่ยนสิทธิ์ไฟล์ (ดูหัวข้อ 5.6)
php artisan down --secret="skp-maintenance-bypass"
git pull --ff-only origin main          # รับโค้ด + ไฟล์ public/build ใหม่ + deploy.sh
chmod +x deploy.sh
./deploy.sh
```
ถ้า `git pull` แจ้งว่ามีไฟล์ถูกแก้ ให้ดูว่าเป็นไฟล์อะไร: ถ้าเป็นไฟล์ใน `public/build/` ที่ใครเคย build บนเซิร์ฟเวอร์ ให้ `git checkout -- public/build` (ปลอดภัย ไฟล์ที่ถูกต้องจะมากับ pull)

### 5.5 ตรวจหลัง deploy
```bash
git log -1 --format='%h %s'                              # ต้องตรงกับ commit ล่าสุดบน GitHub
grep '"file"' public/build/manifest.json                  # ชื่อไฟล์ CSS ต้องเป็นชื่อใหม่ ไม่ซ้ำกับครั้งก่อน
php artisan route:list --name=application.consent         # ต้องเห็น route (ตัวอย่างการตรวจ cache เก่า)
```
แล้วเปิดเว็บด้วยเบราว์เซอร์ กด Ctrl+F5 ทดสอบหน้าแรก (กดสมัครงาน → modal นโยบาย) และหน้าที่แก้ไขในรอบนั้น

### 5.6 `git status` บนเซิร์ฟเวอร์ขึ้น Modified / Untracked — ต้องทำอะไร
**ไฟล์ที่ปรากฏบ่อยและเป็นเรื่องปกติ ไม่เกี่ยวกับ HTTP 500:**

| ที่เห็นใน `git status` | ความหมาย | วิธีจัดการ |
|---|---|---|
| `modified: bootstrap/cache/.gitignore`, `storage/**/.gitignore`, `storage/fonts/Sarabun-*.ttf` พร้อม `old mode 100644 / new mode 100755` | **เปลี่ยนเฉพาะสิทธิ์ไฟล์** (execute bit) เนื้อหาเหมือนเดิม มักเกิดจาก `chmod -R 775` | ตั้ง `git config core.fileMode false` (บนเซิร์ฟเวอร์เท่านั้น) และใช้ `find ... -type f -exec chmod 664` แทน `chmod -R 775` (ดูหัวข้อ 2.4) |
| `untracked: public/vendor/` | ไฟล์ asset ของแพ็กเกจที่สร้างจาก `php artisan vendor:publish --tag=laravel-assets` (รันอัตโนมัติหลัง `composer update`) | **ห้ามลบ** เพราะเว็บอาจเรียกใช้ ตอนนี้ถูกใส่ใน `.gitignore` แล้ว หลัง pull จะไม่ขึ้นเป็น untracked อีก |
| `storage/fonts/installed-fonts.json`, `sarabun_*` | แคชฟอนต์ที่ dompdf สร้างตอนสร้าง PDF ครั้งแรก | อยู่ใน `.gitignore` แล้ว ไม่ต้องทำอะไร |

**ไฟล์ที่ต้องอยู่บนเซิร์ฟเวอร์และอยู่ใน git:** `storage/fonts/Sarabun-Regular.ttf` และ `Sarabun-Bold.ttf` (ฟอนต์ภาษาไทยของ PDF ใบสมัคร อ้างจาก `resources/views/pdf/application.blade.php`) ถ้าหายหรือเสีย PDF จะแสดงภาษาไทยไม่ได้ กู้คืนด้วย `git checkout -- storage/fonts/Sarabun-Regular.ttf storage/fonts/Sarabun-Bold.ttf`

**วิธีตรวจว่า "modified" เป็นเรื่อง mode หรือเนื้อหา:**
```bash
git diff --summary            # แสดงเฉพาะ "mode change 100644 => 100755" = เปลี่ยนแค่สิทธิ์ ไม่ต้องกังวล
git -c core.fileMode=false status --short   # ถ้าว่างเปล่า = ไม่มีการเปลี่ยนเนื้อหาจริง
git diff --stat               # ถ้ามีจำนวนบรรทัดที่เปลี่ยน = เนื้อหาถูกแก้จริง ต้องสอบถามว่าใครแก้และทำไม
```
สิ่งที่ **ไม่ควรทำ**: `git reset --hard`, `git clean -fd`, `chmod -R 777`, ลบไฟล์หรือเปลี่ยนสิทธิ์แบบเหมารวม

### 5.7 ย้อนกลับเมื่อพบปัญหาหลัง deploy
```bash
git log --oneline -5              # หา commit ก่อนหน้าที่ใช้งานได้
git checkout <commit-เดิม>         # ย้อนโค้ด (อยู่ในโหมด detached HEAD ชั่วคราว)
php artisan optimize:clear && php artisan optimize
sudo systemctl reload php8.4-fpm
```
migration ที่เพิ่มคอลัมน์ใหม่ (nullable) ไม่ต้องย้อนก็ได้ โค้ดเก่าทำงานต่อได้ ถ้าต้องย้อนฐานข้อมูลจริง ให้ใช้ไฟล์สำรองที่ทำไว้ก่อน deploy
เมื่อแก้ปัญหาเสร็จให้กลับมาที่ `main`: `git checkout main`

---

## 6. แนวทางแก้ไขปัญหาเบื้องต้น (Troubleshooting)

| อาการ | สาเหตุที่เป็นไปได้ | แนวทางแก้ไข |
| :--- | :--- | :--- |
| **Error 500 (White Screen)** | ยังไม่ได้ตั้งสิทธิ์โฟลเดอร์ `storage` หรือ `.env` มีปัญหา | ตรวจสอบ `chown -R www-data:www-data storage bootstrap/cache` และดู Log ใน `storage/logs/laravel.log` |
| **ผู้สมัครอัปโหลดเอกสารไม่ได้** | ขีดจำกัดไฟล์ใน `php.ini` หรือ `nginx.conf` ต่ำเกินไป | ตรวจสอบ `upload_max_filesize = 25M` และ `post_max_size = 25M` ใน `php.ini` รวมถึง `client_max_body_size 25M;` ใน Nginx |
| **เข้าหน้าเว็บแล้วขึ้น CSS ไม่ครบ** | ไฟล์ build ใน git ไม่ตรงกับโค้ดล่าสุด (ลืม build/commit `public/build` ก่อน push) | ที่เครื่อง dev รัน `npm run check:build`, `git add public/build`, commit และ push แล้ว pull/deploy ใหม่ |
| **`Route [...] not defined` หลัง deploy** | route cache เก่า (`bootstrap/cache/routes-v7.php`) ที่สร้างก่อน pull | รัน `php artisan optimize:clear && php artisan optimize` แล้ว reload PHP-FPM (หรือรัน `./deploy.sh` ซ้ำ) |
| **หน้าเว็บ/modal เพี้ยนหลัง deploy (CSS เก่า)** | เหมือนข้างบน: ไฟล์ใน `public/build` ที่ commit ไว้เป็นเวอร์ชันเก่า | ตรวจ `grep '"file"' public/build/manifest.json` เทียบกับเครื่อง dev แล้วทำตามแถวข้างบน |
| **`git status` ขึ้น modified เป็นกลุ่มไฟล์ใน storage/ และ bootstrap/cache/** | file mode เปลี่ยน (execute bit) จาก `chmod -R 775` | ดูหัวข้อ 5.6: ตั้ง `git config core.fileMode false` ไม่ต้อง reset หรือ chmod เหมารวม |
| **`git pull` แจ้ง local changes would be overwritten** | มีคนแก้ไฟล์บนเซิร์ฟเวอร์ | ดู `git status` ถ้าเป็นไฟล์ใน `public/build/` รัน `git checkout -- public/build` ถ้าเป็นไฟล์โค้ด ให้ส่งการแก้ไขนั้นกลับไปที่เครื่อง dev อย่าแก้บนเซิร์ฟเวอร์ |
| **Composer แจ้งเตือน PHP version** | เซิร์ฟเวอร์ยังใช้งาน PHP ต่ำกว่า 8.4 | ตรวจสอบ `php -v` และใช้คำสั่งชี้ไปที่ binary PHP 8.4 โดยตรง |
