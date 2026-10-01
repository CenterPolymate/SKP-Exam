<?php

namespace App\Http\Controllers;

use App\Models\ApplicantDocument;
use App\Models\ExamAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    public function applicantDocument(ApplicantDocument $document): StreamedResponse
    {
        abort_unless(Auth::guard('web')->user()?->isAdmin(), 403);

        return Storage::disk('local')->response($document->file_path, $document->original_filename);
    }

    public function examAnswer(ExamAnswer $answer): StreamedResponse
    {
        $isAdmin = Auth::guard('web')->user()?->isAdmin();
        $ownsAttempt = $answer->attempt->exam_assignment_id === Auth::guard('exam_applicant')->id();

        abort_unless($isAdmin || $ownsAttempt, 403);

        return Storage::disk('local')->response($answer->answer_file_path);
    }
}
