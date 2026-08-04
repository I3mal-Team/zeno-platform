<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Services\JobAlertService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class JobAlertController extends SiteController
{
    public function __construct(private readonly JobAlertService $alerts) {}

    public function index(Request $request): View
    {
        return view('site.job-alerts', [
            'alerts' => $this->alerts->listFor($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'work_type_id' => ['nullable', 'integer', 'exists:work_types,id'],
        ]);

        $this->alerts->create($request->user(), [
            'keyword' => ($validated['keyword'] ?? '') !== '' ? $validated['keyword'] : null,
            'category_id' => $validated['category_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'work_type_id' => $validated['work_type_id'] ?? null,
        ]);

        return redirect()->route('site.job-alerts')
            ->with('status', 'تم إنشاء التنبيه — سنُشعرك بأي وظيفة مطابقة.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->alerts->delete($request->user(), $id);

        return back();
    }
}
