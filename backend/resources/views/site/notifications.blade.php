@extends('site.layouts.app')

@section('title', 'الإشعارات')

@php
  if (! function_exists('zenoNotificationText')) {
  function zenoNotificationText(array $data): array {
      $job = $data['job_title'] ?? '';
      return match ($data['type'] ?? '') {
          'application_submitted' => ['طلب جديد على وظيفتك', "وظيفة «{$job}» — راجع المتقدّم."],
          'application_decision' => ($data['accepted'] ?? false)
              ? ['تم قبول طلبك', "تم قبولك في «{$job}» — ستصلك طريقة التواصل."]
              : ['تحديث على طلبك', "تم تحديث حالة طلبك على «{$job}»."],
          'job_changed' => ['تغيير في وظيفة قدّمت عليها', "تغيّرت بيانات وظيفة «{$job}»."],
          default => ['إشعار', ''],
      };
  }
  }
@endphp

@section('content')
<x-site.header/>

<section style="max-width:720px;margin:0 auto;padding:40px 24px 60px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:30px;font-weight:900;color:#211F20">الإشعارات</h1>
    @if (count($notifications) > 0)
      <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" style="background:none;border:none;font-family:inherit;font-size:14px;font-weight:800;color:#8A6D12;cursor:pointer">تعليم الكل كمقروء</button>
      </form>
    @endif
  </div>

  @forelse ($notifications as $notification)
    @php([$title, $body] = zenoNotificationText($notification->data))
    @php($unread = $notification->read_at === null)
    <div style="display:flex;gap:13px;align-items:flex-start;background:{{ $unread ? '#FFFDF6' : '#fff' }};border:1px solid {{ $unread ? '#F7BE17' : '#EFEDE6' }};border-radius:16px;padding:16px;margin-bottom:11px">
      <div style="width:40px;height:40px;border-radius:12px;background:#FDF3D6;color:#8A6D12;display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:20px" icon-name="bell-2"></i></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:15.5px;font-weight:800;color:#211F20">{{ $title }}</div>
        @if ($body)<div style="font-size:13.5px;color:#6C665C;font-weight:500;margin-top:3px;line-height:1.6">{{ $body }}</div>@endif
      </div>
      @if ($unread)
        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
          @csrf
          <button type="submit" title="تعليم كمقروء" style="background:none;border:none;cursor:pointer;padding:4px"><span style="display:block;width:9px;height:9px;border-radius:50%;background:#F7BE17"></span></button>
        </form>
      @endif
    </div>
  @empty
    <div style="background:#fff;border:1px solid #EFEDE6;border-radius:20px;padding:48px 26px;text-align:center">
      <div style="width:60px;height:60px;border-radius:18px;background:#F4F1EA;color:#A39D90;display:flex;align-items:center;justify-content:center;margin:0 auto"><i class="iconsax" style="font-size:28px" icon-name="bell-2"></i></div>
      <div style="font-size:18px;font-weight:800;color:#211F20;margin-top:16px">لا توجد إشعارات</div>
    </div>
  @endforelse

  <div style="margin-top:16px">{{ $notifications->links() }}</div>
</section>
@endsection
