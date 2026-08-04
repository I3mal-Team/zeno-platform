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
use Illuminate\Http\UploadedFile;

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

        return redirect()->route('dashboard')->with('status', 'تم حفظ ملفك بنجاح.');
    }

    public function edit(Request $request, CatalogService $catalog): View|RedirectResponse
    {
        $profile = $this->profiles->find($request->user());

        if ($profile === null) {
            return redirect()->route('profile.complete');
        }

        return view('site.profile.edit', [
            'profile' => $profile,
            'cities' => $catalog->activeCities(),
            'countries' => $catalog->activeCountries(),
        ]);
    }

    public function update(SaveCandidateProfileRequest $request): RedirectResponse
    {
        $this->profiles->save($request->user(), $request->toDto());

        return redirect()->route('profile.edit')->with('status', 'تم تحديث ملفك بنجاح.');
    }

    public function avatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:'.config('media.max_upload_kb')],
        ]);

        $profile = $this->profiles->find($request->user());
        $file = $request->file('avatar');

        if ($profile === null || ! $file instanceof UploadedFile) {
            return redirect()->route('profile.edit');
        }

        $this->profiles->saveAvatar($profile, $file);

        return redirect()->route('profile.edit')->with('status', 'تم تحديث الصورة الشخصية.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:'.config('media.max_upload_kb')],
        ]);

        $profile = $this->profiles->find($request->user());
        $file = $request->file('resume');

        if ($profile === null || ! $file instanceof UploadedFile) {
            return redirect()->route('profile.edit');
        }

        $this->profiles->saveResume($profile, $file);

        return redirect()->route('profile.edit')->with('status', 'تم رفع السيرة الذاتية.');
    }
}
