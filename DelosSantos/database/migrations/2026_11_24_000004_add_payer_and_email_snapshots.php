<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->string('member_email_snapshot')->nullable()->after('member_name_snapshot');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->timestamp('paid_at')->nullable()->change();
            $table->string('payer_name')->nullable()->after('amount');
            $table->string('payer_account_number', 50)->nullable()->after('payer_name');
            $table->text('payer_address')->nullable()->after('payer_account_number');
            $table->decimal('payer_latitude', 10, 7)->nullable()->after('payer_address');
            $table->decimal('payer_longitude', 10, 7)->nullable()->after('payer_latitude');
        });

        DB::table('membership_transactions')
            ->whereNull('member_email_snapshot')
            ->orderBy('id')
            ->get()
            ->each(function (object $transaction): void {
                $email = $transaction->member_id
                    ? DB::table('members')->where('id', $transaction->member_id)->value('email')
                    : null;

                DB::table('membership_transactions')
                    ->where('id', $transaction->id)
                    ->update(['member_email_snapshot' => $email]);
            });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['payer_name', 'payer_account_number', 'payer_address', 'payer_latitude', 'payer_longitude']);
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->dropColumn('member_email_snapshot');
        });
    }
};
