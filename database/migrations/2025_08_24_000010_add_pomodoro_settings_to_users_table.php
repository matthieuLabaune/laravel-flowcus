<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedSmallInteger('pomodoro_length')->default(25)->after('remember_token');
            $table->unsignedSmallInteger('short_break_length')->default(5)->after('pomodoro_length');
            $table->unsignedSmallInteger('long_break_length')->default(15)->after('short_break_length');
            $table->unsignedTinyInteger('long_break_interval')->default(4)->after('long_break_length');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'pomodoro_length',
                'short_break_length',
                'long_break_length',
                'long_break_interval',
            ]);
        });
    }
};
