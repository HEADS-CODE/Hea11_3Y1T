<?php

namespace App\Services;

use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class MembershipTransactionService
{
    public function create(array $attributes): MembershipTransaction
    {
        $level = $attributes['level'];
        $member = $attributes['member'];

        return MembershipTransaction::create([
            'member_id' => $member->id,
            'membership_category_id' => $attributes['category']->id,
            'membership_level_id' => $level->id,
            'reference_number' => $this->reference('TXN'),
            'type' => $attributes['type'],
            'status' => MembershipTransaction::STATUS_PENDING,
            'amount' => $level->fee,
            'created_by_user_id' => $attributes['created_by_user_id'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ]);
    }

    public function recordFullPayment(MembershipTransaction $transaction, string $method, ?int $userId = null): Payment
    {
        $transaction->loadMissing('level', 'payment');

        if ($transaction->status !== MembershipTransaction::STATUS_PENDING || $transaction->effectiveStatus() !== MembershipTransaction::STATUS_PENDING) {
            throw ValidationException::withMessages(['transaction' => 'Only a pending transaction without a payment can be paid.']);
        }

        if (! in_array($method, array_merge(MembershipTransaction::ONLINE_CHANNELS, [MembershipTransaction::CHANNEL_FRONT_DESK]), true)) {
            throw ValidationException::withMessages(['payment_channel' => 'Choose a valid payment channel.']);
        }

        if ($transaction->payment) {
            throw ValidationException::withMessages(['amount' => 'This transaction already has a payment.']);
        }

        if (number_format((float) $transaction->amount, 2, '.', '')
            !== number_format((float) $transaction->level->fee, 2, '.', '')) {
            throw ValidationException::withMessages(['amount' => 'The payment must equal the selected level fee.']);
        }

        return DB::transaction(function () use ($transaction, $method, $userId): Payment {
            $payment = Payment::create([
                'membership_transaction_id' => $transaction->id,
                'amount' => $transaction->level->fee,
                'payment_reference' => $this->reference('PAY'),
                'payment_method' => $method,
                'status' => 'paid',
                'paid_at' => now(),
                'recorded_by_user_id' => $userId,
            ]);
            $transaction->update([
                'status' => MembershipTransaction::STATUS_PAID,
                'payment_channel' => $method,
            ]);

            return $payment;
        });
    }

    public function markFrontDeskRequested(MembershipTransaction $transaction): void
    {
        $transaction->loadMissing('payment');

        if ($transaction->status !== MembershipTransaction::STATUS_PENDING || $transaction->payment) {
            throw ValidationException::withMessages(['transaction' => 'Only a pending transaction without a payment can be sent to the front desk.']);
        }

        $transaction->update([
            'payment_channel' => MembershipTransaction::CHANNEL_FRONT_DESK,
            'notes' => trim(implode(' ', array_filter([
                $transaction->notes,
                'Awaiting front-desk payment.',
            ]))),
        ]);
    }

    public function approve(MembershipTransaction $transaction, int $userId): void
    {
        $transaction->loadMissing('level', 'payment');

        if (! $transaction->canBeApproved()) {
            throw ValidationException::withMessages(['transaction' => 'Only a fully paid transaction can be approved.']);
        }

        $startDate = now();

        if ($transaction->type === MembershipTransaction::TYPE_RENEWAL) {
            $previous = $transaction->member?->transactions()
                ->where('id', '!=', $transaction->id)
                ->where('status', MembershipTransaction::STATUS_APPROVED)
                ->whereDate('ends_at', '>=', today())
                ->latest('ends_at')
                ->first();

            if ($previous?->ends_at) {
                $startDate = $previous->ends_at->copy()->addDay()->startOfDay();
            }
        }

        $start = $startDate->toDateString();
        $end = $startDate->copy()->addMonths((int) $transaction->level->validity_months)->subDay()->toDateString();
        $transaction->update([
            'status' => MembershipTransaction::STATUS_APPROVED,
            'starts_at' => $start,
            'ends_at' => $end,
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
        ]);
    }

    private function reference(string $prefix): string
    {
        do {
            $reference = $prefix . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        } while (MembershipTransaction::where('reference_number', $reference)->exists() || Payment::where('payment_reference', $reference)->exists());

        return $reference;
    }
}
