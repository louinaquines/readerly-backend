<?php
namespace App\Jobs;

use App\Models\ReadingSession;
use App\Models\GeneratedStory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRemedialStory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ReadingSession $session) {}

    public function handle(): void
    {
        $student       = $this->session->student;
        $errorPatterns = $this->session->error_patterns ?? [];
        if (is_string($errorPatterns)) {
            $errorPatterns = json_decode($errorPatterns, true) ?? [];
        }
        
        if (empty($errorPatterns)) {
            return;
        }

        $story = $this->generateStory($errorPatterns, $student->grade ?? '1');

        GeneratedStory::create([
            'student_id'         => $student->id,
            'reading_session_id' => $this->session->id,
            'story'              => $story,
            'target_patterns'    => $errorPatterns,
        ]);
    }

    private function generateStory(array $errorWords, string $grade): string
    {
        $words  = array_slice($errorWords, 0, 5);
        $count  = count($words);
        $grade  = (int) preg_replace('/[^0-9]/', '', $grade) ?: 1;

        $characters = ['Ana', 'Ben', 'Carlo', 'Diana', 'Emma', 'Felix'];
        $char = $characters[array_sum(array_map('ord', str_split(implode('', $words)))) % count($characters)];

        $settings = ['at school', 'at home', 'in the park', 'by the river', 'in the garden'];
        $setting  = $settings[strlen(implode('', $words)) % count($settings)];

        $sentences   = [];
        $sentences[] = "{$char} is a happy child who loves to read {$setting}.";

        foreach ($words as $i => $word) {
            $w = strtolower(trim($word));
            switch ($i % 5) {
                case 0:
                    $sentences[] = "Every day, {$char} practices the word \"{$w}\" to become a better reader.";
                    break;
                case 1:
                    $sentences[] = "The teacher said that the word \"{$w}\" is important and must be learned well.";
                    break;
                case 2:
                    $sentences[] = "After practicing, {$char} finally understood how to read the word \"{$w}\" correctly.";
                    break;
                case 3:
                    $sentences[] = "{$char} wrote the word \"{$w}\" in a notebook ten times until it felt easy.";
                    break;
                case 4:
                    $sentences[] = "Now {$char} can read the word \"{$w}\" without any difficulty at all.";
                    break;
            }
        }

        $closings = [
            "{$char}'s parents were so proud of the hard work and dedication shown every single day.",
            "Because of all the effort, {$char} became one of the best readers in the whole class.",
            "{$char} kept reading every day, knowing that practice is the key to success.",
        ];
        $sentences[] = $closings[array_sum(array_map('ord', str_split(implode('', $words)))) % count($closings)];

        if ($grade >= 3 && $count > 0) {
            $wordList    = implode(', ', $words);
            $sentences[] = "Keep practicing these words: \"{$wordList}\" — you are doing a great job!";
        }

        return implode(' ', $sentences);
    }
}