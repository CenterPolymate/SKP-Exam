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
  - **Node.js 20.x LTS** และ **npm**
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

# ติดตั้ง Node Dependencies และทำการคอมไพล์ Frontend Assets
npm ci
npm run build
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
sudo chmod -R 775 /var/www/skp-exam/storage
sudo chmod -R 775 /var/www/skp-exam/bootstrap/cache

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

## 5. ขั้นตอนการอัปเดตโค้ดเมื่อมีเวอร์ชันใหม่ (Zero-Downtime Deployment Flow)

เมื่อมีการปรับปรุงเวอร์ชันใหม่ ให้ดำเนินการตามลำดับดังนี้:
```bash
cd /var/www/skp-exam

# 1. เปิดโหมดปรับปรุงระบบชั่วคราว
php artisan down --secret="skp-maintenance-bypass"

# 2. ดึงโค้ดล่าสุด
git pull origin main

# 3. อัปเดต Dependencies และ Build Assets
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 4. รัน Database Migration (ถ้ามีการเปลี่ยนแปลงตาราง)
php artisan migrate --force

# 5. เคลียร์และแคชใหม่
php artisan optimize:clear
php artisan optimize

# 6. รีสตาร์ท PHP-FPM เพื่อโหลด OPcache ใหม่
sudo systemctl reload php8.4-fpm

# 7. ปิดโหมดปรับปรุงระบบ
php artisan up
```

---

## 6. แนวทางแก้ไขปัญหาเบื้องต้น (Troubleshooting)

| อาการ | สาเหตุที่เป็นไปได้ | แนวทางแก้ไข |
| :--- | :--- | :--- |
| **Error 500 (White Screen)** | ยังไม่ได้ตั้งสิทธิ์โฟลเดอร์ `storage` หรือ `.env` มีปัญหา | ตรวจสอบ `chown -R www-data:www-data storage bootstrap/cache` และดู Log ใน `storage/logs/laravel.log` |
| **ผู้สมัครอัปโหลดเอกสารไม่ได้** | ขีดจำกัดไฟล์ใน `php.ini` หรือ `nginx.conf` ต่ำเกินไป | ตรวจสอบ `upload_max_filesize = 25M` และ `post_max_size = 25M` ใน `php.ini` รวมถึง `client_max_body_size 25M;` ใน Nginx |
| **เข้าหน้าเว็บแล้วขึ้น CSS ไม่ครบ** | ยังไม่ได้สั่ง build assets | รันคำสั่ง `npm run build` |
| **Composer แจ้งเตือน PHP version** | เซิร์ฟเวอร์ยังใช้งาน PHP ต่ำกว่า 8.4 | ตรวจสอบ `php -v` และใช้คำสั่งชี้ไปที่ binary PHP 8.4 โดยตรง |
