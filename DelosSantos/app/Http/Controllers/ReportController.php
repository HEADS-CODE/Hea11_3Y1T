<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReport($request);

        return view('reports.index', $data + [
            'categories' => MembershipCategory::query()->orderBy('name')->get(),
            'levels' => MembershipLevel::query()->orderBy('fee')->get(),
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->buildReport($request);
        $filename = 'puihaha-'.$data['type'].'-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_values($data['columns']));

            foreach ($data['rows'] as $row) {
                fputcsv($handle, array_map(fn (string $key) => $row[$key] ?? '', array_keys($data['columns'])));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReport(Request $request): array
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:members,transactions,payments,expiring'],
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:30'],
            'category_id' => ['nullable', 'integer'],
            'level_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $filters['type'] = $filters['type'] ?? 'members';

        return match ($filters['type']) {
            'transactions' => $this->transactionsReport($filters),
            'payments' => $this->paymentsReport($filters),
            'expiring' => $this->expiringReport($filters),
            default => $this->membersReport($filters),
        } + ['filters' => $filters];
    }

    private function membersReport(array $filters): array
    {
        $query = Member::query()->with(['category', 'level'])->latest('id');
        $this->applyMemberFilters($query, $filters);

        $rows = $query->get()->map(fn (Member $member): array => [
            'membership_number' => $member->membership_number,
            'name' => $member->full_name,
            'email' => $member->email,
            'category' => $member->category?->name ?? '—',
            'level' => $member->level?->name ?? '—',
            'status' => ucfirst((string) $member->status),
            'joined' => $member->joined_at?->format('M d, Y') ?? '—',
        ])->all();

        return [
            'type' => 'members',
            'title' => 'Member report.',
            'description' => 'A filtered directory of member profiles, assignments, and current account states.',
            'columns' => [
                'membership_number' => 'Membership no.',
                'name' => 'Member',
                'email' => 'Email',
                'category' => 'Category',
                'level' => 'Level',
                'status' => 'Status',
                'joined' => 'Joined',
            ],
            'rows' => $rows,
        ];
    }

    private function transactionsReport(array $filters): array
    {
        $query = MembershipTransaction::query()->with(['member', 'category', 'level'])->latest('id');
        $this->applyTransactionFilters($query, $filters);

        $rows = $query->get()->map(fn (MembershipTransaction $transaction): array => [
            'reference' => $transaction->reference_number,
            'member' => $transaction->member_display_name,
            'type' => ucwords(str_replace('_', ' ', $transaction->type)),
            'level' => $transaction->level_display_name,
            'amount' => '₱'.number_format((float) $transaction->amount, 2),
            'status' => ucfirst($transaction->effectiveStatus()),
            'created' => $transaction->created_at?->format('M d, Y') ?? '—',
        ])->all();

        return [
            'type' => 'transactions',
            'title' => 'Transaction report.',
            'description' => 'Review membership applications, renewals, level changes, amounts, and statuses.',
            'columns' => [
                'reference' => 'Reference',
                'member' => 'Member',
                'type' => 'Type',
                'level' => 'Level',
                'amount' => 'Amount',
                'status' => 'Status',
                'created' => 'Created',
            ],
            'rows' => $rows,
        ];
    }

    private function paymentsReport(array $filters): array
    {
        $query = Payment::query()->with(['transaction.member', 'transaction.level'])->latest('id');
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $this->applyDateFilters($query, $filters, 'paid_at');

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($subquery) use ($search): void {
                $subquery->where('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('transaction', function ($transactionQuery) use ($search): void {
                        $transactionQuery->where('member_name_snapshot', 'like', "%{$search}%")
                            ->orWhere('membership_number_snapshot', 'like', "%{$search}%")
                            ->orWhereHas('member', function ($memberQuery) use ($search): void {
                                $memberQuery->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('membership_number', 'like', "%{$search}%");
                            });
                    });
            });
        }
        if (! empty($filters['category_id'])) {
            $query->whereHas('transaction', fn ($transactionQuery) => $transactionQuery->where('membership_category_id', $filters['category_id']));
        }
        if (! empty($filters['level_id'])) {
            $query->whereHas('transaction', fn ($transactionQuery) => $transactionQuery->where('membership_level_id', $filters['level_id']));
        }

        $rows = $query->get()->map(fn (Payment $payment): array => [
            'payment_reference' => $payment->payment_reference,
            'transaction' => $payment->transaction?->reference_number ?? '—',
            'member' => $payment->transaction?->member_display_name ?? '—',
            'amount' => '₱'.number_format((float) $payment->amount, 2),
            'method' => ucwords(str_replace('_', ' ', $payment->payment_method)),
            'status' => ucfirst((string) $payment->status),
            'paid_at' => $payment->paid_at?->format('M d, Y h:i A') ?? '—',
        ])->all();

        return [
            'type' => 'payments',
            'title' => 'Payment report.',
            'description' => 'A traceable list of recorded payments and their linked membership transactions.',
            'columns' => [
                'payment_reference' => 'Payment reference',
                'transaction' => 'Transaction',
                'member' => 'Member',
                'amount' => 'Amount',
                'method' => 'Method',
                'status' => 'Status',
                'paid_at' => 'Paid at',
            ],
            'rows' => $rows,
        ];
    }

    private function expiringReport(array $filters): array
    {
        $from = $filters['from'] ?? today()->toDateString();
        $to = $filters['to'] ?? today()->addDays(30)->toDateString();
        $query = MembershipTransaction::query()
            ->with(['member', 'level'])
            ->where('status', MembershipTransaction::STATUS_APPROVED)
            ->whereBetween('ends_at', [$from, $to])
            ->latest('ends_at');
        if (! empty($filters['category_id'])) {
            $query->where('membership_category_id', $filters['category_id']);
        }
        if (! empty($filters['level_id'])) {
            $query->where('membership_level_id', $filters['level_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->whereHas('member', function ($memberQuery) use ($search): void {
                $memberQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%");
            });
        }

        $rows = $query->get()->map(fn (MembershipTransaction $transaction): array => [
            'member' => $transaction->member_display_name,
            'membership_number' => $transaction->membership_number_display,
            'level' => $transaction->level_display_name,
            'ends_at' => $transaction->ends_at?->format('M d, Y') ?? '—',
            'days_left' => $transaction->ends_at ? (string) max(0, today()->diffInDays($transaction->ends_at, false)) : '—',
            'reference' => $transaction->reference_number,
        ])->all();

        return [
            'type' => 'expiring',
            'title' => 'Expiring memberships.',
            'description' => 'Approved memberships ending within the selected window, ready for renewal follow-up.',
            'columns' => [
                'member' => 'Member',
                'membership_number' => 'Membership no.',
                'level' => 'Level',
                'ends_at' => 'Ends',
                'days_left' => 'Days left',
                'reference' => 'Reference',
            ],
            'rows' => $rows,
        ];
    }

    private function applyMemberFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('membership_category_id', $filters['category_id']);
        }
        if (! empty($filters['level_id'])) {
            $query->where('membership_level_id', $filters['level_id']);
        }
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($subquery) use ($search): void {
                $subquery->where('membership_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
    }

    private function applyTransactionFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('membership_category_id', $filters['category_id']);
        }
        if (! empty($filters['level_id'])) {
            $query->where('membership_level_id', $filters['level_id']);
        }
        $this->applyDateFilters($query, $filters, 'created_at');
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($transactionQuery) use ($search): void {
                $transactionQuery->where('member_name_snapshot', 'like', "%{$search}%")
                    ->orWhere('membership_number_snapshot', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($memberQuery) use ($search): void {
                        $memberQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('membership_number', 'like', "%{$search}%");
                    });
            });
        }
    }

    private function applyDateFilters($query, array $filters, string $column): void
    {
        if (! empty($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }
    }
}
