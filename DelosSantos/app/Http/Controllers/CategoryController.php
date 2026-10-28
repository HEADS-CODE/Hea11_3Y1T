<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', ['categories' => MembershipCategory::withCount('members')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        MembershipCategory::create($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Membership category created.');
    }

    public function edit(MembershipCategory $membershipCategory): View
    {
        return view('categories.edit', ['category' => $membershipCategory]);
    }

    public function update(Request $request, MembershipCategory $membershipCategory): RedirectResponse
    {
        $membershipCategory->update($this->validated($request, $membershipCategory));

        return redirect()->route('categories.index')->with('success', 'Membership category updated.');
    }

    public function destroy(MembershipCategory $membershipCategory): RedirectResponse
    {
        if ($membershipCategory->members()->exists()) {
            return back()->with('error', 'This category cannot be deleted while members are assigned to it.');
        }

        $membershipCategory->delete();

        return back()->with('success', 'Membership category deleted.');
    }

    private function validated(Request $request, ?MembershipCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
