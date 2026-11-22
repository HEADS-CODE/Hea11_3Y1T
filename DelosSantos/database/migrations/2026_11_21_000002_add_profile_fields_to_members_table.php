<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->after('first_name');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->timestamp('archived_at')->nullable()->after('joined_at');
            $table->boolean('is_legacy_test_data')->default(false)->after('archived_at');
        });

        DB::table('members')
            ->whereIn('id', [1, 2])
            ->update(['is_legacy_test_data' => true]);
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'middle_name',
                'latitude',
                'longitude',
                'archived_at',
                'is_legacy_test_data',
            ]);
        });
    }
};
