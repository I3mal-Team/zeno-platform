@extends('site.layouts.app')

@section('title', 'الوظائف المتاحة')

@section('content')
<x-site.header/>

<section style="background:linear-gradient(155deg,#315C4A,#223F31);padding:44px 0">
  <div style="max-width:1180px;margin:0 auto;padding:0 26px">
    <h1 style="font-size:32px;font-weight:900;color:#fff">ابحث عن وظيفتك القريبة</h1>

    <form method="GET" action="{{ route('site.jobs.index') }}" style="display:flex;gap:12px;margin-top:22px;flex-wrap:wrap">
      <div style="flex:1;min-width:240px;display:flex;align-items:center;gap:10px;background:#fff;border-radius:15px;padding:13px 16px">
        <i class="iconsax" style="font-size:20px;color:#869089" icon-name="search-normal-1"></i>
        <input name="q" value="{{ $filters['query'] }}" placeholder="وظيفة، مهنة، أو منشأة…" style="flex:1;border:none;outline:none;font-family:inherit;font-size:15px;font-weight:600;color:#284C3D;background:none">
      </div>
      <select name="city" style="min-width:170px;border:none;border-radius:15px;padding:13px 16px;font-family:inherit;font-size:15px;font-weight:700;color:#284C3D">
        <option value="">كل المدن</option>
        @foreach ($cities as $city)
          <option value="{{ $city->id }}" @selected($filters['city'] == $city->id)>{{ $city->name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn" style="background:#C9A24B;color:#284C3D;border:none;font-family:inherit;font-size:15px;font-weight:800;padding:13px 28px;border-radius:15px;cursor:pointer">بحث</button>
    </form>

    @auth
      @if (auth()->user()->role === 'candidate')
        @php($alertCategoryId = optional($categories->firstWhere('code', $filters['category']))->id)
        <form method="POST" action="{{ route('site.job-alerts.store') }}" style="margin-top:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
          @csrf
          @if ($filters['query'])<input type="hidden" name="keyword" value="{{ $filters['query'] }}">@endif
          @if ($filters['city'])<input type="hidden" name="city_id" value="{{ $filters['city'] }}">@endif
          @if ($alertCategoryId)<input type="hidden" name="category_id" value="{{ $alertCategoryId }}">@endif
          <button type="submit" class="btn" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.28);font-family:inherit;font-size:14px;font-weight:800;padding:11px 18px;border-radius:13px;cursor:pointer"><i class="iconsax" style="font-size:18px" icon-name="notification-bing"></i>احفظ هذا البحث كتنبيه</button>
          <a href="{{ route('site.job-alerts') }}" class="lnk" style="font-size:13.5px;font-weight:800;color:rgba(255,255,255,.85)">تنبيهاتي</a>
        </form>
      @endif
    @endauth
  </div>
</section>

<div class="g-jobs" style="max-width:1180px;margin:0 auto;padding:30px 26px;display:grid;grid-template-columns:262px 1fr;gap:26px;align-items:start">

  <aside style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;position:sticky;top:92px">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:16px;font-weight:800;color:#284C3D">تصفية</span>
      <a href="{{ route('site.jobs.index') }}" class="lnk" style="font-size:13px;font-weight:700;color:#869089">مسح الكل</a>
    </div>

    <div style="font-size:14px;font-weight:800;color:#284C3D;margin-top:22px;margin-bottom:12px">المجال</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      @foreach ($categories as $category)
        @php($isActive = $filters['category'] === $category->code)
        <a href="{{ route('site.jobs.index', array_filter(['q' => $filters['query'], 'city' => $filters['city'], 'category' => $isActive ? null : $category->code])) }}"
           style="font-size:12.5px;font-weight:700;padding:7px 13px;border-radius:100px;border:1px solid {{ $isActive ? '#284C3D' : '#E5EAE6' }};background:{{ $isActive ? '#284C3D' : '#fff' }};color:{{ $isActive ? '#fff' : '#5C6862' }}">{{ $category->name }}</a>
      @endforeach
    </div>
  </aside>

  <div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
      <span style="font-size:15px;font-weight:700;color:#5C6862">{{ $jobs->total() }} وظيفة متاحة</span>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">
      @forelse ($jobs as $job)
        <x-site.job-card :job="$job"/>
      @empty
        <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:56px 26px;text-align:center">
          <div style="width:64px;height:64px;border-radius:20px;background:#EDF1EC;display:flex;align-items:center;justify-content:center;color:#869089;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="search-status-1"></i></div>
          <div style="font-size:19px;font-weight:800;color:#284C3D;margin-top:18px">لا توجد نتائج مطابقة</div>
          <div style="font-size:15px;color:#7E8B84;font-weight:600;margin-top:6px">جرّب تعديل الفلاتر أو كلمة البحث</div>
          <a href="{{ route('site.jobs.index') }}" class="btn" style="display:inline-block;margin-top:20px;background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:11px 22px;border-radius:13px">مسح الفلاتر</a>
        </div>
      @endforelse
    </div>

    <div style="margin-top:26px">{{ $jobs->links() }}</div>
  </div>
</div>

<x-site.footer/>
@endsection

@push('head')
<style>@media (max-width: 900px) { .g-jobs { grid-template-columns: 1fr !important } }</style>
@endpush
