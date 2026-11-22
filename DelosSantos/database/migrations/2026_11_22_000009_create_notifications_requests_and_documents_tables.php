<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('member_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('request_type', 40);
            $table->string('source', 20)->default('online');
            $table->string('status', 20)->default('pending');
            $table->json('previous_values')->nullable();
            $table->json('proposed_values')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->string('email_reverification_hash', 64)->nullable();
            $table->timestamp('email_reverification_expires_at')->nullable();
            $table->timestamp('email_reverified_at')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'status']);
            $table->index(['request_type', 'status']);
        });

        Schema::create('member_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_request_id')->constrained('member_requests')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 50);
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamp('privacy_consented_at');
            $table->timestamps();
            $table->index(['member_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_documents');
        Schema::dropIfExists('member_requests');
        Schema::dropIfExists('notifications');
    }
};
