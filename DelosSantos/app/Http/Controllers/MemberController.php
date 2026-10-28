<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Member::with(['category', 'level'])->latest('joined_at')->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($memberQuery) use ($search) {
                $memberQuery->where('membership_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->when($request->filled('category_id'), fn ($q) => $q->where('membership_category_id', $request->integer('category_id')))
            ->when($request->filled('level_id'), fn ($q) => $q->where('membership_level_id', $request->integer('level_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()));

        $members = $query->paginate(10)->withQueryString();
        $categories = MembershipCategory::orderBy('name')->get();
        $levels = MembershipLevel::orderBy('fee')->get();
        $stats = [
            'total' => Member::count(),
            'active' => Member::where('status', 'active')->count(),
            'inactive' => Member::where('status', 'inactive')->count(),
            'categories' => MembershipCategory::count(),
        ];

        return view('members.index', compact('members', 'categories', 'levels', 'stats'));
    }

    public function create(): View
    {
        return view('members.create', [
            'categories' => MembershipCategory::where('is_active', true)->orderBy('name')->get(),
            'levels' => MembershipLevel::where('is_active', true)->orderBy('fee')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['membership_number'] = $this->generateMembershipNumber();

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Member registered successfully.');
    }

    public function show(Member $member): View
    {
        $member->load(['category', 'level']);

        return view('members.show', compact('member'));
    }

    public function edit(Member $member): View
    {
        return view('members.edit', [
            'member' => $member,
            'categories' => MembershipCategory::orderBy('name')->get(),
            'levels' => MembershipLevel::orderBy('fee')->get(),
        ]);
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $this->validated($request, $member);
        $member->update($validated);

        return redirect()->route('members.show', $member)->with('success', 'Member details updated.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member removed from the directory.');
    }

    public function toggleStatus(Member $member): RedirectResponse
    {
        $member->update(['status' => $member->status === 'active' ? 'inactive' : 'active']);
        $message = $member->status === 'active' ? 'Member reactivated.' : 'Member deactivated.';

        return back()->with('success', $message);
    }

    private function validated(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'joined_at' => ['required', 'date'],
        ]);
    }

    private function generateMembershipNumber(): string
    {
        do {
            $number = '17' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (Member::where('membership_number', $number)->exists());

        return $number;
    }
}
