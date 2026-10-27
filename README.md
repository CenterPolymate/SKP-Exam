# SKP-Exam: ระบบรับสมัครงานและทดสอบออนไลน์ (Recruitment & Online Examination System)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)

ระบบเว็บแอปพลิเคชันบริหารจัดการกระบวนการรับสมัครงานและสอบคัดเลือกออนไลน์แบบครบวงจรสำหรับเครือ **Grand SK / SKP** พัฒนาขึ้นเพื่อทดแทนกระบวนการกรอกเอกสารกระดาษตามแบบฟอร์ม **FS-A21-001 (E) ใบสมัครงาน (สำหรับพนักงานรายเดือน)** พร้อมระบบสอบออนไลน์ที่มีกลไกตรวจจับการทุจริตและการสลับหน้าจอ

---

## 📑 สารบัญเอกสารสำคัญของโปรเจกต์ (Project Documentation)

เอกสารทางเทคนิคและคู่มือการใช้งานฉบับเต็มได้รับการจัดเก็บไว้ในโฟลเดอร์ [`document/`](file:///document):

1. **[คู่มือการใช้งานระบบ (User Manual)](file:///document/USER_MANUAL.md)**
   - ขั้นตอนการสมัครงานและการแนบเอกสารสำหรับผู้สมัคร
   - วิธีการเข้าสอบออนไลน์ กฎระเบียบ และระบบตรวจจับพฤติกรรมน่าสงสัย
   - คู่มือการทำงานของ HR: การคัดกรอง, การออกรหัสสอบ, การสร้างข้อสอบ, การตรวจคะแนน และการดูรายงาน
2. **[ข้อกำหนดสถาปัตยกรรมระบบ (System Specification)](file:///document/SYSTEM_SPECIFICATION.md)**
   - วัตถุประสงค์และสถาปัตยกรรมเชิงลึก (Architecture & Tech Stack)
   - Multi-Guard Authentication (`admin` และ `exam_applicant`)
   - Anti-Cheating & Suspicious Activity Monitoring Engine
   - Secure Private File Storage สำหรับเอกสารสำคัญ
3. **[พจนานุกรมและโครงสร้างฐานข้อมูล (Database Schema & Dictionary)](file:///document/DATABASE_SCHEMA.md)**
   - แผนภาพความสัมพันธ์ของตาราง (ER Diagram)
   - รายละเอียดฟิลด์ ชนิดข้อมูล ความสัมพันธ์ และข้อจำกัดของทุกตาราง (Data Dictionary)

---

## 🚀 ฟีเจอร์หลัก (Key Features)

- **Online Job Application (`/apply`)**:
  - กรอกประวัติครบถ้วนตามแบบฟอร์มมาตรฐาน FS-A21-001 (E)
  - แนบไฟล์เอกสารสำคัญ (บัตรประชาชน, สำเนาทะเบียนบ้าน, วุฒิการศึกษา, Resume)
  - ระบบตรวจสอบความยินยอม PDPA
- **HR Screening & Verification**:
  - ตรวจสอบประวัติอาชญากรรม, Blacklist, Social media background check
  - บันทึกการสัมภาษณ์และปรับสถานะผู้สมัคร (Qualified / Disqualified)
- **Exam Management & Assignment**:
  - สร้างชุดข้อสอบแยกตามตำแหน่งงาน (Single Choice, Multiple Choice, Essay)
  - กำหนดเวลาทำข้อสอบ (Timer) และเกณฑ์ผ่าน (% Pass Score)
  - ระบบสร้างบัญชีผู้สอบและรหัสผ่านสุ่มอัตโนมัติพร้อมกำหนดวันหมดอายุ (Exam Deadline)
- **Anti-Cheating Monitoring**:
  - ตรวจจับการสลับแท็บเบราว์เซอร์ ย่อหน้าต่าง หรือออกจากโหมด Fullscreen
  - บันทึกจำนวนครั้งและประวัติเหตุการณ์ (Suspicious Activity Log) ส่งให้ HR ตรวจสอบ
- **Grading & Comprehensive Summary**:
  - ตรวจข้อสอบปรนัยอัตโนมัติ พร้อมระบบตรวจและให้คะแนนข้อสอบเขียนอัตนัย
  - หน้า Dashboard และ Summary แสดงสถิติและผลคะแนนภาพรวม

---

## 🛠 ความต้องการของระบบ (System Requirements)

- **PHP**: `>= 8.4.1` (ต้องการ Extensions: `curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, `zip`)
- **Composer**: `>= 2.7`
- **Node.js**: `>= 20.x` และ **npm**
- **Database**: MySQL 8.0+ หรือ SQLite 3

---

## 💻 การติดตั้งและเริ่มต้นใช้งาน (Installation & Setup)

### 1. Clone โปรเจกต์
```bash
git clone https://github.com/CenterPolymate/SKP-Exam.git
cd SKP-Exam
```

### 2. ติดตั้ง Dependencies ฝั่ง PHP และ Node.js
```bash
composer install
npm install
```

### 3. ตั้งค่าสภาพแวดล้อม (.env)
คัดลอกไฟล์ตัวอย่างและสร้าง Application Key:
```bash
cp .env.example .env
php artisan key:generate
```
*(กำหนดค่าการเชื่อมต่อฐานข้อมูล `DB_CONNECTION`, `DB_DATABASE` ในไฟล์ `.env` ตามต้องการ)*

### 4. รัน Database Migration และ Seeder
สร้างโครงสร้างตารางและข้อมูลเริ่มต้น (Admin เริ่มต้น และรายชื่อตำแหน่งงาน):
```bash
php artisan migrate --seed
```

### 5. Build ทรัพยากรสำหรับ Frontend
```bash
npm run build
```

### 6. เริ่มต้นรันเซิร์ฟเวอร์สำหรับพัฒนา (Development Server)
```bash
php artisan serve
```
หรือรันพร้อมกับ Vite Hot-Reloading:
```bash
composer run dev
```

---

## 🔑 ข้อมูลเข้าสู่ระบบเริ่มต้น (Default Credentials)

| บทบาท | URL เข้าใช้งาน | บัญชีผู้ใช้ (Username/Email) | รหัสผ่าน (Password) |
| :--- | :--- | :--- | :--- |
| **HR / ผู้ดูแลระบบ (Admin)** | `/login` | `admin` หรือ `admin@grandsk.com` | `Admin@1234` |
| **ผู้เข้าสอบ (Applicant)** | `/exam/login` | *สร้างผ่านเมนู "ออกรหัสสอบ" โดย HR* | *รหัสผ่านสุ่มตามที่ HR กำหนด* |

---

## 🧪 การรันแบบทดสอบ (Testing)

โปรเจกต์นี้มีชุดแบบทดสอบครอบคลุมด้วย **Pest PHP**:
```bash
php artisan test
```

---

## 📄 ใบอนุญาต (License)

โปรเจกต์นี้พัฒนาและสงวนลิขสิทธิ์สำหรับใช้งานภายในองค์กร **Grand SK / SKP**.
