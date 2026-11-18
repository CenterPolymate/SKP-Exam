#!/usr/bin/env bash
#
# สคริปต์ deploy สำหรับ Production (รันบนเซิร์ฟเวอร์ในโฟลเดอร์โปรเจกต์)
#
#   ./deploy.sh
#
# ลำดับ: ตรวจสถานะ → ปิดเว็บชั่วคราว → pull → composer → ตรวจ assets → migrate → ล้าง/สร้าง cache → reload PHP-FPM → เปิดเว็บ
# ทุกขั้นตอนรันซ้ำได้ ถ้าล้มกลางทางให้แก้ปัญหาแล้วรันสคริปต์ซ้ำ
#
# ตัวแปรที่ปรับได้ (ตั้งก่อนรันคำสั่ง):
#   BRANCH=main                 สาขาที่ต้องการ deploy
#   PHP_FPM_SERVICE=php8.4-fpm  ชื่อ service ของ PHP-FPM (เว้นว่างไว้ = ข้ามการ reload)
#   PHP_BIN=php                 คำสั่ง PHP ที่ใช้
#   MAINTENANCE_SECRET=...      ค่า secret สำหรับเข้าเว็บระหว่างปิดปรับปรุง
#
set -euo pipefail

cd "$(dirname "$0")"

BRANCH="${BRANCH:-main}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE-php8.4-fpm}"
PHP_BIN="${PHP_BIN:-php}"
MAINTENANCE_SECRET="${MAINTENANCE_SECRET:-skp-maintenance-bypass}"

step() { printf '\n\033[1;33m==> %s\033[0m\n' "$1"; }

# ถ้าสคริปต์ล้ม ให้ "คงโหมดปิดปรับปรุงไว้" เพื่อไม่ให้ผู้ใช้เจอเว็บที่โค้ดใหม่แต่ยังไม่ครบขั้นตอน
on_error() {
    printf '\n\033[1;31mdeploy ล้มเหลว — เว็บยังอยู่ในโหมดปิดปรับปรุง\033[0m\n'
    printf 'แก้ปัญหาตามข้อความด้านบน แล้วรัน ./deploy.sh ซ้ำ (หรือรัน "%s artisan up" เพื่อเปิดเว็บกลับ)\n' "$PHP_BIN"
}
trap on_error ERR

step "ตรวจสถานะ git"
# เซิร์ฟเวอร์ Linux มักตั้งสิทธิ์ไฟล์ด้วย chmod (เช่น 775 ใน storage/ และ bootstrap/cache/) ทำให้ git เห็นว่า "modified"
# ทั้งที่เนื้อหาไม่เปลี่ยน (แค่ mode 100644 → 100755) จึงให้ git ไม่นับ file mode ในรีโพนี้ (ตั้งซ้ำได้ ไม่มีผลข้างเคียงอื่น)
git config core.fileMode false

if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    echo "พบไฟล์ที่ถูกแก้บนเซิร์ฟเวอร์ (ห้ามแก้โค้ดบนเซิร์ฟเวอร์ ให้แก้ที่เครื่อง dev แล้ว push):"
    git status --short --untracked-files=no
    exit 1
fi

step "เปิดโหมดปิดปรับปรุง"
"$PHP_BIN" artisan down --secret="$MAINTENANCE_SECRET" || true

step "ดึงโค้ดล่าสุด (origin/$BRANCH)"
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"
NEW_COMMIT="$(git log -1 --format='%h %s')"

step "ติดตั้ง PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

step "ตรวจ frontend assets (CSS/JS ถูก build บนเครื่อง dev แล้วมากับ git — เซิร์ฟเวอร์นี้ไม่ต้องใช้ Node)"
# ตรวจว่า manifest ชี้ไปยังไฟล์ที่มีอยู่จริง (กันกรณีลืม commit ไฟล์ใน public/build)
"$PHP_BIN" -r '
$manifest = json_decode(file_get_contents("public/build/manifest.json"), true);
if (! $manifest) { fwrite(STDERR, "อ่าน public/build/manifest.json ไม่ได้\n"); exit(1); }
foreach ($manifest as $entry) {
    if (! is_file("public/build/".$entry["file"])) { fwrite(STDERR, "ไม่พบไฟล์ build: ".$entry["file"]."\n"); exit(1); }
}
echo "assets ครบ (".count($manifest)." รายการ)\n";
'

step "อัปเดตโครงสร้างฐานข้อมูล (migrate เท่านั้น ไม่ลบข้อมูล)"
"$PHP_BIN" artisan migrate --force

step "ล้างและสร้าง cache ใหม่ (config/route/view/event)"
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan optimize

step "reload PHP-FPM (ล้าง OPcache)"
if [ -n "$PHP_FPM_SERVICE" ]; then
    if sudo -n systemctl reload "$PHP_FPM_SERVICE" 2>/dev/null || systemctl reload "$PHP_FPM_SERVICE" 2>/dev/null; then
        echo "reload $PHP_FPM_SERVICE แล้ว"
    else
        echo "คำเตือน: reload $PHP_FPM_SERVICE ไม่สำเร็จ — กรุณารันด้วยตนเอง: sudo systemctl reload $PHP_FPM_SERVICE"
    fi
else
    echo "ข้าม (ไม่ได้ตั้ง PHP_FPM_SERVICE)"
fi

step "เปิดเว็บกลับ"
"$PHP_BIN" artisan up

trap - ERR
printf '\n\033[1;32mdeploy เรียบร้อย:\033[0m %s\n' "$NEW_COMMIT"
