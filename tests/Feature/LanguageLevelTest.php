<?php

use App\Models\ApplicantLanguage;
use App\Support\LanguageLevel;

test('language levels 0-5 are formatted as number plus word', function () {
    expect(LanguageLevel::format(0))->toBe('0 (ไม่ได้)')
        ->and(LanguageLevel::format(1))->toBe('1 (น้อยมาก)')
        ->and(LanguageLevel::format(2))->toBe('2 (พอใช้)')
        ->and(LanguageLevel::format(3))->toBe('3 (ดี)')
        ->and(LanguageLevel::format(4))->toBe('4 (ดีมาก)')
        ->and(LanguageLevel::format('5'))->toBe('5 (ดีเยี่ยม)')
        ->and(LanguageLevel::format(5, 'en'))->toBe('5 (Excellent)')
        ->and(LanguageLevel::format(0, 'both'))->toBe('0 (ไม่ได้ / None)')
        ->and(LanguageLevel::format(null))->toBe('')
        ->and(LanguageLevel::format(''))->toBe('');
});

test('pdf, review page and export show the number with its word', function () {
    $applicant = makeFullApplicant();
    $applicant->languages()->delete();
    foreach (['thai' => 5, 'english' => 3, 'chinese' => 0] as $language => $level) {
        ApplicantLanguage::create([
            'applicant_id' => $applicant->id, 'language' => $language,
            'speaking_level' => $level, 'understanding_level' => $level, 'reading_level' => $level, 'writing_level' => $level,
        ]);
    }
    $applicant = $applicant->fresh()->load(['position', 'education', 'languages', 'workHistories', 'references', 'documents']);

    $pdf = view('pdf.application', ['applicant' => $applicant])->render();
    expect($pdf)->toContain('5 (ดีเยี่ยม)')->toContain('3 (ดี)')->toContain('0 (ไม่ได้)')
        ->not->toContain('พอใช้</td>');

    $review = view('partials.applicant-review', ['a' => $applicant, 'locale' => 'en'])->render();
    expect($review)->toContain('5 (Excellent)')->toContain('0 (None)');

    $summary = Livewire\Volt\Volt::actingAs(makeAdmin())->test('pages.admin.summary')->instance();
    $columns = (new ReflectionMethod($summary, 'exportColumns'))->invoke($summary);
    expect($columns['ความสามารถด้านภาษา']($applicant))->toContain('จีน (พูด 0 (ไม่ได้)')->toContain('อังกฤษ (พูด 3 (ดี)');
});
