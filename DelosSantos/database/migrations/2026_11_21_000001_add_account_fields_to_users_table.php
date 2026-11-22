<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
            $table->string('role', 20)->default('member')->after('member_id');
            $table->string('status', 20)->default('active')->after('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->unique(['email', 'role'], 'users_email_role_unique');
            $table->unique('member_id', 'users_member_id_unique');
        });

        DB::table('users')
            ->where('email', 'test@example.com')
            ->update([
                'role' => 'member',
                'status' => 'disabled',
                'member_id' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_role_unique');
            $table->dropUnique('users_member_id_unique');
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'role', 'status']);
            $table->unique('email');
        });
    }
};
