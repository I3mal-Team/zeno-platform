@extends('site.layouts.app')

@section('title', 'الوظائف المتاحة')

@section('content')
<x-site.header/>

<section style="background:linear-gradient(155deg,#34302B,#26221F);padding:44px 0">
  <div style="max-width:1180px;margin:0 auto;padding:0 26px">
    <h1 style="font-size:32px;font-weight:900;color:#fff">ابحث عن وظيفتك القريبة</h1>

    <form method="GET" action="{{ route('site.jobs.index') }}" style="display:flex;gap:12px;margin-top:22px;flex-wrap:wrap">
      <div style="flex:1;min-width:240px;display:flex;align-items:center;gap:10px;background:#fff;border-radius:15px;padding:13px 16px">
        <i class="iconsax" style="font-size:20px;color:#A39D90" icon-name="search-normal-1"></i>
        <input name="q" value="{{ $filters['query'] }}" placeholder="وظيفة، مهنة، أو منشأة…" style="flex:1;border:none;outline:none;font-family:inherit;font-size:15px;font-weight:600;color:#2B2724;background:none">
      </div>
      <select name="city" style="min-width:170px;border:none;border-radius:15px;padding:13px 16px;font-family:inherit;font-size:15px;font-weight:700;color:#2B2724">
        <option value="">كل المدن</option>
        @foreach ($cities as $city)
          <option value="{{ $city->id }}" @selected($filters['city'] == $city->id)>{{ $city->name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn" style="background:#F7BE17;color:#2B2724;border:none;font-family:inherit;font-size:15px;font-weight:800;padding:13px 28px;border-radius:15px;cursor:pointer">بحث</button>
    </form>
  </div>
</section>

<div class="g-jobs" style="max-width:1180px;margin:0 auto;padding:30px 26px;display:grid;grid-template-columns:262px 1fr;gap:26px;align-items:start">

  <aside style="background:#fff;border:1px solid #EFEDE6;border-radius:20px;padding:22px;position:sticky;top:92px">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:16px;font-weight:800;color:#2B2724">تصفية</span>
      <a href="{{ route('site.jobs.index') }}" class="lnk" style="font-size:13px;font-weight:700;color:#9A958A">مسح الكل</a>
    </div>

    <div style="font-size:14px;font-weight:800;color:#2B2724;margin-top:22px;margin-bottom:12px">المجال</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
      @foreach ($categories as $category)
        @php($isActive = $filters['category'] === $category->code)
        <a href="{{ route('site.jobs.index', array_filter(['q' => $filters['query'], 'city' => $filters['city'], 'category' => $isActive ? null : $category->code])) }}"
           style="font-size:12.5px;font-weight:700;padding:7px 13px;border-radius:100px;border:1px solid {{ $isActive ? '#2B2724' : '#ECEAE3' }};background:{{ $isActive ? '#2B2724' : '#fff' }};color:{{ $isActive ? '#fff' : '#6C665C' }}">{{ $category->name }}</a>
      @endforeach
    </div>
  </aside>

  <div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
      <span style="font-size:15px;font-weight:700;color:#6C665C">{{ $jobs->total() }} وظيفة متاحة</span>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">
      @forelse ($jobs as $job)
        <x-site.job-card :job="$job"/>
      @empty
        <div style="background:#fff;border:1px solid #EFEDE6;border-radius:20px;padding:56px 26px;text-align:center">
          <div style="width:64px;height:64px;border-radius:20px;background:#F4F1EA;display:flex;align-items:center;justify-content:center;color:#A39D90;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="search-status-1"></i></div>
          <div style="font-size:19px;font-weight:800;color:#2B2724;margin-top:18px">لا توجد نتائج مطابقة</div>
          <div style="font-size:15px;color:#8A857A;font-weight:600;margin-top:6px">جرّب تعديل الفلاتر أو كلمة البحث</div>
          <a href="{{ route('site.jobs.index') }}" class="btn" style="display:inline-block;margin-top:20px;background:#2B2724;color:#fff;font-size:14.5px;font-weight:800;padding:11px 22px;border-radius:13px">مسح الفلاتر</a>
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
