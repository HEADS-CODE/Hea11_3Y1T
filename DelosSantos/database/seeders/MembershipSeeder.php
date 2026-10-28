<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Student', 'description' => 'For currently enrolled students with a valid school identification card.', 'is_active' => true],
            ['name' => 'Regular', 'description' => 'The standard membership for individual learners.', 'is_active' => true],
            ['name' => 'Senior Citizen', 'description' => 'For senior learners who want to continue learning at their own pace.', 'is_active' => true],
            ['name' => 'Corporate', 'description' => 'For organizations managing learning access for their team members.', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            MembershipCategory::updateOrCreate(['name' => $category['name']], $category);
        }

        $levels = [
            ['name' => 'Basic', 'fee' => 399, 'validity_months' => 1, 'benefits' => 'Digital membership card and access to introductory learning materials.', 'is_active' => true],
            ['name' => 'Silver', 'fee' => 699, 'validity_months' => 3, 'benefits' => 'Basic benefits plus curated learning paths and progress tracking.', 'is_active' => true],
            ['name' => 'Gold', 'fee' => 999, 'validity_months' => 6, 'benefits' => 'Silver benefits plus priority learning support and completion certificates.', 'is_active' => true],
            ['name' => 'Platinum', 'fee' => 1499, 'validity_months' => 12, 'benefits' => 'All benefits plus premium resources, priority support, and exclusive workshops.', 'is_active' => true],
        ];

        foreach ($levels as $level) {
            MembershipLevel::updateOrCreate(['name' => $level['name']], $level);
        }
    }
}
