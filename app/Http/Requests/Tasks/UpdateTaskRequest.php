<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // auth middleware
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'deadline_at' => ['sometimes', 'nullable', 'date'],
            'project_id' => ['sometimes', 'nullable', 'integer', 'exists:projects,id'],
        ];
    }
}
