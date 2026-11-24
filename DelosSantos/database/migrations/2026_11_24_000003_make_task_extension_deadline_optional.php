<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_extension_requests', function (Blueprint $table): void {
            $table->dateTime('requested_deadline')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('task_extension_requests', function (Blueprint $table): void {
            $table->dateTime('requested_deadline')->nullable(false)->change();
        });
    }
};
