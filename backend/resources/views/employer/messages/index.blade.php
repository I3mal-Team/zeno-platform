@extends('employer.layouts.app', ['active' => 'messages', 'pageTitle' => 'الرسائل', 'pageSubtitle' => 'تواصل مع المتقدّمين المقبولين'])

@section('title', 'الرسائل')

@section('content')
<div class="view" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;overflow:hidden;display:grid;grid-template-columns:300px 1fr;height:calc(100vh - 190px)">

  {{-- Conversation list --}}
  <div style="border-left:1px solid #E5EAE6;overflow-y:auto">
    <div style="padding:16px 18px;font-size:16px;font-weight:800;color:#22302A;border-bottom:1px solid #F2F0E9">المحادثات</div>

    @forelse ($conversations as $conversation)
      @php($name = $conversation->candidate->candidateProfile?->full_name ?? 'مرشح')
      @php($on = $activeConversation && $activeConversation->uuid === $conversation->uuid)
      <a href="{{ route('employer.messages.show', $conversation->uuid) }}" class="rowh" style="display:flex;align-items:center;gap:12px;padding:14px 18px;border-bottom:1px solid #EFF2EE;background:{{ $on ? '#F1EFE4' : '#fff' }}">
        <x-employer.avatar :name="$name" :size="46" :radius="14" :font="17"/>
        <div style="flex:1;min-width:0">
          <div style="font-size:14.5px;font-weight:800;color:#284C3D;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $name }}</div>
          <div style="font-size:12.5px;color:#7E8B84;font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $conversation->latestMessage?->body ?? 'لم تبدأ المحادثة بعد' }}</div>
        </div>
        @if ($conversation->unread_count > 0)
          <span style="min-width:21px;height:21px;padding:0 6px;border-radius:8px;background:#C9A24B;color:#284C3D;font-size:11.5px;font-weight:800;display:flex;align-items:center;justify-content:center;flex:0 0 auto">{{ $conversation->unread_count }}</span>
        @endif
      </a>
    @empty
      <div style="padding:40px 20px;text-align:center;color:#7E8B84;font-size:13.5px;font-weight:600;line-height:1.8">لا توجد محادثات بعد.<br>تُفتح المحادثة تلقائياً بمجرد قبول متقدّم.</div>
    @endforelse
  </div>

  {{-- Thread --}}
  <div style="display:flex;flex-direction:column;min-width:0">
    @if ($activeConversation)
      @php($name = $activeConversation->candidate->candidateProfile?->full_name ?? 'مرشح')
      @php($application = $activeConversation->application)

      <div style="padding:14px 20px;border-bottom:1px solid #F2F0E9;display:flex;align-items:center;gap:12px">
        <x-employer.avatar :name="$name" :size="44" :radius="13" :font="16"/>
        <div style="flex:1;min-width:0">
          <div style="font-size:16px;font-weight:800;color:#22302A">{{ $name }}</div>
          <div style="font-size:12.5px;color:#7E8B84;font-weight:600">{{ $application->job->title }} · طلب #{{ $application->reference_number }}</div>
        </div>
        @if ($application->contact_channel->allowsWhatsApp())
          {{-- Only when the listing offered WhatsApp as a channel; otherwise the thread is the only route. --}}
          <a href="https://wa.me/{{ ltrim($activeConversation->candidate->phone_e164, '+') }}" target="_blank" rel="noopener" class="btn" style="display:flex;align-items:center;gap:7px;background:#25D366;color:#fff;border-radius:12px;padding:9px 15px;font-size:13.5px;font-weight:800"><i class="iconsax" style="font-size:18px" icon-name="message-text"></i>واتساب</a>
        @endif
      </div>

      <div data-thread data-conversation="{{ $activeConversation->uuid }}" data-me="{{ auth()->user()->uuid }}" style="flex:1;overflow-y:auto;padding:22px;background:#F7F5F0;display:flex;flex-direction:column;gap:12px">
        @foreach ($messages as $message)
          @php($mine = $message->sender_id === auth()->id())
          <div data-uuid="{{ $message->uuid }}" style="align-self:{{ $mine ? 'flex-start' : 'flex-end' }};max-width:70%;background:{{ $mine ? '#284C3D' : '#fff' }};color:{{ $mine ? '#fff' : '#284C3D' }};padding:12px 16px;border-radius:{{ $mine ? '16px 16px 16px 5px' : '16px 16px 5px 16px' }};font-size:14.5px;font-weight:600;line-height:1.6;box-shadow:0 6px 14px -10px rgba(40,35,25,.3)">
            {{ $message->body }}
            <div style="font-size:11px;opacity:.6;font-weight:700;margin-top:5px;text-align:left">{{ $message->created_at?->timezone('Asia/Riyadh')->format('H:i') }}</div>
          </div>
        @endforeach
      </div>

      {{-- Cloned by resources/js/chat.js to render a live message with the same styling. --}}
      <template data-msg="mine"><div style="align-self:flex-start;max-width:70%;background:#284C3D;color:#fff;padding:12px 16px;border-radius:16px 16px 16px 5px;font-size:14.5px;font-weight:600;line-height:1.6;box-shadow:0 6px 14px -10px rgba(40,35,25,.3)"><span data-slot="body"></span><div data-slot="time" style="font-size:11px;opacity:.6;font-weight:700;margin-top:5px;text-align:left"></div></div></template>
      <template data-msg="them"><div style="align-self:flex-end;max-width:70%;background:#fff;color:#284C3D;padding:12px 16px;border-radius:16px 16px 5px 16px;font-size:14.5px;font-weight:600;line-height:1.6;box-shadow:0 6px 14px -10px rgba(40,35,25,.3)"><span data-slot="body"></span><div data-slot="time" style="font-size:11px;opacity:.6;font-weight:700;margin-top:5px;text-align:left"></div></div></template>

      <form method="POST" action="{{ route('employer.messages.send', $activeConversation->uuid) }}" data-composer style="padding:14px 18px;border-top:1px solid #F2F0E9;display:flex;align-items:center;gap:11px">
        @csrf
        <div style="flex:1;display:flex;align-items:center;gap:9px;background:#EFF2EE;border-radius:14px;padding:12px 16px">
          <input name="body" required maxlength="2000" autocomplete="off" placeholder="اكتب رسالة…" style="flex:1;border:none;outline:none;background:none;font-size:14.5px;font-weight:600;color:#284C3D">
        </div>
        <button type="submit" class="btn" style="width:46px;height:46px;border-radius:14px;background:#284C3D;border:none;cursor:pointer;color:#C9A24B;display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:21px" icon-name="send-2"></i></button>
      </form>
    @else
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#7E8B84;gap:12px;background:#F7F5F0">
        <i class="iconsax" style="font-size:44px;color:#BAC4BD" icon-name="messages-2"></i>
        <div style="font-size:15px;font-weight:700">اختر محادثة لعرضها</div>
      </div>
    @endif
  </div>

</div>

@if ($activeConversation)
  @push('scripts')
    @vite('resources/js/chat.js')
  @endpush
@endif
@endsection
