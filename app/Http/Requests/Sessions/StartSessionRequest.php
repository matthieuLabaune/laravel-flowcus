<?php

declare(strict_types=1);

namespace App\Http\Requests\Sessions;

use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // auth middleware already applied
    }

    public function rules(): array
    {
        return [
            'planned_seconds' => ['required', 'integer', 'min:60', 'max:7200'],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
        ];
    }
}
