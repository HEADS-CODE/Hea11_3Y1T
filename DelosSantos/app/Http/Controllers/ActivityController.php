<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();
        $query->when($request->filled('actor'), function ($q) use ($request): void {
            $actor = $request->string('actor')->toString();
            $q->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$actor}%")->orWhere('email', 'like', "%{$actor}%"));
        });
        $query->when($request->filled('role'), fn ($q) => $q->whereHas('user', fn ($userQuery) => $userQuery->where('role', $request->string('role')->toString())));
        $query->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%'.$request->string('action')->toString().'%'));
        $query->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')));
        $query->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')));

        return view('admin.activity', ['logs' => $query->paginate(25)->withQueryString()]);
    }
}
