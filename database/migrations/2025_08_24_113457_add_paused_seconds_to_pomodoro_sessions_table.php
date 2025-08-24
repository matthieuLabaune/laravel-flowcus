<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->integer('paused_seconds')->default(0)->after('interruptions_count');
            $table->timestamp('last_paused_at')->nullable()->after('paused_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->dropColumn(['paused_seconds', 'last_paused_at']);
        });
    }
};
