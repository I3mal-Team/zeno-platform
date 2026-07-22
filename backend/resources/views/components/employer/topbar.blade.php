@props(['title', 'subtitle'])

<div style="position:sticky;top:0;z-index:20;background:rgba(241,239,233,.85);backdrop-filter:blur(12px);border-bottom:1px solid #E5E1D8;padding:16px 30px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap">
  <div>
    <div style="font-size:22px;font-weight:900;color:#211F20">{{ $title }}</div>
    <div style="font-size:13.5px;color:#8A857A;font-weight:600;margin-top:2px">{{ $subtitle }}</div>
  </div>

  <div style="display:flex;align-items:center;gap:12px">
    <a href="{{ route('notifications.index') }}" title="الإشعارات" class="btn" style="position:relative;width:44px;height:44px;border-radius:13px;background:#fff;border:1px solid #E7E3DA;display:flex;align-items:center;justify-content:center;color:#2B2724"><i class="iconsax" style="font-size:21px" icon-name="bell-2"></i>
      @if (($notificationsUnread ?? 0) > 0)<span style="position:absolute;top:-4px;left:-4px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#F7BE17;color:#2B2724;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center">{{ $notificationsUnread > 9 ? '9+' : $notificationsUnread }}</span>@endif
    </a>
    <a href="{{ route('employer.jobs.create') }}" class="btn" style="display:flex;align-items:center;gap:8px;background:#2B2724;color:#fff;border-radius:13px;padding:0 20px;height:44px;font-size:14.5px;font-weight:800;box-shadow:0 12px 24px -14px rgba(40,35,25,.5)"><i class="iconsax" style="font-size:19px;color:#F7BE17" icon-name="add"></i>نشر وظيفة</a>
  </div>
</div>
