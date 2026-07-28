<header style="position:sticky;top:0;z-index:60;background:rgba(250,248,243,.82);backdrop-filter:blur(14px);border-bottom:1px solid #EEEBE3">
  <div style="max-width:1180px;margin:0 auto;padding:14px 26px;display:flex;align-items:center;justify-content:space-between;gap:20px">
    <a href="{{ route('site.home') }}" style="display:flex;align-items:center"><img src="{{ asset('assets/zeno-logo.png') }}" alt="زينو" style="height:34px;width:auto"></a>

    <nav class="hide-sm" style="display:flex;align-items:center;gap:30px;font-size:15px;font-weight:700;color:#6C665C">
      <a href="{{ route('site.home') }}#features" class="lnk">المميزات</a>
      <a href="{{ route('site.home') }}#how" class="lnk">كيف يعمل</a>
      <a href="{{ route('site.jobs.index') }}" class="lnk">الوظائف</a>
      <a href="{{ route('site.pricing') }}" class="lnk">لأصحاب العمل</a>
      <a href="{{ route('site.about') }}" class="lnk">من نحن</a>
    </nav>

    <div style="display:flex;align-items:center;gap:11px">
      @auth
        <a href="{{ route('notifications.index') }}" title="الإشعارات" style="position:relative;width:40px;height:40px;border-radius:12px;background:#fff;border:1px solid #E7E3DA;display:flex;align-items:center;justify-content:center;color:#2B2724"><i class="iconsax" style="font-size:19px" icon-name="bell-2"></i>
          @if (($notificationsUnread ?? 0) > 0)<span style="position:absolute;top:-5px;left:-5px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#F7BE17;color:#2B2724;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center">{{ $notificationsUnread > 9 ? '9+' : $notificationsUnread }}</span>@endif
        </a>
        @if (auth()->user()->role === 'employer')
          <a href="{{ route('employer.dashboard') }}" class="lnk hide-sm" style="font-size:14.5px;font-weight:800;color:#2B2724;display:flex;align-items:center;gap:6px"><i class="iconsax" style="font-size:18px" icon-name="task-list"></i>لوحة التحكم</a>
        @else
          <a href="{{ route('applications.index') }}" class="lnk hide-sm" style="font-size:14.5px;font-weight:800;color:#2B2724;display:flex;align-items:center;gap:6px"><i class="iconsax" style="font-size:18px" icon-name="document-text-1"></i>طلباتي</a>
          <a href="{{ route('messages.index') }}" class="lnk hide-sm" style="font-size:14.5px;font-weight:800;color:#2B2724;display:flex;align-items:center;gap:6px"><i class="iconsax" style="font-size:18px" icon-name="messages-2"></i>الرسائل</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
          @csrf
          <button type="submit" class="lnk" style="background:none;border:none;font-family:inherit;font-size:14.5px;font-weight:800;color:#8A857A;cursor:pointer">تسجيل الخروج</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="lnk hide-sm" style="font-size:14.5px;font-weight:800;color:#2B2724">تسجيل الدخول</a>
        <a href="{{ route('site.home') }}#download" class="btn" style="background:#2B2724;color:#fff;font-size:14.5px;font-weight:800;padding:11px 20px;border-radius:13px;box-shadow:0 12px 24px -14px rgba(40,35,25,.5)">حمّل التطبيق</a>
      @endauth
    </div>
  </div>
</header>
