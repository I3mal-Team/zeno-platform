@extends('site.layouts.app')

@section('title', 'الرسائل')

@section('content')
<div style="max-width:1180px;margin:0 auto;padding:28px 26px 48px">
  <h1 style="font-size:24px;font-weight:900;color:#211F20;margin-bottom:6px">الرسائل</h1>
  <p style="font-size:14.5px;color:#8A857A;font-weight:600;margin-bottom:20px">تواصل مع أصحاب العمل بعد قبول تقديمك</p>

  <div style="display:flex;gap:16px;align-items:stretch;height:560px">

    {{-- Conversation list --}}
    <div style="width:320px;flex:0 0 auto;background:#fff;border:1px solid #EDEAE2;border-radius:20px;overflow-y:auto">
      @forelse ($conversations as $conversation)
        @php($name = $conversation->organization->name)
        @php($on = $activeConversation && $activeConversation->uuid === $conversation->uuid)
        <a href="{{ route('messages.show', $conversation->uuid) }}" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;border-bottom:1px solid #F3F1EB;background:{{ $on ? '#FBF6E7' : 'transparent' }}">
          <div style="width:46px;height:46px;border-radius:14px;background:#2B2724;color:#F7BE17;display:flex;align-items:center;justify-content:center;flex:0 0 auto;font-size:17px;font-weight:900">{{ mb_substr($name, 0, 1) }}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:15px;font-weight:800;color:#211F20;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $name }}</div>
            <div style="font-size:12px;color:#8A857A;font-weight:600;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ optional($conversation->application->job)->title ?? '' }}</div>
          </div>
        </a>
      @empty
        <div style="padding:40px 22px;text-align:center;color:#8A857A;font-size:14px;font-weight:600;line-height:1.7">لا توجد محادثات بعد.<br>تُفتح المحادثة تلقائياً بمجرد قبول تقديمك.</div>
      @endforelse
    </div>

    {{-- Thread --}}
    <div style="flex:1;min-width:0;background:#fff;border:1px solid #EDEAE2;border-radius:20px;display:flex;flex-direction:column;overflow:hidden">
      @if ($activeConversation)
        @php($name = $activeConversation->organization->name)

        <div style="padding:16px 20px;border-bottom:1px solid #F0EEE8;display:flex;align-items:center;gap:12px">
          <div style="width:44px;height:44px;border-radius:13px;background:#2B2724;color:#F7BE17;display:flex;align-items:center;justify-content:center;flex:0 0 auto;font-size:17px;font-weight:900">{{ mb_substr($name, 0, 1) }}</div>
          <div>
            <div style="font-size:16px;font-weight:800;color:#211F20">{{ $name }}</div>
            <div style="font-size:12.5px;color:#8A857A;font-weight:700;margin-top:1px">{{ optional($activeConversation->application->job)->title ?? '' }}</div>
          </div>
        </div>

        <div style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:10px;background:#F6F5F1">
          <div style="align-self:center;background:rgba(33,31,32,.06);color:#8A857A;font-size:11.5px;font-weight:700;padding:6px 13px;border-radius:10px">تم فتح التواصل بعد القبول</div>
          @foreach ($messages as $message)
            @php($mine = $message->sender_id === auth()->id())
            <div style="display:flex;justify-content:{{ $mine ? 'flex-start' : 'flex-end' }}">
              <div style="max-width:72%;background:{{ $mine ? '#F7BE17' : '#fff' }};border:1px solid {{ $mine ? '#F7BE17' : '#ECEAE3' }};border-radius:16px;padding:11px 14px">
                <div style="font-size:14.5px;font-weight:500;line-height:1.6;color:#211F20">{{ $message->body }}</div>
                <div style="font-size:10px;font-weight:600;color:rgba(33,31,32,.5);margin-top:4px;text-align:left">{{ $message->created_at?->timezone('Asia/Riyadh')->format('H:i') }}</div>
              </div>
            </div>
          @endforeach
        </div>

        <form method="POST" action="{{ route('messages.send', $activeConversation->uuid) }}" style="border-top:1px solid #F0EEE8;padding:12px 16px;display:flex;gap:10px;align-items:center">
          @csrf
          <input name="body" required maxlength="2000" autocomplete="off" placeholder="اكتب رسالة..." style="flex:1;height:46px;border:1.5px solid #EFEDE6;border-radius:14px;background:#F6F5F1;padding:0 15px;font-family:inherit;font-size:15px;font-weight:600;color:#211F20;outline:none">
          <button type="submit" style="width:46px;height:46px;border-radius:14px;border:none;background:#F7BE17;color:#2B2724;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:0 0 auto"><i class="iconsax" style="font-size:22px" icon-name="send-2"></i></button>
        </form>
      @else
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#8A857A;gap:12px">
          <i class="iconsax" style="font-size:44px;color:#C9C4B9" icon-name="messages-2"></i>
          <div style="font-size:15px;font-weight:700">اختر محادثة لعرضها</div>
        </div>
      @endif
    </div>

  </div>
</div>
@endsection
