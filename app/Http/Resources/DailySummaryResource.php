<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DailySummary */
class DailySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'summary_date' => $this->summary_date,
            'total_focus_seconds' => $this->total_focus_seconds,
            'completed_task_ids' => $this->completed_task_ids,
            'markdown_body' => $this->markdown_body,
            'generated_at' => $this->generated_at,
        ];
    }
}
