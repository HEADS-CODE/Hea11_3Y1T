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
            $table->string('member_name_snapshot')->nullable()->after('member_id');
            $table->string('membership_number_snapshot', 7)->nullable()->after('member_name_snapshot');
            $table->string('category_name_snapshot')->nullable()->after('membership_number_snapshot');
            $table->string('level_name_snapshot')->nullable()->after('category_name_snapshot');
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->dropForeign(['member_id']);
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->unsignedBigInteger('member_id')->nullable()->change();
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });

        DB::table('membership_transactions')
            ->orderBy('id')
            ->get()
            ->each(function (object $transaction): void {
                $member = $transaction->member_id
                    ? DB::table('members')->where('id', $transaction->member_id)->first()
                    : null;
                $category = DB::table('membership_categories')->where('id', $transaction->membership_category_id)->first();
                $level = DB::table('membership_levels')->where('id', $transaction->membership_level_id)->first();

                DB::table('membership_transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'member_name_snapshot' => $member ? trim(implode(' ', array_filter([
                            $member->first_name,
                            $member->middle_name,
                            $member->last_name,
                        ]))) : null,
                        'membership_number_snapshot' => $member?->membership_number,
                        'category_name_snapshot' => $category?->name,
                        'level_name_snapshot' => $level?->name,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->dropForeign(['member_id']);
        });

        Schema::table('membership_transactions', function (Blueprint $table): void {
            $table->unsignedBigInteger('member_id')->nullable(false)->change();
            $table->foreign('member_id')->references('id')->on('members')->restrictOnDelete();
            $table->dropColumn([
                'member_name_snapshot',
                'membership_number_snapshot',
                'category_name_snapshot',
                'level_name_snapshot',
            ]);
        });
    }
};
