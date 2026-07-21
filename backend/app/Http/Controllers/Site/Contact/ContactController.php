<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Contact;

use App\Http\Controllers\Site\SiteController;
use App\Http\Requests\Site\SendContactRequest;
use App\Http\ViewModels\Site\ContactViewModel;
use App\Services\ContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class ContactController extends SiteController
{
    public function show(ContactViewModel $viewModel): View
    {
        return view('site.pages.contact', [
            'topics' => $viewModel->topics(),
            'channels' => $viewModel->channels(),
            'workingHours' => $viewModel->workingHours(),
        ]);
    }

    public function send(SendContactRequest $request, ContactService $contact): RedirectResponse
    {
        $contact->record($request->toDto());

        return redirect()->route('site.contact')->with('contact_sent', true);
    }
}
