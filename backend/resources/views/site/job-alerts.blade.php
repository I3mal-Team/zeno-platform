@extends('site.layouts.app')

@section('title', 'تنبيهات الوظائف')

@section('content')
<x-site.header/>

<div style="max-width:760px;margin:0 auto;padding:34px 26px">
  <h1 style="font-size:26px;font-weight:900;color:#22302A">تنبيهات الوظائف</h1>
  <p style="font-size:15px;color:#7E8B84;font-weight:500;margin-top:6px">أنشئ تنبيهًا من <a href="{{ route('site.jobs.index') }}" class="lnk" style="color:#8A6D12;font-weight:800">صفحة الوظائف</a>، وسنُشعرك بأي وظيفة جديدة تطابقه.</p>

  @if (session('status'))
    <div style="margin-top:18px;background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ session('status') }}</div>
  @endif

  @if ($alerts->isEmpty())
    <div style="margin-top:24px;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:44px;text-align:center">
      <i class="iconsax" style="font-size:42px;color:#C9C4B9" icon-name="notification-bing"></i>
      <div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:12px">لا توجد تنبيهات بعد</div>
      <div style="font-size:14px;color:#869089;font-weight:600;margin-top:6px">ابحث عن وظيفة ثم اضغط «احفظ هذا البحث كتنبيه».</div>
      <a href="{{ route('site.jobs.index') }}" class="btn" style="display:inline-block;margin-top:18px;background:#C9A24B;color:#284C3D;font-size:14px;font-weight:800;padding:11px 22px;border-radius:12px">تصفّح الوظائف</a>
    </div>
  @else
    <div style="display:flex;flex-direction:column;gap:12px;margin-top:22px">
      @foreach ($alerts as $alert)
        @php($chips = array_filter([
          $alert->keyword ? '«'.$alert->keyword.'»' : null,
          $alert->category?->name,
          $alert->city?->name,
          $alert->workType?->name,
        ]))
        <div style="background:#fff;border:1px solid #E5EAE6;border-radius:18px;padding:18px;display:flex;align-items:center;gap:14px">
          <div style="width:46px;height:46px;border-radius:13px;flex:0 0 auto;background:#F3ECD6;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:22px;color:#8A6D12" icon-name="notification-bing"></i></div>
          <div style="flex:1;display:flex;flex-wrap:wrap;gap:7px">
            @forelse ($chips as $chip)
              <span style="font-size:13.5px;font-weight:800;color:#284C3D;background:#F4F6F3;border-radius:9px;padding:6px 12px">{{ $chip }}</span>
            @empty
              <span style="font-size:14px;font-weight:700;color:#5C6862">كل الوظائف الجديدة</span>
            @endforelse
          </div>
          <form method="POST" action="{{ route('site.job-alerts.destroy', $alert->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit" title="حذف التنبيه" style="width:36px;height:36px;border-radius:11px;background:#FBE6E6;color:#B23232;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:18px" icon-name="trash"></i></button>
          </form>
        </div>
      @endforeach
    </div>
  @endif
</div>

<x-site.footer/>
@endsection
