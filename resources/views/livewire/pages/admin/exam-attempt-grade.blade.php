<?php

use App\Livewire\Actions\Logout;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public ExamAttempt $attempt;

    public array $scores = [];

    public function mount(ExamAttempt $attempt): void
    {
        $this->attempt = $attempt->load(['exam.questions.choices', 'answers', 'assignment.applicant']);

        foreach ($this->attempt->exam->questions as $question) {
            if ($question->question_type === 'essay') {
                $answer = $this->attempt->answers->firstWhere('exam_question_id', $question->id);
                $this->scores[$question->id] = $answer?->score_given !== null ? (float) $answer->score_given : 0;
            }
        }
    }

    public function save(): void
    {
        $this->validate(
            collect($this->scores)->mapWithKeys(fn ($v, $questionId) => [
                "scores.{$questionId}" => ['required', 'numeric', 'min:0'],
            ])->all(),
            [],
            collect($this->scores)->mapWithKeys(fn ($v, $questionId) => [
                "scores.{$questionId}" => 'คะแนน',
            ])->all()
        );

        foreach ($this->attempt->exam->questions as $question) {
            if ($question->question_type !== 'essay') {
                continue;
            }

            $score = min((float) $this->scores[$question->id], (float) $question->points);

            ExamAnswer::updateOrCreate(
                ['exam_attempt_id' => $this->attempt->id, 'exam_question_id' => $question->id],
                ['score_given' => $score, 'graded_by' => auth()->id(), 'graded_at' => now()]
            );
        }

        $totalPoints = (float) $this->attempt->exam->questions->sum('points');
        $earned = (float) ExamAnswer::where('exam_attempt_id', $this->attempt->id)->sum('score_given');
        $percentage = $totalPoints > 0 ? ($earned / $totalPoints) * 100 : 0;

        $this->attempt->update([
            'status' => $percentage >= (float) $this->attempt->exam->pass_percentage ? 'passed' : 'failed',
            'score' => round($percentage, 2),
        ]);

        $this->redirect(route('admin.exam-review.show', $this->attempt->assignment->applicant), navigate: true);
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="flex min-h-screen bg-[#FAF8F3]">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <a href="{{ route('admin.exam-review.show', $attempt->assignment->applicant) }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
            &larr; กลับไปหน้าตรวจข้อสอบ
        </a>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-stone-900">{{ $attempt->exam->name }}</h1>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $attempt->assignment->applicant->title_th }}{{ $attempt->assignment->applicant->first_name_th }} {{ $attempt->assignment->applicant->last_name_th }}
                </p>
            </div>
        </div>

        <div class="mt-4 space-y-4">
            @foreach ($attempt->exam->questions as $index => $q)
                @php $answer = $attempt->answers->firstWhere('exam_question_id', $q->id); @endphp
                <div class="rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-stone-800">ข้อ {{ $index + 1 }}</span>
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                            {{ match($q->question_type) {
                                'single_choice' => 'ตัวเลือกเดียว (ตรวจอัตโนมัติ)',
                                'multi_choice' => 'หลายตัวเลือก (ตรวจอัตโนมัติ)',
                                'essay' => $q->answer_format === 'file' ? 'อัตนัย (แนบไฟล์)' : 'อัตนัย (พิมพ์คำตอบ)',
                            } }}
                        </span>
                    </div>

                    <div class="mt-3">
                        @if ($q->content_format === 'image' && $q->question_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($q->question_image_path) }}" class="max-h-80 rounded-lg ring-1 ring-stone-200">
                        @else
                            <p class="whitespace-pre-line text-stone-800">{{ $q->question_text }}</p>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-stone-400">คะแนนเต็ม {{ rtrim(rtrim(number_format((float) $q->points, 2), '0'), '.') }} คะแนน</p>

                    <div class="mt-4 rounded-lg bg-stone-50 p-4">
                        @if ($q->question_type === 'single_choice')
                            <p class="text-sm text-stone-600">คำตอบที่เลือก: {{ $q->choices->firstWhere('id', $answer?->selected_choice_id)?->choice_text ?? '-' }}</p>
                            <p class="mt-1 text-xs {{ $answer?->score_given > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $answer?->score_given > 0 ? 'ตอบถูก' : 'ตอบผิด' }} ({{ $answer?->score_given ?? 0 }}/{{ rtrim(rtrim(number_format((float) $q->points, 2), '0'), '.') }})
                            </p>
                        @elseif ($q->question_type === 'multi_choice')
                            @php $selected = collect($answer?->selected_choice_ids ?? [])->map(fn ($id) => $q->choices->firstWhere('id', $id)?->choice_text)->filter()->join(', '); @endphp
                            <p class="text-sm text-stone-600">คำตอบที่เลือก: {{ $selected ?: '-' }}</p>
                            <p class="mt-1 text-xs {{ $answer?->score_given > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $answer?->score_given > 0 ? 'ตอบถูก' : 'ตอบผิด' }} ({{ $answer?->score_given ?? 0 }}/{{ rtrim(rtrim(number_format((float) $q->points, 2), '0'), '.') }})
                            </p>
                        @elseif ($q->answer_format === 'file')
                            @if ($answer?->answer_file_path)
                                <a href="{{ route('secure-files.exam-answer', $answer) }}" target="_blank" class="text-sm font-medium text-[#BA772A] hover:underline">ดูไฟล์คำตอบที่แนบไว้</a>
                            @else
                                <p class="text-sm text-stone-400">ผู้สมัครไม่ได้แนบไฟล์คำตอบ</p>
                            @endif

                            <div class="mt-3 w-40">
                                <label class="text-xs text-stone-500">ให้คะแนน (เต็ม {{ rtrim(rtrim(number_format((float) $q->points, 2), '0'), '.') }})</label>
                                <input type="number" min="0" max="{{ $q->points }}" step="0.5" wire:model="scores.{{ $q->id }}"
                                       class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                                @error("scores.{$q->id}") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <p class="whitespace-pre-line text-sm text-stone-700">{{ $answer?->answer_text ?: 'ผู้สมัครไม่ได้ตอบคำถามนี้' }}</p>

                            <div class="mt-3 w-40">
                                <label class="text-xs text-stone-500">ให้คะแนน (เต็ม {{ rtrim(rtrim(number_format((float) $q->points, 2), '0'), '.') }})</label>
                                <input type="number" min="0" max="{{ $q->points }}" step="0.5" wire:model="scores.{{ $q->id }}"
                                       class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                                @error("scores.{$q->id}") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-end">
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="rounded-lg bg-[#BA772A] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#a6661f] disabled:opacity-60">
                บันทึกคะแนนและสรุปผล
            </button>
        </div>
    </main>
</div>
