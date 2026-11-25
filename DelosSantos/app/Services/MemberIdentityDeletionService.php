<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberIdentityDeletionService
{
    public function findReleasableByEmail(string $email): ?Member
    {
        $normalizedEmail = strtolower(trim($email));

        return Member::with('user')
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->whereNull('archived_at')
            ->where('status', 'inactive')
            ->whereHas('transactions', function ($query): void {
                $query->where('type', \App\Models\MembershipTransaction::TYPE_APPLICATION)
                    ->where('status', \App\Models\MembershipTransaction::STATUS_CANCELLED);
            })
            ->whereDoesntHave('transactions', function ($query): void {
                $query->where('status', \App\Models\MembershipTransaction::STATUS_APPROVED)
                    ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', 'paid'));
            })
            ->first();
    }

    public function delete(
        Member $member,
        ?User $actor = null,
        string $auditAction = 'member.deleted',
        array $additionalAuditDetails = [],
    ): void {
        $member->loadMissing(['user', 'documents']);

        $transactions = $member->transactions()->get();
        $documents = $member->documents;
        $user = $member->user;

        AuditLog::record($auditAction, array_merge([
            'member_id' => $member->id,
            'membership_number' => $member->membership_number,
            'transaction_count_preserved' => $transactions->count(),
            'document_count_removed' => $documents->count(),
        ], $additionalAuditDetails), $member, $actor);

        foreach ($documents as $document) {
            Storage::disk('member_private')->delete($document->storage_path);
        }

        // Financial history remains available through immutable snapshots, but no longer points to the deleted profile.
        $member->transactions()->update(['member_id' => null]);
        $member->requests()->delete();
        $member->tasks()->delete();

        if ($user) {
            // Reset tokens and database sessions are not foreign-keyed to the user record.
            DB::table('member_password_reset_tokens')->where('email', $user->email)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $user->notifications()->delete();
            $user->delete();
        }

        $member->delete();
    }
}
