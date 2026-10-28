<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function index(): View
    {
        return view('levels.index', ['levels' => MembershipLevel::withCount('members')->orderBy('fee')->get()]);
    }

    public function create(): View
    {
        return view('levels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        MembershipLevel::create($this->validated($request));

        return redirect()->route('levels.index')->with('success', 'Membership level created.');
    }

    public function edit(MembershipLevel $membershipLevel): View
    {
        return view('levels.edit', ['level' => $membershipLevel]);
    }

    public function update(Request $request, MembershipLevel $membershipLevel): RedirectResponse
    {
        $membershipLevel->update($this->validated($request, $membershipLevel));

        return redirect()->route('levels.index')->with('success', 'Membership level updated.');
    }

    public function destroy(MembershipLevel $membershipLevel): RedirectResponse
    {
        if ($membershipLevel->members()->exists()) {
            return back()->with('error', 'This level cannot be deleted while members are assigned to it.');
        }

        $membershipLevel->delete();

        return back()->with('success', 'Membership level deleted.');
    }

    private function validated(Request $request, ?MembershipLevel $level = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_levels', 'name')->ignore($level?->id)],
            'fee' => ['required', 'numeric', 'min:300.01'],
            'validity_months' => ['required', 'integer', 'min:1', 'max:120'],
            'benefits' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
