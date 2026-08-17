<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlanAudience;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Starter plans. These are just data — the dashboard can edit, add, or remove
 * any of them. Only the shape (entitlement keys) is fixed in code. Prices are in
 * SAR, VAT excluded, over a 30-day cycle.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // ── Employer ──
            [
                'code' => 'employer_free', 'audience' => PlanAudience::Employer->value,
                'name' => ['ar' => 'مجاني'], 'price' => 0, 'duration_days' => 30,
                'entitlements' => ['active_listings_limit' => 1],
            ],
            [
                'code' => 'employer_basic', 'audience' => PlanAudience::Employer->value,
                'name' => ['ar' => 'أساسي'], 'price' => 99, 'duration_days' => 30,
                'entitlements' => [
                    'active_listings_limit' => 5,
                    'verified_badge' => true, 'show_logo' => true,
                    'applicant_management' => true, 'analytics' => true,
                ],
            ],
            [
                'code' => 'employer_pro', 'audience' => PlanAudience::Employer->value,
                'name' => ['ar' => 'احترافي'], 'price' => 249, 'duration_days' => 30,
                'entitlements' => [
                    'active_listings_limit' => 15,
                    'verified_badge' => true, 'show_logo' => true,
                    'applicant_management' => true, 'analytics' => true,
                    'featured_listings' => true, 'candidate_search' => true,
                ],
            ],

            // ── Candidate ──
            [
                'code' => 'candidate_free', 'audience' => PlanAudience::Candidate->value,
                'name' => ['ar' => 'مجاني'], 'price' => 0, 'duration_days' => 30,
                'entitlements' => [],
            ],
            [
                'code' => 'candidate_premium', 'audience' => PlanAudience::Candidate->value,
                'name' => ['ar' => 'مميّز'], 'price' => 19, 'duration_days' => 30,
                'entitlements' => [
                    'priority_applicant' => true, 'featured_profile' => true,
                    'who_viewed' => true, 'early_apply' => true, 'available_badge' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::query()->updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
