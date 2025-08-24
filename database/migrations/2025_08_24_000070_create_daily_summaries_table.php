<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('summary_date');
            $table->unsignedInteger('total_focus_seconds')->default(0);
            $table->json('completed_task_ids')->nullable();
            $table->longText('markdown_body');
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->unique(['user_id', 'summary_date']);
            $table->index(['summary_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
