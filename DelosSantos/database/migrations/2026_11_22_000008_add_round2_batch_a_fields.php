<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->string('payment_channel', 30)->nullable()->after('status');
            $table->text('cancel_reason')->nullable()->after('notes');
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->timestamp('completed_at')->nullable()->after('completed');
        });

        Schema::table('members', function (Blueprint $table): void {
            $table->string('gender', 30)->nullable()->after('last_name');
            $table->string('unit_floor_building')->nullable()->after('address');
            $table->string('house_street')->nullable()->after('unit_floor_building');
            $table->string('barangay')->nullable()->after('house_street');
            $table->string('city_municipality')->nullable()->after('barangay');
            $table->string('province')->nullable()->after('city_municipality');
            $table->string('zip_code', 10)->nullable()->after('province');
        });

        Schema::create('admin_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 30);
            $table->text('address')->nullable();
            $table->string('unit_floor_building')->nullable();
            $table->string('house_street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_profiles');

        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn([
                'gender', 'unit_floor_building', 'house_street', 'barangay',
                'city_municipality', 'province', 'zip_code',
            ]);
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn('completed_at');
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->dropColumn(['payment_channel', 'cancel_reason']);
        });
    }
};
