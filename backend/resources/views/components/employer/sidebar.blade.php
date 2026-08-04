@props(['active'])

<aside class="emp-sidebar" style="width:262px;flex:0 0 auto;background:linear-gradient(180deg,#2E2A25,#241F1C);color:#B8C4BC;display:flex;flex-direction:column;position:sticky;top:0;height:100vh">
  <div style="padding:24px 22px 18px;display:flex;align-items:center;justify-content:space-between">
    <img src="{{ asset('assets/zeno-logo.png') }}" alt="AMS" style="height:30px;width:auto;filter:brightness(0) invert(1)">
    <a href="{{ route('site.home') }}" class="btn" title="الرئيسية" style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;color:#EDE9E0"><i class="iconsax" style="font-size:17px" icon-name="home-2"></i></a>
  </div>

  <div style="padding:6px 22px 10px;font-size:11.5px;font-weight:800;color:#7E8B84;letter-spacing:.06em">القائمة</div>

  <nav style="padding:0 14px;display:flex;flex-direction:column;gap:4px">
    @foreach ($items as $item)
      @php($isActive = $active === $item['key'])
      <a href="{{ $item['url'] }}" class="navi" style="display:flex;align-items:center;gap:13px;padding:12px 14px;border-radius:13px;font-size:15px;font-weight:800;background:{{ $isActive ? '#C9A24B' : 'transparent' }};color:{{ $isActive ? '#284C3D' : '#B8C4BC' }}">
        <i class="iconsax" style="font-size:21px" icon-name="{{ $item['icon'] }}"></i>
        <span style="flex:1">{{ $item['label'] }}</span>
        @if (($item['badge'] ?? 0) > 0)
          <span style="min-width:22px;height:22px;padding:0 6px;border-radius:8px;background:{{ $isActive ? '#284C3D' : '#C9A24B' }};color:{{ $isActive ? '#C9A24B' : '#284C3D' }};font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center">{{ $item['badge'] }}</span>
        @endif
      </a>
    @endforeach
  </nav>

  <div style="flex:1"></div>

  <div style="margin:14px;padding:16px;border-radius:16px;background:rgba(247,190,23,.1);border:1px solid rgba(247,190,23,.2)">
    <div style="font-size:14px;font-weight:800;color:#C9A24B">باقة الأعمال</div>
    <div style="font-size:12.5px;color:#A6B0AA;font-weight:600;margin-top:5px;line-height:1.6">إعلانات غير محدودة وأولوية في الظهور.</div>
    <a href="{{ route('site.pricing') }}" class="btn" style="display:block;text-align:center;width:100%;margin-top:12px;background:#C9A24B;color:#284C3D;border-radius:11px;padding:9px;font-size:13.5px;font-weight:800">ترقية الحساب</a>
  </div>

  <form method="POST" action="{{ route('logout') }}" style="padding:14px 18px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:11px">
    @csrf
    @if ($logoUrl)
      <img src="{{ $logoUrl }}" alt="{{ $organizationName }}" style="width:40px;height:40px;border-radius:12px;object-fit:cover;border:1px solid rgba(247,190,23,.3);flex:0 0 auto">
    @else
      <div style="width:40px;height:40px;border-radius:12px;background:radial-gradient(circle at 30% 26%,#3A352F,#241F1C);border:1px solid rgba(247,190,23,.3);display:flex;align-items:center;justify-content:center;color:#C9A24B;font-size:16px;font-weight:900;flex:0 0 auto">{{ $initial }}</div>
    @endif
    <div style="flex:1;min-width:0;line-height:1.35">
      <div style="font-size:14px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $organizationName }}</div>
      <div style="font-size:12px;color:#7E8B84;font-weight:600">{{ $verificationLabel }}</div>
    </div>
    <button type="submit" title="تسجيل الخروج" style="background:none;border:none;cursor:pointer;color:#7E8B84;display:flex;align-items:center;padding:0;flex:0 0 auto"><i class="iconsax" style="font-size:18px" icon-name="logout-1"></i></button>
  </form>
</aside>
