@extends('site.layouts.app')

@section('title', 'الوظائف القريبة مني')

@section('content')
<x-site.header/>

<div style="max-width:820px;margin:0 auto;padding:34px 26px">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px">
    <div>
      <h1 style="font-size:26px;font-weight:900;color:#22302A">الوظائف القريبة مني</h1>
      <p style="font-size:15px;color:#7E8B84;font-weight:500;margin-top:6px">مرتّبة حسب الأقرب إليك.</p>
    </div>
    <button id="useLoc" class="btn" style="display:inline-flex;align-items:center;gap:8px;background:#284C3D;color:#fff;border:none;font-family:inherit;font-size:14px;font-weight:800;padding:11px 18px;border-radius:13px;cursor:pointer"><i class="iconsax" style="font-size:18px" icon-name="gps"></i>استخدم موقعي الحالي</button>
  </div>

  @if (! $byGps && $jobs !== null && ! $jobs->isEmpty())
    <div style="margin-top:18px;background:#F3ECD6;color:#8A6D12;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">النتائج مبنية على مدينتك — فعّل الموقع لنتائج أدق.</div>
  @endif

  @if ($jobs === null)
    <div style="margin-top:24px;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:44px;text-align:center">
      <i class="iconsax" style="font-size:42px;color:#C9C4B9" icon-name="location-slash"></i>
      <div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:12px">حدّد موقعك أولاً</div>
      <div style="font-size:14px;color:#869089;font-weight:600;margin-top:6px">اسمح بالوصول لموقعك، أو أضِف مدينتك في <a href="{{ route('profile.edit') }}" style="color:#8A6D12;font-weight:800">ملفك الشخصي</a>.</div>
    </div>
  @elseif ($jobs->isEmpty())
    <div style="margin-top:24px;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:44px;text-align:center">
      <i class="iconsax" style="font-size:42px;color:#C9C4B9" icon-name="location"></i>
      <div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:12px">لا توجد وظائف قريبة</div>
      <div style="font-size:14px;color:#869089;font-weight:600;margin-top:6px">جرّب لاحقًا أو تصفّح كل الوظائف.</div>
      <a href="{{ route('site.jobs.index') }}" class="btn" style="display:inline-block;margin-top:18px;background:#C9A24B;color:#284C3D;font-size:14px;font-weight:800;padding:11px 22px;border-radius:12px">تصفّح الوظائف</a>
    </div>
  @else
    <div style="display:flex;flex-direction:column;gap:14px;margin-top:22px">
      @foreach ($jobs as $job)
        <x-site.job-card :job="$job"/>
      @endforeach
    </div>
    <div style="margin-top:22px">{{ $jobs->links() }}</div>
  @endif
</div>

<script>
  (function () {
    const url = new URL(window.location.href);
    const hasCoords = url.searchParams.has('lat');

    function locate() {
      if (!navigator.geolocation) return;
      navigator.geolocation.getCurrentPosition(function (position) {
        url.searchParams.set('lat', position.coords.latitude);
        url.searchParams.set('lng', position.coords.longitude);
        window.location.href = url.toString();
      });
    }

    document.getElementById('useLoc').addEventListener('click', locate);
    // Ask once automatically the first time, before falling back to the city.
    if (!hasCoords) locate();
  })();
</script>

<x-site.footer/>
@endsection
