<?php

namespace App\Livewire\Concerns;

/**
 * คำนำหน้า (TH/EN) ที่เลือก "อื่นๆ / Other" แล้วกรอกเองได้ (เช่น ดร., ร้อยตรี)
 *
 * คอลัมน์ title_th / title_en ยังคงเก็บค่าสุดท้ายเป็นข้อความตรง ๆ ไม่ต้องแก้ฐานข้อมูล
 * ส่วน *_choice / *_other เป็นสถานะของ UI เท่านั้น
 */
trait HasCustomTitles
{
    public string $title_th_choice = '';

    public string $title_th_other = '';

    public string $title_en_choice = '';

    public string $title_en_other = '';

    /** @return list<string> */
    public function titlePresets(string $lang): array
    {
        return $lang === 'th' ? ['นาย', 'นาง', 'นางสาว'] : ['Mr.', 'Mrs.', 'Ms.'];
    }

    public function updatedTitleThChoice(string $value): void
    {
        $this->title_th = $value === 'other' ? trim($this->title_th_other) : $value;
    }

    public function updatedTitleThOther(string $value): void
    {
        if ($this->title_th_choice === 'other') {
            $this->title_th = trim($value);
        }
    }

    public function updatedTitleEnChoice(string $value): void
    {
        $this->title_en = $value === 'other' ? trim($this->title_en_other) : $value;
    }

    public function updatedTitleEnOther(string $value): void
    {
        if ($this->title_en_choice === 'other') {
            $this->title_en = trim($value);
        }
    }

    /** กำหนดสถานะ dropdown จากค่า title_th/title_en ที่โหลดมาจากฐานข้อมูล */
    protected function loadTitleChoices(): void
    {
        foreach (['th', 'en'] as $lang) {
            $value = (string) $this->{"title_$lang"};

            if ($value === '' || in_array($value, $this->titlePresets($lang), true)) {
                $this->{"title_{$lang}_choice"} = $value;
                $this->{"title_{$lang}_other"} = '';
            } else {
                $this->{"title_{$lang}_choice"} = 'other';
                $this->{"title_{$lang}_other"} = $value;
            }
        }
    }
}
