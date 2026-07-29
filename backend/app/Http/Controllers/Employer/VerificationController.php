<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\OrganizationService;
use App\Services\VerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class VerificationController extends Controller
{
    public function __construct(
        private readonly VerificationService $verification,
        private readonly OrganizationService $organizations,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $organization = $this->organizations->find($request->user());

        if ($organization === null) {
            return redirect()->route('employer.register');
        }

        return view('employer.verification.show', [
            'organization' => $organization,
            'request' => $this->verification->currentFor($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [], ['documents' => 'المستندات']);

        $this->verification->submit($request->user(), $request->file('documents'));

        return to_route('employer.verification.show')
            ->with('status', 'تم إرسال طلب التوثيق. سنراجعه ونبلغك بالنتيجة.');
    }
}
