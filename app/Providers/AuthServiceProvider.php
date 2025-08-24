<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Note;
use App\Policies\TaskPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\NotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Note::class => NotePolicy::class,
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
