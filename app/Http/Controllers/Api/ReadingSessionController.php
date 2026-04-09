<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingSession;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Events\SessionCompleted;
use App\Jobs\GenerateRemedialStory;


class ReadingSessionController extends Controller
{
    // List all sessions for a student
    public function index(Student $student)
    {
        $sessions = $student->readingSessions()
            ->latest()
            ->get();

        return response()->json($sessions);
    }

    // Assign a passage / create a session
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'passage' => 'required|string',
        ]);

        $session = $student->readingSessions()->create([
            'teacher_id' => auth('api')->id(),
            'passage'    => $request->passage,
            'status'     => 'pending',
        ]);

        return response()->json($session, 201);
    }

    // Submit transcript + errors after student reads
    public function submit(Request $request, Student $student, ReadingSession $session)
    {
        $request->validate([
            'transcript'     => 'required|string',
            'error_patterns' => 'nullable|array',
        ]);

        // Calculate accuracy
        $passageWords  = str_word_count($session->passage);
        $errorCount    = count($request->error_patterns ?? []);
        $accuracyScore = max(0, round((($passageWords - $errorCount) / $passageWords) * 100));

        $session->update([
            'transcript'     => $request->transcript,
            'error_patterns' => $request->error_patterns ?? [],
            'accuracy_score' => $accuracyScore,
            'status'         => 'completed',
        ]);

        broadcast(new SessionCompleted($session->fresh()->load('student')))->toOthers();
        
        GenerateRemedialStory::dispatch($session->fresh()->load('student'));

        return response()->json([
            'session'        => $session->fresh(),
            'accuracy_score' => $accuracyScore,
            'message'        => 'Session submitted successfully',
        ]);
    }

    // Teacher approves and levels up the student
    public function approve(Student $student, ReadingSession $session)
    {
        if ($session->status !== 'completed') {
            return response()->json(['message' => 'Session is not completed yet'], 422);
        }

        // Get current reading level threshold
        $level     = \App\Models\ReadingLevel::where('level', $student->reading_level)->first();
        $threshold = $level?->accuracy_threshold ?? 80;

        if ($session->accuracy_score < $threshold) {
            return response()->json([
                'message'   => 'Accuracy too low to level up',
                'required'  => $threshold,
                'actual'    => $session->accuracy_score,
            ], 422);
        }

        // Check 3 consecutive sessions meet threshold
        $recentSessions = $student->readingSessions()
            ->where('status', 'completed')
            ->latest()
            ->take(3)
            ->get();

        $allPass = $recentSessions->count() >= 3
            && $recentSessions->every(fn($s) => $s->accuracy_score >= $threshold);

        if (!$allPass) {
            return response()->json([
                'message' => 'Student needs 3 consecutive passing sessions to level up',
                'passing_sessions' => $recentSessions->where('accuracy_score', '>=', $threshold)->count(),
            ], 422);
        }

        // Level up!
        $session->update(['status' => 'approved']);
        $student->increment('reading_level');

        return response()->json([
            'message'       => 'Student leveled up!',
            'new_level'     => $student->fresh()->reading_level,
        ]);
    }

    public function stories(Student $student)
    {
        $stories = \App\Models\GeneratedStory::where('student_id', $student->id)
            ->latest()
            ->get();

        return response()->json($stories);
    }

    public function show(Student $student, ReadingSession $session)
    {
        return response()->json($session);
    }
}