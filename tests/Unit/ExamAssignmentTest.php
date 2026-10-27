<?php

use App\Models\ExamAssignment;
use Carbon\Carbon;

test('exam assignment detects expired deadline correctly', function () {
    $pastAssignment = new ExamAssignment([
        'exam_deadline' => Carbon::yesterday(),
    ]);

    $futureAssignment = new ExamAssignment([
        'exam_deadline' => Carbon::tomorrow(),
    ]);

    expect($pastAssignment->isExpired())->toBeTrue()
        ->and($futureAssignment->isExpired())->toBeFalse();
});
