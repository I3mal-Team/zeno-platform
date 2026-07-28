<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Profile;

use App\Http\Controllers\Site\SiteController;
use App\Http\Requests\Site\Profile\SaveCandidateProfileRequest;
use App\Services\CandidateProfileService;
use App\Services\CatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The web "أكمل بياناتك" intake a new job seeker lands on straight after OTP,
 * mirroring the employer register step. Reuses the same profile service the
 * API uses, so completion scoring stays in one place.
 */
final class CandidateProfileController extends SiteController
{
    public function __construct(private readonly CandidateProfileService $profiles) {}

    public function show(Request $request, CatalogService $catalog): View|RedirectResponse
    {
        if ($this->profiles->find($request->user()) !== null) {
            return redirect()->route('site.home');
        }

        return view('site.profile.complete', [
            'cities' => $catalog->activeCities(),
            'countries' => $catalog->activeCountries(),
        ]);
    }

    public function store(SaveCandidateProfileRequest $request): RedirectResponse
    {
        $this->profiles->save($request->user(), $request->toDto());

        return redirect()->route('site.home')->with('status', 'تم حفظ ملفك بنجاح.');
    }
}
