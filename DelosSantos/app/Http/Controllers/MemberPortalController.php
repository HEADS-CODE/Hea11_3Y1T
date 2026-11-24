<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function membership(): View
    {
        $member = auth()->user()->member()->with(['category', 'level', 'latestTransaction'])->firstOrFail();

        return view('member.membership', compact('member'));
    }

    public function categories(): View
    {
        $member = auth()->user()->member;
        $categories = MembershipCategory::where('is_active', true)->orderBy('name')->get();

        return view('member.categories', ['categories' => $categories, 'currentCategoryId' => $member?->membership_category_id]);
    }

    public function levels(): View
    {
        $member = auth()->user()->member;
        $levels = MembershipLevel::where('is_active', true)->orderBy('fee')->get();

        return view('member.levels', ['levels' => $levels, 'currentLevelId' => $member?->membership_level_id]);
    }
}
