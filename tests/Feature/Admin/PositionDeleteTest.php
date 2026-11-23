<?php

use App\Models\Applicant;
use App\Models\Position;
use Livewire\Volt\Volt;

test('a position that already has applicants can be deleted without touching those applications', function () {
    $applicant = makeFullApplicant();
    $position = $applicant->position;
    $position->update(['title' => 'HR Officer']);

    Volt::actingAs(makeAdmin())->test('pages.admin.positions')
        ->call('confirmDelete', $position->id)
        ->call('delete')
        ->assertSet('deleteError', '');

    // ตำแหน่งหายจากรายการปกติ (หน้าสมัคร/จัดการ) แต่แถวยังอยู่ในฐานข้อมูล
    expect(Position::find($position->id))->toBeNull()
        ->and(Position::withTrashed()->find($position->id))->not->toBeNull();

    // ใบสมัครเดิมยังอยู่ และยังเห็นว่าเคยสมัครตำแหน่ง HR Officer
    $fresh = Applicant::find($applicant->id);
    expect($fresh)->not->toBeNull()
        ->and($fresh->position_id)->toBe($position->id)
        ->and($fresh->position->title)->toBe('HR Officer');
});
