<?php

namespace App\Support;

/**
 * ระดับความสามารถด้านภาษา (0-5) ที่ผู้สมัครเลือกในฟอร์ม แปลงเป็นคำสำหรับแสดงผล
 * (PDF ใบสมัคร, หน้าตรวจทาน, ไฟล์ส่งออก)
 *
 * 0 = ไม่ได้ · 1 = น้อยมาก · 2 = พอใช้ · 3 = ดี · 4 = ดีมาก · 5 = ดีเยี่ยม
 */
class LanguageLevel
{
    /** @var array<int, array{0: string, 1: string}> [ไทย, อังกฤษ] */
    public const LABELS = [
        0 => ['ไม่ได้', 'None'],
        1 => ['น้อยมาก', 'Very limited'],
        2 => ['พอใช้', 'Fair'],
        3 => ['ดี', 'Good'],
        4 => ['ดีมาก', 'Very good'],
        5 => ['ดีเยี่ยม', 'Excellent'],
    ];

    /**
     * รูปแบบ "5 (ดีเยี่ยม)" — $lang: 'th', 'en' หรือ 'both' (ไทย / อังกฤษ)
     * คืนค่าว่างเมื่อไม่ได้กรอกระดับ
     */
    public static function format(int|string|null $level, string $lang = 'th'): string
    {
        if ($level === null || $level === '') {
            return '';
        }

        $level = (int) $level;
        $labels = self::LABELS[$level] ?? null;

        if ($labels === null) {
            return (string) $level;
        }

        $text = match ($lang) {
            'en' => $labels[1],
            'both' => $labels[0].' / '.$labels[1],
            default => $labels[0],
        };

        return $level.' ('.$text.')';
    }
}
