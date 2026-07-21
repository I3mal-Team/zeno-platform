<header style="position:sticky;top:0;z-index:60;background:rgba(250,248,243,.82);backdrop-filter:blur(14px);border-bottom:1px solid #EEEBE3">
  <div style="max-width:1180px;margin:0 auto;padding:14px 26px;display:flex;align-items:center;justify-content:space-between;gap:20px">
    <a href="{{ route('site.home') }}" style="display:flex;align-items:center"><img src="{{ asset('assets/zeno-logo.png') }}" alt="زينو" style="height:34px;width:auto"></a>

    <nav class="hide-sm" style="display:flex;align-items:center;gap:30px;font-size:15px;font-weight:700;color:#6C665C">
      <a href="{{ route('site.home') }}#features" class="lnk">المميزات</a>
      <a href="{{ route('site.home') }}#how" class="lnk">كيف يعمل</a>
      <a href="{{ route('site.jobs.index') }}" class="lnk">الوظائف</a>
      <a href="{{ route('site.home') }}#employers" class="lnk">لأصحاب العمل</a>
      <a href="{{ route('site.home') }}#faq" class="lnk">الأسئلة</a>
    </nav>

    <div style="display:flex;align-items:center;gap:11px">
      <a href="{{ route('employer.dashboard') }}" class="lnk hide-sm" style="font-size:14.5px;font-weight:800;color:#2B2724;display:flex;align-items:center;gap:6px"><i class="iconsax" style="font-size:18px" icon-name="task-list"></i>لوحة التحكم</a>
      <a href="{{ route('site.home') }}#download" class="btn" style="background:#2B2724;color:#fff;font-size:14.5px;font-weight:800;padding:11px 20px;border-radius:13px;box-shadow:0 12px 24px -14px rgba(40,35,25,.5)">حمّل التطبيق</a>
    </div>
  </div>
</header>
