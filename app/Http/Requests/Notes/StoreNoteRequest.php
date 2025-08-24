<?php

declare(strict_types=1);

namespace App\Http\Requests\Notes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $allowedTypes = [
            'App\\Models\\Task',
            'App\\Models\\Project',
            'App\\Models\\PomodoroSession'
        ];

        return [
            'content' => ['required', 'string', 'max:65535'],
            'noteable_type' => [
                'required',
                'string',
                Rule::in($allowedTypes),
            ],
            'noteable_id' => ['required', 'integer', 'exists:' . $this->getTableFromType()],
        ];
    }

    /**
     * Get the table name from the noteable_type.
     */
    private function getTableFromType(): string
    {
        return match ($this->input('noteable_type')) {
            'App\\Models\\Task' => 'tasks,id',
            'App\\Models\\Project' => 'projects,id',
            'App\\Models\\PomodoroSession' => 'pomodoro_sessions,id',
            default => 'tasks,id',
        };
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Note content is required.',
            'content.max' => 'Note content cannot exceed 65535 characters.',
            'noteable_type.in' => 'Invalid note type.',
            'noteable_id.exists' => 'The selected item does not exist.',
        ];
    }
}
