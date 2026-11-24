<?php

namespace App\Support;

/**
 * แปลงข้อความโจทย์เป็น HTML ที่ปลอดภัย โดยทำให้ URL แบบ http(s):// คลิกได้
 * - ข้อความทุกส่วน (รวมตัว URL) ถูก escape ก่อนแสดง จึงฝัง HTML/สคริปต์ผ่านโจทย์ไม่ได้
 * - ลิงก์เปิดในแท็บใหม่ด้วย rel="noopener noreferrer" และมี data-exam-link ให้หน้าข้อสอบใช้แยกว่าเป็นลิงก์ของโจทย์
 */
class Linkify
{
    public static function toHtml(?string $text): string
    {
        $parts = preg_split('#(https?://[^\s<>"\']+)#iu', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $html = '';
        foreach ($parts as $index => $part) {
            // index คี่ = ส่วนที่ตรงกับ pattern (URL) ตามพฤติกรรมของ PREG_SPLIT_DELIM_CAPTURE
            if ($index % 2 === 0) {
                $html .= e($part);

                continue;
            }

            // ตัดเครื่องหมายวรรคตอนท้ายประโยคที่ไม่ใช่ส่วนของลิงก์ออก (เช่น "ดูที่ https://x.com/a.")
            $trimmed = rtrim($part, '.,;:!?)]}');
            $trailing = substr($part, strlen($trimmed));

            if (filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
                $html .= e($part);

                continue;
            }

            $html .= '<a href="'.e($trimmed).'" target="_blank" rel="noopener noreferrer" data-exam-link '
                .'class="break-all font-medium text-blue-700 underline hover:text-blue-900">'.e($trimmed).'</a>'.e($trailing);
        }

        return $html;
    }
}
