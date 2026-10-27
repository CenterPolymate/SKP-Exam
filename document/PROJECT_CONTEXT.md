# บริบทและข้อกำหนดการพัฒนาโปรเจกต์ (Project Context & Developer Guidelines)
## สำหรับนักพัฒนาและ AI Coding Assistant (Antigravity / Cursor / Copilot)

เอกสารฉบับนี้สรุป **แก่นสาระสำคัญ (Core Context), กฎเกณฑ์ทางธุรกิจ (Business Rules), และข้อจำกัดทางเทคนิค (Technical Constraints)** เพื่อให้การต่อยอดและแก้ไขโค้ดเป็นไปในทิศทางเดียวกันอย่างถูกต้อง แม่นยำ และปลอดภัย

---

## 1. บริบทและขอบเขตของระบบ (Domain Context)

- **ชื่อระบบ**: SKP-Exam (ระบบรับสมัครงานและทดสอบออนไลน์เครือ Grand SK / SKP)
- **วัตถุประสงค์**:
  1. แปลงแบบฟอร์มกระดาษมาตรฐาน **FS-A21-001 (E) ใบสมัครงาน (สำหรับพนักงานรายเดือน)** ให้เป็นระบบดิจิทัลแบบ 100%
  2. จัดการกระบวนการคัดกรองคุณสมบัติของฝ่ายบุคคล (HR Background & Security Check)
  3. ระบบสอบออนไลน์ตามตำแหน่งงาน พร้อมกลไกตรวจจับการทุจริต (Anti-Cheating Engine)
  4. ระบบตรวจข้อสอบและสรุปผลคะแนนแบบรวมศูนย์

---

## 2. ข้อจำกัดทางเทคนิคและสถาปัตยกรรม (Technical Constraints)

| หมวดหมู่ | ข้อกำหนดบังคับ | ข้อควรระวัง (Do's & Don'ts) |
| :--- | :--- | :--- |
| **PHP Version** | **PHP >= 8.4.1** | **ห้ามลดเวอร์ชันต่ำกว่า 8.4** เนื่องจาก Laravel 13 และ Symfony 8.x บังคับใช้คุณสมบัติของ PHP 8.4 |
| **Backend Framework** | **Laravel 13.x** | ใช้ไวยากรณ์สมัยใหม่ เช่น Anonymous Migrations, Invokable Controller, Method casts() บน Eloquent |
| **UI Components** | **Livewire 3 + Volt** | **ใช้ Volt Single File Components (SFC)** ใน `resources/views/livewire/pages/` เป็นหลัก **ห้ามสร้าง Controller ธรรมดาสำหรับเรนเดอร์หน้าเว็บ** ยกเว้นงานเฉพาะทาง เช่น Stream ไฟล์ หรือ Web API |
| **Frontend Utilities** | **Alpine.js 3** | ใช้งานสำหรับการควบคุม DOM หน้าบ้าน เช่น ตัวนับเวลาถอยหลัง (Timer) และการตรวจจับเหตุการณ์สลับหน้าจอ |
| **CSS & Styling** | **Tailwind CSS 3** | ใช้ Utility Classes ของ Tailwind ในการจัดรูปแบบหน้าตาเว็บ |
| **Automated Test** | **Pest PHP 5.x** | เขียนชุดทดสอบด้วย Pest (`tests/Feature/`) รันผ่านคำสั่ง `php artisan test` |

---

## 3. ระบบยืนยันตัวตนและสิทธิ์ (Authentication & Guard Rules)

ระบบใช้ **Multi-Guard Architecture** แยกขาดจากกันโดยสิ้นเชิง:

```text
[HTTP Requests]
   ├── /admin/*  ---> Guard: 'web'             ---> Model: App\Models\User
   │                  Middleware: ['auth', 'verified', 'admin']
   │
   └── /exam/*   ---> Guard: 'exam_applicant'  ---> Model: App\Models\ExamAssignment
                      Middleware: ['auth:exam_applicant', 'exam.not_expired']
```

- **Admin Account (`users`)**:
  - เก็บ Password เป็น Bcrypt Hash ปกติ (`Hash::make(...)`)
  - มีฟิลด์ `role = 'admin'` สำหรับตรวจสอบสิทธิ์ผู้ดูแลระบบ
- **Applicant Exam Account (`exam_assignments`)**:
  - เข้ารหัส Password สองทางด้วย `password_encrypted` ผ่าน `Crypt::encryptString()` / `Crypt::decryptString()` เพื่อให้เจ้าหน้าที่ HR สามารถคัดลอกรหัสผ่านส่งต่อให้ผู้สมัครได้
  - วันหมดอายุสอบ (`exam_deadline`) ถูกกำกับโดย Middleware `exam.not_expired` หากเกินกำหนด ผู้สมัครจะเข้าสอบไม่ได้ และสถานะจะถูกเปลี่ยนเป็น `failed_test` อัตโนมัติ

---

## 4. สถานะและวงจรชีวิตของผู้สมัคร (Applicant State Machine)

สถานะของผู้สมัคร (`applicants.status`) มี 8 สถานะ และต้องเปลี่ยนผ่านตามลำดับดังนี้:

```mermaid
graph LR
    draft[1. draft] --> submitted[2. submitted]
    submitted --> qualified[3. qualified]
    submitted --> disqualified[4. disqualified]
    qualified --> code_generated[5. code_generated]
    code_generated --> awaiting_review[6. awaiting_review]
    code_generated --> failed_expired[7. failed_test (หมดเวลา)]
    awaiting_review --> passed[8. passed_test]
    awaiting_review --> failed[7. failed_test]
```

1. **`draft`**: กำลังกรอกข้อมูลใบสมัครออนไลน์
2. **`submitted`**: กดยืนยันส่งใบสมัครแล้ว (เข้าสู่คิวรอ HR คัดกรอง)
3. **`qualified`**: HR ตรวจประวัติและอนุมัติคุณสมบัติเบื้องต้น
4. **`disqualified`**: คุณสมบัติไม่ผ่านเกณฑ์ (สิ้นสุดกระบวนการ)
5. **`code_generated`**: HR ออกรหัสเข้าสอบและกำหนด Deadline ให้ผู้สมัคร
6. **`awaiting_review`**: ผู้สมัครทำข้อสอบเสร็จและส่งเรียบร้อยแล้ว รอ HR เข้าตรวจข้อเขียน
7. **`passed_test`**: ผ่านการทดสอบคัดเลือก
8. **`failed_test`**: ไม่ผ่านการทดสอบ หรือไม่เข้าสอบภายในเวลาที่กำหนด

---

## 5. กลไกตรวจจับพฤติกรรมน่าสงสัย (Anti-Cheating Rules)

- **การตรวจจับ**: ดักจับ Event `visibilitychange` (เมื่อผู้สอบสลับแท็บ/ย่อหน้าต่าง) และ `fullscreenchange` (เมื่อออกจากโหมดเต็มหน้าจอ)
- **การบันทึก**: เรียก POST ไปยัง `/exam/attempts/{attempt}/suspicious-activity` เพื่อบันทึก:
  - `suspicious_activity_count`: เพิ่มตัวนับสะสม
  - `suspicious_activity_log`: บันทึก Array ข้อมูล `[{"type": "visibility_change", "at": "2026-10-27T10:00:00Z"}]`
- **ข้อห้าม**: ห้ามลบ ปิดกั้น หรือเปลี่ยนแปลง endpoint นี้โดยไม่มีมาตรการรองรับ เพราะส่งผลต่อความโปร่งใสในการสอบ

---

## 6. ความปลอดภัยและการจัดเก็บไฟล์ (Secure Storage Rules)

1. **ไฟล์เอกสารส่วนบุคคล (ID Card, ทรานสคริปต์, ทะเบียนบ้าน, เรซูเม)** และ **ไฟล์คำตอบข้อสอบ**:
   - **ต้องจัดเก็บไว้ใน `storage/app/private/` เท่านั้น**
   - **ห้าม** ย้ายไปไว้ในโฟลเดอร์ `public/` หรือ `storage/app/public/` โดยเด็ดขาด เพื่อป้องกันการเข้าถึงจากบุคคลภายนอกโดยไม่ผ่านการยืนยันตัวตน
2. **การเรียกดูไฟล์**:
   - ต้องผ่าน `SecureFileController` เท่านั้น ซึ่งจะตรวจสอบ Session สิทธิ์ `admin` หรือ `exam_applicant` ก่อนส่งไฟล์ (Stream response)

---

## 7. กฎเหล็กสำหรับนักพัฒนาและ AI Coding (Developer Dos & Don'ts)

### ✅ สิ่งที่ควรทำ (DO):
- ใช้คำสั่ง `composer run dev` หรือ `php artisan serve` ควบคู่กับ `npm run dev` เมื่อพัฒนาหน้าเว็บ
- เขียนคำสั่ง Migration และ Model Relationship ให้ตรงกับ [DATABASE_SCHEMA.md](file:///c:/Users/Film%20Nutthanun/Desktop/SKP-Exam/document/DATABASE_SCHEMA.md)
- เมื่อเพิ่มหรือแก้เงื่อนไขในแบบฟอร์มสมัครงาน ให้ตรวจสอบว่าไม่กระทบกับข้อมูลฟิลด์ตามแบบฟอร์ม FS-A21-001 (E)
- รันชุดทดสอบ `php artisan test` ทุกครั้งก่อน Commit เพื่อยืนยันว่าไม่มี Test เดิมพัง

### ❌ สิ่งที่ห้ามทำ (DON'T):
- **ห้าม** แก้ไขรหัสผ่านของ `exam_assignments` เป็น One-way Hash เพราะ HR จะไม่สามารถดูรหัสผ่านเพื่อส่งให้ผู้สมัครได้
- **ห้าม** ลดระดับเวอร์ชัน PHP ใน `composer.json` จาก `^8.4` เป็นเวอร์ชันต่ำกว่า
- **ห้าม** เขียน Controller และ Blade view แบบแยกไฟล์ หากหน้านั้นสามารถสร้างเป็น Livewire Volt Component ได้
