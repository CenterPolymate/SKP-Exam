<?php

use App\Livewire\Actions\Logout;
use App\Models\Exam;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin')] class extends Component
{
    use WithFileUploads;

    public ?Exam $exam = null;

    public string $name = '';

    public string $description = '';

    public ?int $duration_minutes = 30;

    public $pass_percentage = 60;

    public string $target_type = 'mandatory';

    public array $selectedPositions = [];

    public array $questions = [];

    public function mount(?Exam $exam = null): void
    {
        if ($exam && $exam->exists) {
            $exam->load('questions.choices', 'positions');
            $this->exam = $exam;
            $this->name = $exam->name;
            $this->description = (string) $exam->description;
            $this->duration_minutes = $exam->duration_minutes;
            $this->pass_percentage = (float) $exam->pass_percentage;
            $this->target_type = $exam->target_type;
            $this->selectedPositions = $exam->positions->pluck('id')->map(fn ($id) => (string) $id)->all();

            $this->questions = $exam->questions->map(fn ($q) => [
                'content_format' => $q->content_format,
                'question_text' => (string) $q->question_text,
                'existing_image_path' => $q->question_image_path,
                'question_image' => null,
                'question_type' => $q->question_type,
                'answer_format' => $q->answer_format,
                'points' => (float) $q->points,
                'choices' => $q->choices->map(fn ($c) => [
                    'choice_text' => $c->choice_text,
                    'is_correct' => $c->is_correct,
                ])->all(),
            ])->all();
        } else {
            $this->questions = [$this->emptyQuestion()];
        }
    }

    protected function defaultChoices(): array
    {
        return [
            ['choice_text' => '', 'is_correct' => true],
            ['choice_text' => '', 'is_correct' => false],
            ['choice_text' => '', 'is_correct' => false],
            ['choice_text' => '', 'is_correct' => false],
        ];
    }

    protected function emptyQuestion(): array
    {
        return [
            'content_format' => 'text',
            'question_text' => '',
            'existing_image_path' => null,
            'question_image' => null,
            'question_type' => 'single_choice',
            'answer_format' => null,
            'points' => 1,
            'choices' => $this->defaultChoices(),
        ];
    }

    public function addQuestion(): void
    {
        $this->questions[] = $this->emptyQuestion();
    }

    public function removeQuestion(int $index): void
    {
        if (count($this->questions) <= 1) {
            return;
        }

        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function setContentFormat(int $qIndex, string $format): void
    {
        $this->questions[$qIndex]['content_format'] = $format;
    }

    public function removeQuestionImage(int $qIndex): void
    {
        $this->questions[$qIndex]['question_image'] = null;
        $this->questions[$qIndex]['existing_image_path'] = null;
    }

    public function answerMode(array $question): string
    {
        return match ($question['question_type']) {
            'single_choice' => 'single_choice',
            'multi_choice' => 'multi_choice',
            'essay' => $question['answer_format'] === 'file' ? 'essay_file' : 'essay_text',
            default => 'single_choice',
        };
    }

    public function setAnswerMode(int $qIndex, string $mode): void
    {
        $this->questions[$qIndex]['question_type'] = match ($mode) {
            'single_choice' => 'single_choice',
            'multi_choice' => 'multi_choice',
            default => 'essay',
        };

        $this->questions[$qIndex]['answer_format'] = match ($mode) {
            'essay_text' => 'text',
            'essay_file' => 'file',
            default => null,
        };

        if (in_array($mode, ['single_choice', 'multi_choice']) && count($this->questions[$qIndex]['choices']) === 0) {
            $this->questions[$qIndex]['choices'] = $this->defaultChoices();
        }
    }

    public function addChoice(int $qIndex): void
    {
        $this->questions[$qIndex]['choices'][] = ['choice_text' => '', 'is_correct' => false];
    }

    public function removeChoice(int $qIndex, int $cIndex): void
    {
        if (count($this->questions[$qIndex]['choices']) <= 2) {
            return;
        }

        $wasCorrect = $this->questions[$qIndex]['choices'][$cIndex]['is_correct'];
        unset($this->questions[$qIndex]['choices'][$cIndex]);
        $this->questions[$qIndex]['choices'] = array_values($this->questions[$qIndex]['choices']);

        if ($wasCorrect && $this->questions[$qIndex]['question_type'] === 'single_choice') {
            $this->questions[$qIndex]['choices'][0]['is_correct'] = true;
        }
    }

    public function markCorrect(int $qIndex, int $cIndex): void
    {
        foreach ($this->questions[$qIndex]['choices'] as $i => $choice) {
            $this->questions[$qIndex]['choices'][$i]['is_correct'] = ($i === $cIndex);
        }
    }

    public function toggleCorrect(int $qIndex, int $cIndex): void
    {
        $this->questions[$qIndex]['choices'][$cIndex]['is_correct'] = ! $this->questions[$qIndex]['choices'][$cIndex]['is_correct'];
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'pass_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_type' => ['required', 'in:mandatory,position_specific'],
            'questions' => ['required', 'array', 'min:1'],
        ];

        foreach ($this->questions as $i => $q) {
            if (($q['content_format'] ?? 'text') === 'text') {
                $rules["questions.{$i}.question_text"] = ['required', 'string'];
            } elseif (empty($q['existing_image_path']) && empty($q['question_image'])) {
                $rules["questions.{$i}.question_image"] = ['required', 'image', 'max:5120'];
            } elseif (! empty($q['question_image'])) {
                $rules["questions.{$i}.question_image"] = ['image', 'max:5120'];
            }

            $rules["questions.{$i}.points"] = ['required', 'numeric', 'min:0.01'];

            if (in_array($q['question_type'], ['single_choice', 'multi_choice'])) {
                $rules["questions.{$i}.choices"] = ['required', 'array', 'min:2'];
                $rules["questions.{$i}.choices.*.choice_text"] = ['required', 'string'];
            }
        }

        if ($this->target_type === 'position_specific') {
            $rules['selectedPositions'] = ['required', 'array', 'min:1'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'กรุณากรอกชื่อข้อสอบ',
            'duration_minutes.required' => 'กรุณากรอกเวลาในการทำข้อสอบ',
            'pass_percentage.required' => 'กรุณากรอกเกณฑ์ผ่าน',
            'questions.*.question_text.required' => 'กรุณากรอกโจทย์คำถามให้ครบทุกข้อ',
            'questions.*.question_image.required' => 'กรุณาอัปโหลดรูปภาพโจทย์ให้ครบทุกข้อ',
            'questions.*.points.required' => 'กรุณากรอกคะแนนให้ครบทุกข้อ',
            'questions.*.choices.*.choice_text.required' => 'กรุณากรอกตัวเลือกให้ครบทุกช่อง',
            'selectedPositions.required' => 'กรุณาเลือกตำแหน่งอย่างน้อย 1 ตำแหน่ง',
        ];
    }

    protected function questionsHaveValidAnswers(): bool
    {
        foreach ($this->questions as $i => $q) {
            if ($q['question_type'] === 'single_choice') {
                $correct = collect($q['choices'])->where('is_correct', true)->count();
                if ($correct !== 1) {
                    $this->addError("questions.{$i}.choices", 'กรุณาเลือกคำตอบที่ถูกต้อง 1 ข้อ');

                    return false;
                }
            } elseif ($q['question_type'] === 'multi_choice') {
                $correct = collect($q['choices'])->where('is_correct', true)->count();
                if ($correct < 1) {
                    $this->addError("questions.{$i}.choices", 'กรุณาเลือกคำตอบที่ถูกต้องอย่างน้อย 1 ข้อ');

                    return false;
                }
            }
        }

        return true;
    }

    public function save(string $asStatus): void
    {
        $this->validate();

        if (! $this->questionsHaveValidAnswers()) {
            return;
        }

        DB::transaction(function () use ($asStatus) {
            $data = [
                'name' => $this->name,
                'description' => $this->description !== '' ? $this->description : null,
                'duration_minutes' => $this->duration_minutes,
                'pass_percentage' => $this->pass_percentage,
                'target_type' => $this->target_type,
                'status' => $asStatus,
            ];

            if ($this->exam) {
                $this->exam->update($data);
                $exam = $this->exam;
                $exam->questions()->delete();
            } else {
                $data['created_by'] = auth()->id();
                $exam = Exam::create($data);
            }

            $exam->positions()->sync($this->target_type === 'position_specific' ? $this->selectedPositions : []);

            foreach ($this->questions as $i => $q) {
                $imagePath = $q['existing_image_path'] ?? null;

                if (! empty($q['question_image'])) {
                    $imagePath = $q['question_image']->store('exam-questions', 'public');
                }

                $question = $exam->questions()->create([
                    'question_type' => $q['question_type'],
                    'content_format' => $q['content_format'],
                    'question_text' => $q['content_format'] === 'text' ? $q['question_text'] : null,
                    'question_image_path' => $q['content_format'] === 'image' ? $imagePath : null,
                    'points' => $q['points'],
                    'answer_format' => $q['question_type'] === 'essay' ? $q['answer_format'] : null,
                    'order_no' => $i,
                ]);

                if (in_array($q['question_type'], ['single_choice', 'multi_choice'])) {
                    foreach ($q['choices'] as $j => $choice) {
                        $question->choices()->create([
                            'choice_text' => $choice['choice_text'],
                            'is_correct' => $choice['is_correct'],
                            'order_no' => $j,
                        ]);
                    }
                }
            }
        });

        $this->redirect(route('admin.exams.index'), navigate: true);
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
        <a href="{{ route('admin.exams.index') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
            &larr; กลับไปคลังข้อสอบ
        </a>

        <h1 class="mt-3 text-2xl font-bold text-stone-900">{{ $exam ? 'แก้ไขข้อสอบ' : 'สร้างข้อสอบใหม่' }}</h1>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[340px_1fr]">
            {{-- Left: exam settings --}}
            <div class="h-fit rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                <div>
                    <label class="text-sm text-stone-500">ชื่อข้อสอบ <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="ข้อสอบใหม่"
                           class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label class="text-sm text-stone-500">คำอธิบาย</label>
                    <textarea wire:model="description" rows="2" class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]"></textarea>
                </div>

                <div class="mt-4">
                    <label class="text-sm text-stone-500">เวลาในการทำ (นาที) <span class="text-red-500">*</span></label>
                    <input type="number" min="1" wire:model="duration_minutes"
                           class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    @error('duration_minutes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label class="text-sm text-stone-500">เกณฑ์ผ่าน (%) <span class="text-red-500">*</span></label>
                    <input type="number" min="0" max="100" step="0.01" wire:model="pass_percentage"
                           class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    @error('pass_percentage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label class="text-sm text-stone-500">ข้อสอบนี้สำหรับ <span class="text-red-500">*</span></label>

                    <label class="mt-2 flex cursor-pointer items-start gap-2 rounded-lg border border-stone-200 p-3 {{ $target_type === 'mandatory' ? 'border-[#BA772A] bg-amber-50/50' : '' }}">
                        <input type="radio" wire:model.live="target_type" value="mandatory" class="mt-0.5 text-[#BA772A] focus:ring-[#BA772A]">
                        <span>
                            <span class="block text-sm font-medium text-stone-800">ให้ทุกคน (บังคับสอบ)</span>
                            <span class="block text-xs text-stone-400">ผู้สมัครทุกตำแหน่งต้องทำข้อสอบนี้</span>
                        </span>
                    </label>

                    <label class="mt-2 flex cursor-pointer items-start gap-2 rounded-lg border border-stone-200 p-3 {{ $target_type === 'position_specific' ? 'border-[#BA772A] bg-amber-50/50' : '' }}">
                        <input type="radio" wire:model.live="target_type" value="position_specific" class="mt-0.5 text-[#BA772A] focus:ring-[#BA772A]">
                        <span>
                            <span class="block text-sm font-medium text-stone-800">ความถนัดตามตำแหน่ง</span>
                            <span class="block text-xs text-stone-400">เฉพาะผู้สมัครตำแหน่งที่เลือก</span>
                        </span>
                    </label>

                    @if ($target_type === 'position_specific')
                        <div class="mt-2 max-h-48 space-y-1 overflow-y-auto rounded-lg border border-stone-200 p-3">
                            @foreach (\App\Models\Position::orderBy('title')->get() as $position)
                                <label class="flex items-center gap-2 text-sm text-stone-600">
                                    <input type="checkbox" wire:model="selectedPositions" value="{{ $position->id }}" class="rounded border-stone-300 text-[#BA772A] focus:ring-[#BA772A]">
                                    {{ $position->title }}
                                </label>
                            @endforeach
                        </div>
                        @error('selectedPositions') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @endif
                </div>

                <div class="mt-6 space-y-2">
                    <button type="button" wire:click="save('draft')" wire:loading.attr="disabled" wire:target="save"
                            class="w-full rounded-lg border border-[#BA772A] bg-white px-4 py-2.5 text-sm font-semibold text-[#BA772A] transition hover:bg-amber-50 disabled:opacity-60">
                        บันทึกฉบับร่าง
                    </button>
                    <button type="button" wire:click="save('published')" wire:loading.attr="disabled" wire:target="save"
                            class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-60">
                        เผยแพร่
                    </button>
                </div>
            </div>

            {{-- Right: question builder --}}
            <div class="rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-stone-800">คำถาม ({{ count($questions) }} ข้อ)</h2>
                    <button type="button" wire:click="addQuestion" class="inline-flex items-center gap-1.5 rounded-lg bg-[#BA772A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        เพิ่มคำถาม
                    </button>
                </div>

                <div class="mt-4 space-y-6">
                    @foreach ($questions as $qIndex => $question)
                        @php $mode = $this->answerMode($question); @endphp
                        <div wire:key="question-{{ $qIndex }}" class="rounded-xl border border-stone-200 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-stone-800">ข้อ {{ $qIndex + 1 }}</span>
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                                        {{ match($mode) {
                                            'single_choice' => 'ตัวเลือกเดียว',
                                            'multi_choice' => 'หลายตัวเลือก',
                                            'essay_text' => 'อัตนัย (พิมพ์คำตอบ)',
                                            'essay_file' => 'อัตนัย (แนบไฟล์)',
                                        } }}
                                    </span>
                                </div>
                                @if (count($questions) > 1)
                                    <button type="button" wire:click="removeQuestion({{ $qIndex }})" class="text-xs font-medium text-red-500 hover:text-red-700">ลบ</button>
                                @endif
                            </div>

                            {{-- Content format tabs --}}
                            <div class="mt-3 inline-flex rounded-lg border border-stone-200 p-1 text-xs font-medium">
                                <button type="button" wire:click="setContentFormat({{ $qIndex }}, 'text')"
                                        class="rounded-md px-3 py-1.5 {{ $question['content_format'] === 'text' ? 'bg-[#BA772A] text-white' : 'text-stone-500' }}">
                                    ข้อความ
                                </button>
                                <button type="button" wire:click="setContentFormat({{ $qIndex }}, 'image')"
                                        class="rounded-md px-3 py-1.5 {{ $question['content_format'] === 'image' ? 'bg-[#BA772A] text-white' : 'text-stone-500' }}">
                                    รูปภาพ
                                </button>
                            </div>

                            @if ($question['content_format'] === 'text')
                                <div class="mt-3">
                                    <label class="text-sm text-stone-500">โจทย์คำถาม <span class="text-red-500">*</span></label>
                                    <textarea wire:model="questions.{{ $qIndex }}.question_text" rows="2"
                                              class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]"></textarea>
                                    @error("questions.{$qIndex}.question_text") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div class="mt-3">
                                    <label class="text-sm text-stone-500">รูปภาพโจทย์ <span class="text-red-500">*</span></label>

                                    @if ($question['question_image'])
                                        <div class="mt-2 flex items-center gap-3">
                                            <img src="{{ $question['question_image']->temporaryUrl() }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-stone-200">
                                            <button type="button" wire:click="removeQuestionImage({{ $qIndex }})" class="text-xs font-medium text-red-500 hover:text-red-700">ลบรูปภาพ</button>
                                        </div>
                                    @elseif ($question['existing_image_path'])
                                        <div class="mt-2 flex items-center gap-3">
                                            <img src="{{ Illuminate\Support\Facades\Storage::url($question['existing_image_path']) }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-stone-200">
                                            <button type="button" wire:click="removeQuestionImage({{ $qIndex }})" class="text-xs font-medium text-red-500 hover:text-red-700">ลบรูปภาพ</button>
                                        </div>
                                    @else
                                        <input type="file" wire:model="questions.{{ $qIndex }}.question_image" accept="image/*"
                                               class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-stone-100 file:px-3 file:py-1.5 focus:border-[#BA772A] focus:ring-[#BA772A]">
                                        <p wire:loading wire:target="questions.{{ $qIndex }}.question_image" class="mt-1 text-xs text-stone-400">กำลังอัปโหลด...</p>
                                    @endif
                                    @error("questions.{$qIndex}.question_image") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            <div class="mt-3 w-32">
                                <label class="text-sm text-stone-500">คะแนน</label>
                                <input type="number" min="0" step="0.5" wire:model="questions.{{ $qIndex }}.points"
                                       class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                            </div>

                            {{-- Answer mode selector --}}
                            <div class="mt-4">
                                <label class="text-sm text-stone-500">ประเภทคำตอบ</label>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach ([
                                        'single_choice' => 'ตัวเลือกเดียว',
                                        'multi_choice' => 'หลายตัวเลือก',
                                        'essay_text' => 'อัตนัย (พิมพ์คำตอบ)',
                                        'essay_file' => 'อัตนัย (แนบไฟล์)',
                                    ] as $modeKey => $modeLabel)
                                        <button type="button" wire:click="setAnswerMode({{ $qIndex }}, '{{ $modeKey }}')"
                                                class="rounded-full border px-3 py-1.5 text-xs font-medium {{ $mode === $modeKey ? 'border-[#BA772A] bg-amber-50 text-[#BA772A]' : 'border-stone-200 text-stone-500 hover:bg-stone-50' }}">
                                            {{ $modeLabel }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @if (in_array($mode, ['single_choice', 'multi_choice']))
                                <div class="mt-4 space-y-2">
                                    @foreach ($question['choices'] as $cIndex => $choice)
                                        <div wire:key="question-{{ $qIndex }}-choice-{{ $cIndex }}" class="flex items-center gap-2">
                                            @if ($mode === 'single_choice')
                                                <button type="button" wire:click="markCorrect({{ $qIndex }}, {{ $cIndex }})"
                                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 {{ $choice['is_correct'] ? 'border-green-600' : 'border-stone-300' }}">
                                                    @if ($choice['is_correct'])
                                                        <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                                                    @endif
                                                </button>
                                            @else
                                                <button type="button" wire:click="toggleCorrect({{ $qIndex }}, {{ $cIndex }})"
                                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 {{ $choice['is_correct'] ? 'border-green-600 bg-green-600' : 'border-stone-300' }}">
                                                    @if ($choice['is_correct'])
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    @endif
                                                </button>
                                            @endif
                                            <input type="text" wire:model="questions.{{ $qIndex }}.choices.{{ $cIndex }}.choice_text" placeholder="ตัวเลือกที่ {{ $cIndex + 1 }}"
                                                   class="w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                                            @if (count($question['choices']) > 2)
                                                <button type="button" wire:click="removeChoice({{ $qIndex }}, {{ $cIndex }})" class="shrink-0 text-stone-300 hover:text-red-500">
                                                    &times;
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                    @error("questions.{$qIndex}.choices") <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <button type="button" wire:click="addChoice({{ $qIndex }})" class="mt-3 text-sm font-medium text-[#BA772A] hover:text-[#a6661f]">
                                    + เพิ่มตัวเลือก
                                </button>
                            @else
                                <p class="mt-4 rounded-lg bg-stone-50 px-3 py-2 text-sm text-stone-500">
                                    {{ $mode === 'essay_file' ? 'ผู้สมัครจะแนบไฟล์คำตอบเอง (PDF หรือรูปภาพ)' : 'ผู้สมัครจะพิมพ์คำตอบเอง' }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
</div>
