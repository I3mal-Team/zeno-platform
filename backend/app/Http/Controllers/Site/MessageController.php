<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ConversationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** The candidate side of the two-pane chat, on the public site's layout. */
final class MessageController extends Controller
{
    public function __construct(private readonly ConversationService $conversations) {}

    public function index(Request $request): View
    {
        return view('site.messages.index', [
            'conversations' => $this->conversations->listFor($request->user()),
            'activeConversation' => null,
            'messages' => collect(),
        ]);
    }

    public function show(Request $request, string $uuid): View
    {
        ['conversation' => $conversation, 'messages' => $messages] =
            $this->conversations->messages($request->user(), $uuid);

        return view('site.messages.index', [
            'conversations' => $this->conversations->listFor($request->user()),
            'activeConversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, string $uuid): RedirectResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $this->conversations->send(
            $request->user(),
            $uuid,
            (string) $request->string('body'),
            (string) Str::uuid(),
        );

        return to_route('messages.show', $uuid);
    }
}
