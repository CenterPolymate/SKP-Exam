<?php

use App\Livewire\Actions\ExamLogout;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.exam-app')] class extends Component
{
    use WithFileUploads;

    public ExamAttempt $attempt;

    public int $currentIndex = 0;

    public array $answers = [];

    public array $pendingFiles = [];

    public int $deadlineTimestamp = 0;

    public function mount(ExamAttempt $attempt): void
    {
        abort_unless($attempt->exam_assignment_id === Auth::guard('exam_applicant')->id(), 403);

        if ($attempt->status === 'not_started') {
            $this->redirect(route('exam.attempts.start', $attempt), navigate: true);

            return;
        }

        if (! in_array($attempt->status, ['in_progress'], true)) {
            $this->redirect(route('exam.dashboard'), navigate: true);

            return;
        }

        $this->attempt = $attempt->load(['exam.questions.choices']);

        $this->deadlineTimestamp = $this->attempt->started_at->timestamp + ($this->attempt->exam->duration_minutes * 60);

        if (now()->timestamp >= $this->deadlineTimestamp) {
            $this->finalizeAndGrade();
            $this->redirect(route('exam.dashboard'), navigate: true);

            return;
        }

        $existing = ExamAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('exam_question_id');

        foreach ($this->attempt->exam->questions as $question) {
            $answer = $existing->get($question->id);

            $this->answers[$question->id] = [
                'selected_choice_id' => $answer?->selected_choice_id,
                'selected_choice_ids' => $answer?->selected_choice_ids ?? [],
                'answer_text' => $answer?->answer_text ?? '',
                'answer_file_path' => $answer?->answer_file_path,
                'marked_for_review' => (bool) $answer?->marked_for_review,
            ];
        }
    }

    protected function question()
    {
        return $this->attempt->exam->questions[$this->currentIndex];
    }

    protected function saveAnswer(int $questionId, array $data): void
    {
        ExamAnswer::updateOrCreate(
            ['exam_attempt_id' => $this->attempt->id, 'exam_question_id' => $questionId],
            $data
        );
    }

    public function selectSingle(int $questionId, int $choiceId): void
    {
        $this->answers[$questionId]['selected_choice_id'] = $choiceId;
        $this->saveAnswer($questionId, ['selected_choice_id' => $choiceId, 'selected_choice_ids' => null]);
    }

    public function toggleMulti(int $questionId, int $choiceId): void
    {
        $ids = collect($this->answers[$questionId]['selected_choice_ids'] ?? []);

        $ids = $ids->contains($choiceId) ? $ids->reject(fn ($id) => $id == $choiceId) : $ids->push($choiceId);

        $this->answers[$questionId]['selected_choice_ids'] = $ids->values()->all();
        $this->saveAnswer($questionId, ['selected_choice_ids' => $ids->values()->all(), 'selected_choice_id' => null]);
    }

    public function saveText(int $questionId): void
    {
        $this->saveAnswer($questionId, ['answer_text' => $this->answers[$questionId]['answer_text']]);
    }

    public function uploadAnswerFile(int $questionId): void
    {
        $file = $this->pendingFiles[$questionId] ?? null;

        if (! $file) {
            return;
        }

        $path = $file->store("exam-answers/{$this->attempt->id}", 'public');

        $this->answers[$questionId]['answer_file_path'] = $path;
        $this->pendingFiles[$questionId] = null;
        $this->saveAnswer($questionId, ['answer_file_path' => $path]);
    }

    public function removeAnswerFile(int $questionId): void
    {
        $this->answers[$questionId]['answer_file_path'] = null;
        $this->saveAnswer($questionId, ['answer_file_path' => null]);
    }

    public function toggleMark(int $questionId): void
    {
        $this->answers[$questionId]['marked_for_review'] = ! $this->answers[$questionId]['marked_for_review'];
        $this->saveAnswer($questionId, ['marked_for_review' => $this->answers[$questionId]['marked_for_review']]);
    }

    public function goTo(int $index): void
    {
        $this->currentIndex = max(0, min($index, $this->attempt->exam->questions->count() - 1));
    }

    public function previous(): void
    {
        $this->goTo($this->currentIndex - 1);
    }

    public function next(): void
    {
        $this->goTo($this->currentIndex + 1);
    }

    protected function finalizeAndGrade(): void
    {
        if ($this->attempt->status !== 'in_progress') {
            return;
        }

        $questions = $this->attempt->exam->questions;
        $hasEssay = $questions->contains(fn ($q) => $q->question_type === 'essay');

        $totalPoints = (float) $questions->sum('points');
        $earned = 0.0;

        foreach ($questions as $q) {
            $answer = ExamAnswer::where('exam_attempt_id', $this->attempt->id)->where('exam_question_id', $q->id)->first();

            if (! $answer) {
                continue;
            }

            if ($q->question_type === 'single_choice') {
                $correctChoiceId = $q->choices->firstWhere('is_correct', true)?->id;
                $isCorrect = $answer->selected_choice_id && (int) $answer->selected_choice_id === $correctChoiceId;
                $answer->update(['score_given' => $isCorrect ? $q->points : 0]);
                $earned += $isCorrect ? (float) $q->points : 0;
            } elseif ($q->question_type === 'multi_choice') {
                $correctIds = $q->choices->where('is_correct', true)->pluck('id')->sort()->values()->all();
                $selectedIds = collect($answer->selected_choice_ids ?? [])->map(fn ($v) => (int) $v)->sort()->values()->all();
                $isCorrect = ! empty($correctIds) && $correctIds === $selectedIds;
                $answer->update(['score_given' => $isCorrect ? $q->points : 0]);
                $earned += $isCorrect ? (float) $q->points : 0;
            }
        }

        $percentage = $totalPoints > 0 ? ($earned / $totalPoints) * 100 : 0;

        $status = $hasEssay
            ? 'awaiting_review'
            : ($percentage >= (float) $this->attempt->exam->pass_percentage ? 'passed' : 'failed');

        $this->attempt->update([
            'status' => $status,
            'submitted_at' => now(),
            'score' => round($percentage, 2),
        ]);
    }

    public function submit(): void
    {
        $this->finalizeAndGrade();

        $this->redirect(route('exam.dashboard'), navigate: true);
    }

    public function autoSubmit(): void
    {
        $this->finalizeAndGrade();

        $this->redirect(route('exam.dashboard'), navigate: true);
    }

    public function logout(ExamLogout $logout): void
    {
        $logout();

        $this->redirect(route('exam.login'), navigate: true);
    }
}; ?>

<div class="min-h-screen bg-[#FAF3E7] pb-16" x-data="{
        remaining: {{ max(0, $deadlineTimestamp - now()->timestamp) }},
        confirmingSubmit: false,
        tick() {
            if (this.remaining <= 0) { $wire.autoSubmit(); return; }
            this.remaining--;
        },
        format() {
            let h = Math.floor(this.remaining / 3600);
            let m = Math.floor((this.remaining % 3600) / 60);
            let s = this.remaining % 60;
            return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }
    }" x-init="setInterval(() => tick(), 1000)">
    <header class="border-b border-stone-200 bg-white px-6 py-4">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-semibold text-stone-900">{{ $attempt->exam->name }}</p>
                <p class="text-xs text-stone-400">
                    {{ $attempt->assignment->applicant->first_name_th ?? '' }} {{ $attempt->assignment->applicant->last_name_th ?? '' }}
                    &middot; APP-{{ str_pad($attempt->assignment->applicant_id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>

            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span x-text="format()"></span>
            </span>

            <p class="text-sm font-medium text-stone-500">ข้อ {{ $currentIndex + 1 }}/{{ $attempt->exam->questions->count() }}</p>
        </div>
    </header>

    <main class="mx-auto mt-6 grid max-w-5xl grid-cols-1 gap-6 px-4 md:grid-cols-[180px_1fr]">
        {{-- Question map --}}
        <div class="h-fit rounded-2xl bg-white p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
            <p class="text-sm font-medium text-stone-500">แผนผังข้อสอบ</p>
            <div class="mt-3 grid grid-cols-5 gap-2 md:grid-cols-4">
                @foreach ($attempt->exam->questions as $index => $q)
                    @php
                        $qAnswered = ! empty($answers[$q->id]['selected_choice_id']) || ! empty($answers[$q->id]['selected_choice_ids']) || ! empty($answers[$q->id]['answer_text']) || ! empty($answers[$q->id]['answer_file_path']);
                        $qMarked = $answers[$q->id]['marked_for_review'] ?? false;
                    @endphp
                    <button
                        wire:click="goTo({{ $index }})"
                        type="button"
                        class="relative h-9 w-9 rounded-lg text-sm font-semibold {{ $index === $currentIndex ? 'bg-[#BA772A] text-white' : ($qAnswered ? 'bg-amber-50 text-amber-700' : 'bg-stone-100 text-stone-500') }}"
                    >
                        {{ $index + 1 }}
                        @if ($qMarked)
                            <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Question card --}}
        <div class="rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
            @php $q = $this->question(); @endphp

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-stone-800">ข้อ {{ $currentIndex + 1 }}</span>
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                        {{ match($q->question_type) {
                            'single_choice' => 'ตัวเลือกเดียว',
                            'multi_choice' => 'หลายตัวเลือก',
                            'essay' => $q->answer_format === 'file' ? 'อัตนัย (แนบไฟล์)' : 'อัตนัย (พิมพ์คำตอบ)',
                        } }}
                    </span>
                </div>
                <button type="button" wire:click="toggleMark({{ $q->id }})"
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium {{ ($answers[$q->id]['marked_for_review'] ?? false) ? 'border-red-300 bg-red-50 text-red-600' : 'border-stone-200 text-stone-500 hover:bg-stone-50' }}">
                    {{ ($answers[$q->id]['marked_for_review'] ?? false) ? 'ยกเลิกทำเครื่องหมาย' : 'ทำเครื่องหมาย' }}
                </button>
            </div>

            <div class="mt-4">
                @if ($q->content_format === 'image' && $q->question_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($q->question_image_path) }}" class="max-h-80 rounded-lg ring-1 ring-stone-200">
                @else
                    <p class="whitespace-pre-line text-stone-800">{{ $q->question_text }}</p>
                @endif
                <p class="mt-1 text-xs text-stone-400">{{ rtrim(rtrim(number_format((float) $q->points, 2), '0'), '.') }} คะแนน</p>
            </div>

            <div class="mt-5 space-y-2" wire:key="question-body-{{ $q->id }}">
                @if ($q->question_type === 'single_choice')
                    @foreach ($q->choices as $choice)
                        <button type="button" wire:click="selectSingle({{ $q->id }}, {{ $choice->id }})"
                                class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-sm {{ ($answers[$q->id]['selected_choice_id'] ?? null) == $choice->id ? 'border-[#BA772A] bg-amber-50 text-stone-900' : 'border-stone-200 text-stone-700 hover:bg-stone-50' }}">
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 {{ ($answers[$q->id]['selected_choice_id'] ?? null) == $choice->id ? 'border-[#BA772A]' : 'border-stone-300' }}">
                                @if (($answers[$q->id]['selected_choice_id'] ?? null) == $choice->id)
                                    <span class="h-2 w-2 rounded-full bg-[#BA772A]"></span>
                                @endif
                            </span>
                            {{ $choice->choice_text }}
                        </button>
                    @endforeach
                @elseif ($q->question_type === 'multi_choice')
                    @foreach ($q->choices as $choice)
                        @php $checked = collect($answers[$q->id]['selected_choice_ids'] ?? [])->contains($choice->id); @endphp
                        <button type="button" wire:click="toggleMulti({{ $q->id }}, {{ $choice->id }})"
                                class="flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left text-sm {{ $checked ? 'border-[#BA772A] bg-amber-50 text-stone-900' : 'border-stone-200 text-stone-700 hover:bg-stone-50' }}">
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded border-2 {{ $checked ? 'border-[#BA772A] bg-[#BA772A]' : 'border-stone-300' }}">
                                @if ($checked)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                @endif
                            </span>
                            {{ $choice->choice_text }}
                        </button>
                    @endforeach
                @elseif ($q->question_type === 'essay' && $q->answer_format === 'text')
                    <textarea wire:model="answers.{{ $q->id }}.answer_text" wire:change="saveText({{ $q->id }})" rows="6"
                              placeholder="พิมพ์คำตอบของคุณที่นี่"
                              class="w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]"></textarea>
                @else
                    @if ($answers[$q->id]['answer_file_path'] ?? null)
                        <div class="flex items-center justify-between rounded-lg border border-stone-200 px-4 py-3 text-sm">
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($answers[$q->id]['answer_file_path']) }}" target="_blank" class="font-medium text-[#BA772A] hover:underline">ดูไฟล์ที่แนบไว้</a>
                            <button type="button" wire:click="removeAnswerFile({{ $q->id }})" class="text-xs font-medium text-red-500 hover:text-red-700">ลบไฟล์</button>
                        </div>
                    @else
                        <input type="file" wire:model="pendingFiles.{{ $q->id }}" accept=".pdf,image/*"
                               class="w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-stone-100 file:px-3 file:py-1.5 focus:border-[#BA772A] focus:ring-[#BA772A]">
                        <p wire:loading wire:target="pendingFiles.{{ $q->id }}" class="mt-1 text-xs text-stone-400">กำลังอัปโหลด...</p>
                        <button type="button" wire:click="uploadAnswerFile({{ $q->id }})" wire:loading.attr="disabled" wire:target="pendingFiles.{{ $q->id }}"
                                class="mt-2 rounded-lg border border-[#BA772A] px-3 py-1.5 text-xs font-medium text-[#BA772A] hover:bg-amber-50 disabled:opacity-60">
                            แนบไฟล์คำตอบ
                        </button>
                    @endif
                @endif
            </div>

            <div class="mt-6 flex gap-3 border-t border-stone-100 pt-5">
                <button type="button" wire:click="previous" @disabled($currentIndex === 0)
                        class="rounded-lg border border-stone-200 px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-50 disabled:opacity-40">
                    &larr; กลับ
                </button>

                @if ($currentIndex + 1 < $attempt->exam->questions->count())
                    <button type="button" wire:click="next" class="ml-auto rounded-lg bg-[#BA772A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#a6661f]">
                        ถัดไป &rarr;
                    </button>
                @else
                    <button type="button" @click="confirmingSubmit = true"
                            class="ml-auto rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                        ส่งคำตอบ
                    </button>
                @endif
            </div>
        </div>
    </main>

    {{-- Confirm submit modal --}}
    <div
        x-show="confirmingSubmit"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
    >
        <div
            @click.outside="confirmingSubmit = false"
            x-show="confirmingSubmit"
            x-transition
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl"
        >
            <h3 class="text-lg font-semibold text-stone-900">ยืนยันการส่งคำตอบ</h3>
            <p class="mt-2 text-sm text-stone-500">หลังจากส่งแล้วจะไม่สามารถกลับมาแก้ไขคำตอบได้อีก ต้องการส่งคำตอบตอนนี้ใช่หรือไม่?</p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="confirmingSubmit = false" class="rounded-lg px-4 py-2 text-sm font-medium text-stone-500 hover:bg-stone-100">
                    ยกเลิก
                </button>
                <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-60">
                    ยืนยันส่งคำตอบ
                </button>
            </div>
        </div>
    </div>
</div>
