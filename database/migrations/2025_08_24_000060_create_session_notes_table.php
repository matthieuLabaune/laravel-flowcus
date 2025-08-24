<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('session_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pomodoro_session_id')->constrained('pomodoro_sessions')->cascadeOnDelete();
            $table->mediumText('body');
            $table->timestamps();
            $table->index(['pomodoro_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_notes');
    }
};
