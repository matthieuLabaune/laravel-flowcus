<?php

declare(strict_types=1);

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'color' => ['sometimes', 'nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
        ];
    }
}
