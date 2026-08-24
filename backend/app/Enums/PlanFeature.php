<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The capabilities a plan can grant. Each feature is a fixed key mapped to an
 * app capability; the *value* per plan is what the dashboard controls. Adding a
 * plan is data (dashboard); adding a new capability is code (here).
 */
enum PlanFeature: string
{
    // Employer
    case ActiveListingsLimit = 'active_listings_limit';
    case VerifiedBadge = 'verified_badge';
    case ShowLogo = 'show_logo';
    case ApplicantManagement = 'applicant_management';
    case Analytics = 'analytics';
    case FeaturedListings = 'featured_listings';
    case CandidateSearch = 'candidate_search';

    // Candidate
    case PriorityApplicant = 'priority_applicant';
    case FeaturedProfile = 'featured_profile';
    case WhoViewed = 'who_viewed';
    case EarlyApply = 'early_apply';
    case AvailableBadge = 'available_badge';

    public function audience(): PlanAudience
    {
        return match ($this) {
            self::PriorityApplicant, self::FeaturedProfile, self::WhoViewed,
            self::EarlyApply, self::AvailableBadge => PlanAudience::Candidate,
            default => PlanAudience::Employer,
        };
    }

    /** 'int' features hold a number (a limit); 'bool' features are a switch. */
    public function type(): string
    {
        return $this === self::ActiveListingsLimit ? 'int' : 'bool';
    }

    public function label(): string
    {
        return match ($this) {
            self::ActiveListingsLimit => 'عدد الإعلانات النشطة',
            self::VerifiedBadge => 'شارة التوثيق',
            self::ShowLogo => 'شعار المنشأة على الكروت',
            self::ApplicantManagement => 'فرز المتقدمين وملاحظات',
            self::Analytics => 'الإحصائيات',
            self::FeaturedListings => 'تمييز الإعلانات',
            self::CandidateSearch => 'البحث عن مرشحين',
            self::PriorityApplicant => 'أولوية في الطلبات',
            self::FeaturedProfile => 'ملف مميّز في البحث',
            self::WhoViewed => 'مين شاف ملفك',
            self::EarlyApply => 'تقديم مبكّر للقريبة',
            self::AvailableBadge => 'شارة «متاح للعمل»',
        };
    }

    public function default(): int|bool
    {
        return $this->type() === 'int' ? 0 : false;
    }

    /** @return list<self> */
    public static function forAudience(PlanAudience $audience): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $f) => $f->audience() === $audience,
        ));
    }
}
