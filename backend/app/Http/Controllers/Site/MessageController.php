<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\ConversationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
            'conversations' => $this->conversations->listWithUnreadFor($request->user()),
            'activeConversation' => null,
            'messages' => collect(),
        ]);
    }

    public function show(Request $request, string $uuid): View
    {
        ['conversation' => $conversation, 'messages' => $messages] =
            $this->conversations->messages($request->user(), $uuid);

        return view('site.messages.index', [
            'conversations' => $this->conversations->listWithUnreadFor($request->user()),
            'activeConversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, string $uuid): RedirectResponse|JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $this->conversations->send(
            $request->user(),
            $uuid,
            (string) $request->string('body'),
            (string) Str::uuid(),
        );

        // The live thread posts over fetch and renders the returned message
        // itself; a no-JS submit still falls through to the classic redirect.
        if ($request->wantsJson()) {
            return response()->json([
                'uuid' => $message->uuid,
                'body' => $message->body,
                'created_at' => $message->created_at->toIso8601String(),
                'is_mine' => true,
            ]);
        }

        return to_route('messages.show', $uuid);
    }
}
