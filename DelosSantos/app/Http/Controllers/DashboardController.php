<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipTransaction;
use App\Models\Payment;
use App\Models\AuditLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'active')->whereNull('archived_at')->whereHas('transactions', fn ($query) => $query->where('status', MembershipTransaction::STATUS_APPROVED)->whereDate('ends_at', '>=', today()))->count(),
            'pending_transactions' => MembershipTransaction::whereIn('status', [MembershipTransaction::STATUS_PENDING, MembershipTransaction::STATUS_PAID])->count(),
            'collected_payments' => Payment::where('status', 'paid')->sum('amount'),
        ];
        $recentTransactions = MembershipTransaction::with(['member', 'level', 'payment'])->latest()->limit(8)->get();
        $recentActivity = AuditLog::with('user')->latest()->limit(10)->get();
        $adminProfile = auth()->user()->adminProfile;
        $hasSavedLocation = is_numeric($adminProfile?->latitude) && is_numeric($adminProfile?->longitude);
        $weatherLocation = [
            'latitude' => $hasSavedLocation ? $adminProfile->latitude : config('membership.weather.default_latitude'),
            'longitude' => $hasSavedLocation ? $adminProfile->longitude : config('membership.weather.default_longitude'),
            'label' => $hasSavedLocation ? 'Your saved office location' : 'Configured office location',
            'uses_default' => ! $hasSavedLocation,
        ];

        return view('dashboard.index', compact('stats', 'recentTransactions', 'recentActivity', 'weatherLocation'));
    }
}
