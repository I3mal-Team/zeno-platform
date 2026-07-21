@props(['active'])

<aside class="emp-sidebar" style="width:262px;flex:0 0 auto;background:linear-gradient(180deg,#2E2A25,#241F1C);color:#C7C2B8;display:flex;flex-direction:column;position:sticky;top:0;height:100vh">
  <div style="padding:24px 22px 18px;display:flex;align-items:center;justify-content:space-between">
    <img src="{{ asset('assets/zeno-logo.png') }}" alt="زينو" style="height:30px;width:auto;filter:brightness(0) invert(1)">
    <a href="{{ route('site.home') }}" class="btn" title="الرئيسية" style="width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;color:#EDE9E0"><i class="iconsax" style="font-size:17px" icon-name="home-2"></i></a>
  </div>

  <div style="padding:6px 22px 10px;font-size:11.5px;font-weight:800;color:#8A857A;letter-spacing:.06em">القائمة</div>

  <nav style="padding:0 14px;display:flex;flex-direction:column;gap:4px">
    @foreach ($items as $item)
      @php($isActive = $active === $item['key'])
      <a href="{{ $item['url'] }}" class="navi" style="display:flex;align-items:center;gap:13px;padding:12px 14px;border-radius:13px;font-size:15px;font-weight:800;background:{{ $isActive ? '#F7BE17' : 'transparent' }};color:{{ $isActive ? '#2B2724' : '#C7C2B8' }}">
        <i class="iconsax" style="font-size:21px" icon-name="{{ $item['icon'] }}"></i>
        <span style="flex:1">{{ $item['label'] }}</span>
        @if (($item['badge'] ?? 0) > 0)
          <span style="min-width:22px;height:22px;padding:0 6px;border-radius:8px;background:{{ $isActive ? '#2B2724' : '#F7BE17' }};color:{{ $isActive ? '#F7BE17' : '#2B2724' }};font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center">{{ $item['badge'] }}</span>
        @endif
      </a>
    @endforeach
  </nav>

  <div style="flex:1"></div>

  <div style="padding:14px 18px;border-top:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:11px">
    <div style="width:40px;height:40px;border-radius:12px;background:radial-gradient(circle at 30% 26%,#3A352F,#241F1C);border:1px solid rgba(247,190,23,.3);display:flex;align-items:center;justify-content:center;color:#F7BE17;font-size:16px;font-weight:900">{{ $initial }}</div>
    <div style="flex:1;line-height:1.35">
      <div style="font-size:14px;font-weight:800;color:#fff">{{ $organizationName }}</div>
      <div style="font-size:12px;color:#8A857A;font-weight:600">{{ $verificationLabel }}</div>
    </div>
  </div>
</aside>
