<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The candidate's "طلباتي" on the public site — mirrors the mobile screen. */
final class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $applications) {}

    public function index(Request $request): View
    {
        return view('site.applications.index', [
            'applications' => $this->applications->listForCandidate($request->user()),
        ]);
    }

    public function withdraw(Request $request, string $reference): RedirectResponse
    {
        $this->applications->withdraw($request->user(), $reference);

        return back()->with('status', 'تم سحب طلبك.');
    }
}
