<?php

namespace App\Events;

use App\Models\ReadingSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ReadingSession $session)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('teacher.' . $this->session->teacher_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'session_id'     => $this->session->id,
            'student_id'     => $this->session->student_id,
            'student_name'   => $this->session->student->name,
            'accuracy_score' => $this->session->accuracy_score,
            'error_patterns' => $this->session->error_patterns,
            'status'         => $this->session->status,
            'alert_color'    => $this->getAlertColor(),
        ];
    }

    private function getAlertColor(): string
    {
        return match(true) {
            $this->session->accuracy_score >= 80 => 'green',
            $this->session->accuracy_score >= 60 => 'yellow',
            default                              => 'red',
        };
    }
}