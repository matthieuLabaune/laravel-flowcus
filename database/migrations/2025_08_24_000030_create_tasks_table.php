<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('status', 20)->default(TaskStatus::Pending->value);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['project_id']);
            $table->index(['deadline_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
