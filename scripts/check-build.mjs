// ตรวจว่าไฟล์ใน public/build (ผลจาก "vite build") ตรงกับที่ commit ไว้ใน git
// เซิร์ฟเวอร์ production ไม่มี Node จึงใช้ไฟล์ที่ build แล้วจาก git — ถ้าลืม commit จะได้ CSS/JS เก่า
//
// ใช้: npm run check:build   (สั่ง vite build แล้วรันสคริปต์นี้)
import { execFileSync } from 'node:child_process';

const output = execFileSync('git', ['status', '--porcelain', '--', 'public/build'], { encoding: 'utf8' }).trim();

if (output !== '') {
    console.error('\n[check:build] ไฟล์ใน public/build ยังไม่ได้ commit (หรือไม่ตรงกับที่ commit ไว้):\n');
    console.error(output);
    console.error('\nรัน:  git add public/build   แล้ว commit ไปพร้อมกับโค้ด ก่อน push\n');
    process.exit(1);
}

console.log('[check:build] OK — public/build ตรงกับที่ commit ไว้');
