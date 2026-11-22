<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('membership_category_id')->constrained('membership_categories')->restrictOnDelete();
            $table->foreignId('membership_level_id')->constrained('membership_levels')->restrictOnDelete();
            $table->string('reference_number', 40)->unique();
            $table->string('type', 30)->default('application');
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 10, 2);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_transaction_id')->unique()->constrained('membership_transactions')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_reference', 40)->unique();
            $table->string('payment_method', 30);
            $table->string('status', 20)->default('paid');
            $table->timestamp('paid_at');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('membership_transactions');
    }
};
