<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\Employer\SaveOrganizationRequest;
use App\Services\CatalogService;
use App\Services\OrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class RegisterController extends Controller
{
    public function __construct(private readonly OrganizationService $organizations) {}

    public function show(Request $request, CatalogService $catalog): View|RedirectResponse
    {
        if ($this->organizations->find($request->user()) !== null) {
            return redirect()->route('employer.dashboard');
        }

        return view('employer.register', ['cities' => $catalog->activeCities()]);
    }

    public function store(SaveOrganizationRequest $request): RedirectResponse
    {
        $this->organizations->save($request->user(), $request->toDto());

        return redirect()->route('employer.dashboard')
            ->with('status', 'تم حفظ بيانات المنشأة.');
    }

    public function logo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:'.config('media.max_upload_kb')],
        ]);

        $organization = $this->organizations->find($request->user());
        $file = $request->file('logo');

        if ($organization === null || ! $file instanceof UploadedFile) {
            return redirect()->route('employer.dashboard');
        }

        $this->organizations->saveLogo($organization, $file);

        return redirect()->route('employer.dashboard')->with('status', 'تم تحديث شعار المنشأة.');
    }
}
