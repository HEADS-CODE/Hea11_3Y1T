<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('created_by_user_id')->nullable()->after('member_id')->constrained('users')->nullOnDelete();
            $table->string('status', 25)->default('assigned')->after('description');
            $table->dateTime('deadline')->nullable()->after('status');
            $table->timestamp('published_at')->nullable()->after('deadline');
            $table->timestamp('archived_at')->nullable()->after('published_at');
            $table->index(['member_id', 'status']);
            $table->index(['deadline', 'archived_at']);
        });

        DB::table('tasks')->where('completed', true)->update(['status' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['created_by_user_id']);
            $table->dropIndex(['member_id', 'status']);
            $table->dropIndex(['deadline', 'archived_at']);
            $table->dropColumn(['created_by_user_id', 'status', 'deadline', 'published_at', 'archived_at']);
        });
    }
};
