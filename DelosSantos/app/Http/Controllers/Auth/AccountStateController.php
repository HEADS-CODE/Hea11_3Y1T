<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountStateController extends Controller
{
    public function verificationNotice(): View
    {
        return view('auth.verification-notice');
    }

    public function approvalPending(): View
    {
        $transaction = auth()->user()->member?->transactions()->with(['category', 'level'])->latest()->first();

        return view('auth.approval-pending', compact('transaction'));
    }

    public function inactiveNotice(): View
    {
        $request = auth()->user()->member?->requests()->latest()->first();

        return view('auth.inactive-account', [
            'request' => $request,
            'requirements' => config('membership.reactivation_requirements'),
        ]);
    }
}
