<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NotificationController extends SiteController
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): View
    {
        return view('site.notifications', [
            'notifications' => $this->notifications->list($request->user(), 30),
        ]);
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $this->notifications->markRead($request->user(), $id);

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllRead($request->user());

        return back()->with('status', 'تم تعليم كل الإشعارات كمقروءة.');
    }
}
