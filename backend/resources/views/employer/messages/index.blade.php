@extends('employer.layouts.app', ['active' => 'messages', 'pageTitle' => 'الرسائل', 'pageSubtitle' => 'تواصل مع المتقدّمين المقبولين'])

@section('title', 'الرسائل')

@section('content')
<div class="view" style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;overflow:hidden;display:grid;grid-template-columns:300px 1fr;height:calc(100vh - 190px)">

  {{-- Conversation list --}}
  <div style="border-left:1px solid #EFEDE6;overflow-y:auto">
    <div style="padding:16px 18px;font-size:16px;font-weight:800;color:#211F20;border-bottom:1px solid #F2F0E9">المحادثات</div>

    @forelse ($conversations as $conversation)
      @php($name = $conversation->candidate->candidateProfile?->full_name ?? 'مرشح')
      @php($on = $activeConversation && $activeConversation->uuid === $conversation->uuid)
      <a href="{{ route('employer.messages.show', $conversation->uuid) }}" class="rowh" style="display:flex;align-items:center;gap:12px;padding:14px 18px;border-bottom:1px solid #F5F3EC;background:{{ $on ? '#FBF6E7' : '#fff' }}">
        <x-employer.avatar :name="$name" :size="46" :radius="14" :font="17"/>
        <div style="flex:1;min-width:0">
          <div style="font-size:14.5px;font-weight:800;color:#2B2724;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $name }}</div>
          <div style="font-size:12.5px;color:#8A857A;font-weight:600;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $conversation->latestMessage?->body ?? 'لم تبدأ المحادثة بعد' }}</div>
        </div>
      </a>
    @empty
      <div style="padding:40px 20px;text-align:center;color:#8A857A;font-size:13.5px;font-weight:600;line-height:1.8">لا توجد محادثات بعد.<br>تُفتح المحادثة تلقائياً بمجرد قبول متقدّم.</div>
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
          <div style="font-size:16px;font-weight:800;color:#211F20">{{ $name }}</div>
          <div style="font-size:12.5px;color:#8A857A;font-weight:600">{{ $application->job->title }} · طلب #{{ $application->reference_number }}</div>
        </div>
        @if ($application->contact_channel->allowsWhatsApp())
          {{-- Only when the listing offered WhatsApp as a channel; otherwise the thread is the only route. --}}
          <a href="https://wa.me/{{ ltrim($activeConversation->candidate->phone_e164, '+') }}" target="_blank" rel="noopener" class="btn" style="display:flex;align-items:center;gap:7px;background:#25D366;color:#fff;border-radius:12px;padding:9px 15px;font-size:13.5px;font-weight:800"><i class="iconsax" style="font-size:18px" icon-name="message-text"></i>واتساب</a>
        @endif
      </div>

      <div style="flex:1;overflow-y:auto;padding:22px;background:#F7F5F0;display:flex;flex-direction:column;gap:12px">
        @foreach ($messages as $message)
          @php($mine = $message->sender_id === auth()->id())
          <div style="align-self:{{ $mine ? 'flex-start' : 'flex-end' }};max-width:70%;background:{{ $mine ? '#2B2724' : '#fff' }};color:{{ $mine ? '#fff' : '#2B2724' }};padding:12px 16px;border-radius:{{ $mine ? '16px 16px 16px 5px' : '16px 16px 5px 16px' }};font-size:14.5px;font-weight:600;line-height:1.6;box-shadow:0 6px 14px -10px rgba(40,35,25,.3)">
            {{ $message->body }}
            <div style="font-size:11px;opacity:.6;font-weight:700;margin-top:5px;text-align:left">{{ $message->created_at?->timezone('Asia/Riyadh')->format('H:i') }}</div>
          </div>
        @endforeach
      </div>

      <form method="POST" action="{{ route('employer.messages.send', $activeConversation->uuid) }}" style="padding:14px 18px;border-top:1px solid #F2F0E9;display:flex;align-items:center;gap:11px">
        @csrf
        <div style="flex:1;display:flex;align-items:center;gap:9px;background:#F5F3EC;border-radius:14px;padding:12px 16px">
          <input name="body" required maxlength="2000" autocomplete="off" placeholder="اكتب رسالة…" style="flex:1;border:none;outline:none;background:none;font-size:14.5px;font-weight:600;color:#2B2724">
        </div>
        <button type="submit" class="btn" style="width:46px;height:46px;border-radius:14px;background:#2B2724;border:none;cursor:pointer;color:#F7BE17;display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:21px" icon-name="send-2"></i></button>
      </form>
    @else
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#8A857A;gap:12px;background:#F7F5F0">
        <i class="iconsax" style="font-size:44px;color:#C9C4B9" icon-name="messages-2"></i>
        <div style="font-size:15px;font-weight:700">اختر محادثة لعرضها</div>
      </div>
    @endif
  </div>

</div>
@endsection
