# นโยบายคุ้มครองข้อมูลส่วนบุคคลและความปลอดภัยของระบบ (PDPA & Security Policy)
## ระบบรับสมัครงานและทดสอบออนไลน์ SKP (SKP-Exam)

---

## 1. บทนำและความสอดคล้องตามกฎหมาย PDPA

ระบบรับสมัครงานและทดสอบออนไลน์ **SKP-Exam** มีการจัดเก็บและประมวลผลข้อมูลส่วนบุคคลของผู้สมัครงานจำนวนมาก ซึ่งรวมถึง **ข้อมูลส่วนบุคคลทั่วไป** และ **ข้อมูลส่วนบุคคลที่มีความอ่อนไหวสูง (Sensitive Personal Data)** ตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA)

เอกสารฉบับนี้กำหนดมาตรการทางเทคนิค (Technical Measures) และมาตรการเชิงบริหารจัดการ (Organizational Measures) เพื่อรักษาความปลอดภัยของข้อมูลในระบบ

---

## 2. การจำแนกประเภทข้อมูลในระบบ (Data Classification)

| ลำดับ | ประเภทข้อมูล | รายการข้อมูลในระบบ | ระดับความสำคัญ | การคุ้มครองในระบบ |
| :---: | :--- | :--- | :---: | :--- |
| **1** | **ข้อมูลส่วนบุคคลทั่วไป** | ชื่อ-นามสกุล, ที่อยู่, อีเมล, เบอร์โทรศัพท์, ประวัติการศึกษา, ประวัติการทำงาน, ภาพถ่าย | **สูง (Confidential)** | เข้าถึงได้เฉพาะผู้ดูแลระบบและเจ้าหน้าที่ HR ที่ได้รับสิทธิ์ |
| **2** | **ข้อมูลส่วนบุคคลอ่อนไหว (Sensitive Data)** | เลขประจำตัวประชาชน 13 หลัก, เลขหลังบัตร (Laser ID), ประวัติสุขภาพ/โรคประจำตัว, ประวัติคดีอาญา, ประวัติคดี ม.112, ศาสนา | **สูงสุด (Strictly Confidential)** | จัดเก็บในระบบปิด ไม่เปิดเผยต่อสาธารณะ ต้องได้รับความยินยอมชัดแจ้งก่อนเก็บ |
| **3** | **เอกสารหลักฐานตัวจริง** | สำเนาบัตรประชาชน, สำเนาทะเบียนบ้าน, ใบรายงานผลการศึกษา (Transcript), เรซูเม | **สูงสุด (Strictly Confidential)** | จัดเก็บใน **Private Storage (`storage/app/private/`)** เข้าถึงผ่าน Session Token เท่านั้น |
| **4** | **ข้อมูลการทดสอบ** | คะแนนสอบ, คำตอบข้อสอบ, บันทึกพฤติกรรมน่าสงสัย (Anti-Cheating Log) | **ภายใน (Internal)** | ใช้เพื่อการประเมินผลคัดเลือกพนักงานเท่านั้น |

---

## 3. มาตรการความปลอดภัยทางเทคนิค (Technical Security Measures)

### 3.1 การจัดเก็บไฟล์แบบส่วนตัว (Private File Storage)
- **ไม่ใช้ Public Disk**: ไฟล์เอกสารสำคัญทั้งหมดจะถูกบันทึกที่ `storage/app/private/applicant_documents/` ซึ่งอยู่นอกโฟลเดอร์ Web Root (`/public`)
- **การส่งไฟล์ผ่าน Controller ปลอดภัย**: การเปิดดูไฟล์หรือดาวน์โหลดต้องผ่าน [SecureFileController](file:///app/Http/Controllers/SecureFileController.php) เท่านั้น โดยระบบจะตรวจสอบ:
  1. ผู้ใช้ต้องล็อกอินผ่าน Guard `web` (เป็น Admin) หรือ Guard `exam_applicant`
  2. ทำการสตรีมไบนารีไฟล์ตรงสู่เบราว์เซอร์ โดยไม่มีการเปิด Direct Path ให้ผู้ไม่หวังดีสแกนไฟล์ได้

### 3.2 การเข้ารหัสข้อมูล (Encryption)
- **รหัสผ่านผู้ดูแลระบบ (Admin Password)**: เข้ารหัสแบบทางเดียวด้วยอัลกอริทึม **Bcrypt** ตามมาตรฐานความปลอดภัยสากล
- **รหัสผ่านผู้สอบ (Applicant Password)**: เข้ารหัสแบบสองทางด้วย Laravel Encrypter (`AES-256-CBC`) โดยใช้ `APP_KEY` ของระบบ เพื่อให้ HR สามารถดึงรหัสผ่านส่งมอบให้ผู้สมัครได้ แต่ป้องกันไม่ให้ฐานข้อมูลถูกขโมยข้อมูลดิบ

### 3.3 การป้องกันการส่งข้อมูลซ้ำและการโจมตี (Rate Limiting & Anti-Brute Force)
- เส้นทางการสมัครงาน `/apply` มีการจำกัดอัตราการส่งข้อมูล (`throttle:30,1`) เพื่อป้องกันการยิงสแปมบอท (Spam Bot Protection)
- ฟอร์มทุกหน้ามีการป้องกัน **CSRF (Cross-Site Request Forgery)** ด้วยโทเคนของ Laravel

---

## 4. นโยบายความยินยอมและการเก็บรักษาข้อมูล (Consent & Retention Policy)

### 4.1 การขอความยินยอม (Consent Collection)
- ผู้สมัครต้องแสดงเจตนายินยอมอย่างชัดแจ้งผ่านการติ๊กเลือกช่อง:
  1. `data_accuracy_consent`: รับรองความถูกต้องของข้อมูลทั้งหมด
  2. `privacy_policy_consent`: ยินยอมให้นำข้อมูลส่วนบุคคลไปประมวลผลเพื่อการคัดเลือกเข้าทำงาน
- มีการบันทึกชื่อผู้ลงนาม (`signature_name`) และวันเวลาที่ส่งข้อมูล (`signed_at`) ไว้เป็นหลักฐานทางกฎหมาย

### 4.2 ระยะเวลาการเก็บรักษาข้อมูล (Retention Period)
- **ผู้สมัครที่ไม่ผ่านการคัดเลือก**: แนะนำให้เก็บข้อมูลไว้เป็นเวลา **1 ปี** นับจากวันที่สรุปผล เพื่อเป็นข้อมูลสำรองกรณีมีการติดต่อเรียกสัมภาษณ์ในตำแหน่งอื่น จากนั้นทำการลบข้อมูลและเอกสารแนบออกจากระบบ (Data Purge)
- **ผู้สมัครที่ผ่านการคัดเลือกและรับเข้าทำงาน**: ข้อมูลจะถูกถ่ายโอนไปยังระบบประวัติพนักงาน (HRM System) ขององค์กร

---

## 5. แผนการสำรองข้อมูลและกู้คืนระบบ (Backup & Disaster Recovery Strategy)

เพื่อให้ระบบมีความต่อเนื่องในการดำเนินงาน (Business Continuity Plan):

### 5.1 การสำรองฐานข้อมูลอัตโนมัติ (Database Backup)
ควรตั้งค่า Shell Script ผ่าน Cron Job บนเซิร์ฟเวอร์ ให้สำรองฐานข้อมูล MySQL ทุกวันเวลาเที่ยงคืน:

```bash
#!/bin/bash
# สคริปต์สำรองฐานข้อมูล skp_backup.sh
BACKUP_DIR="/var/backups/skp-exam"
DATE=$(date +"%Y%m%d_%H%M%S")
mkdir -p $BACKUP_DIR

# ดัมป์ฐานข้อมูลและบีบอัด
mysqldump -u skp_user -p'your_strong_password' skp_exam_prod | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# ลบไฟล์สำรองที่เก่าเกิน 30 วันอัตโนมัติ
find $BACKUP_DIR -name "db_*.sql.gz" -type f -mtime +30 -delete
```

### 5.2 การสำรองไฟล์เอกสารผู้สมัคร (Private Files Backup)
เอกสารใน `storage/app/private/` ควรอบบีบอัดและซิงค์ (Rsync / S3 Bucket Backup) สัปดาห์ละ 1 ครั้ง:

```bash
rsync -avz /var/www/skp-exam/storage/app/private/ /var/backups/skp-exam/private_files_backup/
```

### 5.3 ขั้นตอนการกู้คืนข้อมูล (Recovery Procedure)
กรณีเกิดเหตุฉุกเฉิน:
```bash
# กู้คืนฐานข้อมูล
gunzip < /var/backups/skp-exam/db_YYYYMMDD_HHMMSS.sql.gz | mysql -u skp_user -p'password' skp_exam_prod

# กู้คืนไฟล์แนบ
rsync -avz /var/backups/skp-exam/private_files_backup/ /var/www/skp-exam/storage/app/private/
sudo chown -R www-data:www-data /var/www/skp-exam/storage/app/private/
```
