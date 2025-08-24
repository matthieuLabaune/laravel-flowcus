<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PomodoroSession */
class PomodoroSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'type' => $this->type->value,
            'planned_seconds' => $this->planned_seconds,
            'actual_seconds' => $this->actual_seconds,
            'interruptions_count' => $this->interruptions_count,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
        ];
    }
}
