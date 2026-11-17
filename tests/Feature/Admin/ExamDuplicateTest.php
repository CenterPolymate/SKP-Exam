<?php

use App\Models\Exam;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

function makeExamWithQuestions(User $creator): Exam
{
    Storage::fake('public');
    Storage::disk('public')->put('exam-questions/original.png', 'fake-image-bytes');

    $exam = Exam::create([
        'name' => 'ตรรกะอย่างมีเหตุมีผลสำหรับ Programmer',
        'description' => 'ข้อสอบตรรกะ',
        'duration_minutes' => 45,
        'pass_percentage' => 70,
        'target_type' => 'position_specific',
        'status' => 'published',
        'created_by' => $creator->id,
    ]);
    $exam->positions()->sync([Position::create(['title' => 'Programmer', 'is_active' => true])->id]);

    $q1 = $exam->questions()->create([
        'question_type' => 'single_choice', 'content_format' => 'text', 'question_text' => '1 + 1 = ?',
        'points' => 2, 'order_no' => 0,
    ]);
    foreach ([['1', false], ['2', true], ['3', false]] as $i => [$text, $correct]) {
        $q1->choices()->create(['choice_text' => $text, 'is_correct' => $correct, 'order_no' => $i]);
    }

    $q2 = $exam->questions()->create([
        'question_type' => 'multi_choice', 'content_format' => 'image', 'question_image_path' => 'exam-questions/original.png',
        'points' => 3, 'order_no' => 1,
    ]);
    foreach ([['A', true], ['B', true], ['C', false]] as $i => [$text, $correct]) {
        $q2->choices()->create(['choice_text' => $text, 'is_correct' => $correct, 'order_no' => $i]);
    }

    $exam->questions()->create([
        'question_type' => 'essay', 'content_format' => 'text', 'question_text' => 'อธิบายขั้นตอนการแก้ปัญหา',
        'answer_format' => 'file', 'points' => 5, 'order_no' => 2,
    ]);

    return $exam;
}

test('duplicating an exam copies every question, choice, answer key and image into a new draft', function () {
    $author = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create(['role' => 'admin']);
    $exam = makeExamWithQuestions($author);

    $copy = $exam->duplicate($other->id);

    expect($copy->id)->not->toBe($exam->id)
        ->and($copy->name)->toBe('ตรรกะอย่างมีเหตุมีผลสำหรับ Programmer (สำเนา)')
        ->and($copy->status)->toBe('draft')
        ->and($copy->created_by)->toBe($other->id)
        ->and($copy->description)->toBe('ข้อสอบตรรกะ')
        ->and($copy->duration_minutes)->toBe(45)
        ->and((float) $copy->pass_percentage)->toBe(70.0)
        ->and($copy->target_type)->toBe('position_specific')
        ->and($copy->positions)->toHaveCount(0)
        ->and($copy->attempts)->toHaveCount(0);

    $original = $exam->questions()->with('choices')->get();
    $copied = $copy->questions()->with('choices')->get();
    expect($copied)->toHaveCount(3);

    foreach ($original as $i => $question) {
        $new = $copied[$i];
        expect($new->id)->not->toBe($question->id)
            ->and($new->only(['question_type', 'content_format', 'question_text', 'answer_format', 'order_no']))
            ->toBe($question->only(['question_type', 'content_format', 'question_text', 'answer_format', 'order_no']))
            ->and((float) $new->points)->toBe((float) $question->points)
            ->and($new->choices->map->only(['choice_text', 'is_correct', 'order_no'])->all())
            ->toBe($question->choices->map->only(['choice_text', 'is_correct', 'order_no'])->all());
    }

    // รูปโจทย์ถูกคัดลอกเป็นไฟล์ใหม่ ไม่ใช้ไฟล์ร่วมกัน
    $imagePath = $copied[1]->question_image_path;
    expect($imagePath)->not->toBeNull()->not->toBe('exam-questions/original.png')->toStartWith('exam-questions/');
    Storage::disk('public')->assertExists($imagePath);
    Storage::disk('public')->assertExists('exam-questions/original.png');
    expect(Storage::disk('public')->get($imagePath))->toBe('fake-image-bytes');

    // ต้นฉบับไม่เปลี่ยน
    expect($exam->fresh()->status)->toBe('published')
        ->and($exam->positions()->count())->toBe(1)
        ->and($exam->questions()->count())->toBe(3);
});

test('deleting the copy does not touch the original exam', function () {
    $author = User::factory()->create(['role' => 'admin']);
    $exam = makeExamWithQuestions($author);

    $copy = $exam->duplicate($author->id);
    $copy->delete();

    expect(Exam::find($exam->id))->not->toBeNull()
        ->and($exam->questions()->count())->toBe(3)
        ->and($exam->questions()->first()->choices()->count())->toBe(3);
});

test('exam list has a copy button that creates a draft copy and opens it for editing', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $exam = makeExamWithQuestions($admin);

    $this->actingAs($admin)->get(route('admin.exams.index'))
        ->assertOk()
        ->assertSee('คัดลอกข้อสอบ')
        ->assertSee('duplicate('.$exam->id.')', false);

    Volt::actingAs($admin)->test('pages.admin.exams')
        ->call('duplicate', $exam->id)
        ->assertRedirect(route('admin.exams.edit', Exam::where('name', 'like', '%(สำเนา)')->firstOrFail()));

    $copy = Exam::where('name', 'like', '%(สำเนา)')->firstOrFail();
    expect(Exam::count())->toBe(2)
        ->and($copy->status)->toBe('draft')
        ->and($copy->created_by)->toBe($admin->id)
        ->and($copy->questions()->count())->toBe(3);

    // หน้าแก้ไขของสำเนาแสดงข้อความแจ้ง และโหลดคำถามครบ
    $this->actingAs($admin)->withSession(['status' => 'คัดลอกข้อสอบเรียบร้อยแล้ว (ฉบับร่าง) กรุณา'])
        ->get(route('admin.exams.edit', $copy))
        ->assertOk()
        ->assertSee('คัดลอกข้อสอบเรียบร้อยแล้ว')
        ->assertSee('1 + 1 = ?');
});
