<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\UserCv;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserCvStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public UserCv $cv,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->cv->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'cv.status.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $status = is_string($this->cv->status) ? $this->cv->status : $this->cv->status->value;

        return [
            'cv' => [
                'id' => $this->cv->id,
                'name' => $this->cv->original_name,
                'status' => $status,
                'is_active' => $this->cv->is_active,
                'size' => $this->cv->size,
                'created_at' => $this->cv->created_at?->toIso8601String(),
            ],
        ];
    }
}
