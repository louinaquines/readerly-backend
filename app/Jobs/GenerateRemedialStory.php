<?php

namespace App\Jobs;

use App\Models\ReadingSession;
use App\Models\GeneratedStory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GenerateRemedialStory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ReadingSession $session)
    {
    }

    public function handle(): void
    {
        $student       = $this->session->student;
        $errorPatterns = $this->session->error_patterns ?? [];

        if (empty($errorPatterns)) {
            return;
        }

        $errorList = implode(', ', $errorPatterns);
        $grade     = $student->grade;

        $prompt = "Write a short 5-sentence reading story for a {$grade} student. " .
                  "The story should heavily use these words or similar words: {$errorList}. " .
                  "Write in simple English and Filipino mixed (Taglish). " .
                  "Only return the story, no explanations.";

        $response = Http::timeout(60)->post('http://localhost:11434/api/generate', [
            'model'  => 'llama3.2:1b',
            'prompt' => $prompt,
            'stream' => false,
        ]);

        $story = $response->json('response');

        if ($story) {
            GeneratedStory::create([
                'student_id'         => $student->id,
                'reading_session_id' => $this->session->id,
                'story'              => trim($story),
                'target_patterns'    => $errorPatterns,
            ]);
        }
    }
}