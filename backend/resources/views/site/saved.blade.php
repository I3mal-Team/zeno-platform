@extends('site.layouts.app')

@section('title', 'المحفوظات')

@section('content')
<x-site.header/>

<div style="max-width:820px;margin:0 auto;padding:34px 26px">
  <h1 style="font-size:26px;font-weight:900;color:#22302A">الوظائف المحفوظة</h1>
  <p style="font-size:15px;color:#7E8B84;font-weight:500;margin-top:6px">الوظائف التي حفظتها للرجوع إليها لاحقًا.</p>

  @if ($jobs->isEmpty())
    <div style="margin-top:24px;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:44px;text-align:center">
      <i class="iconsax" style="font-size:42px;color:#C9C4B9" icon-name="heart"></i>
      <div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:12px">لا توجد وظائف محفوظة بعد</div>
      <div style="font-size:14px;color:#869089;font-weight:600;margin-top:6px">احفظ أي وظيفة من صفحتها لتظهر هنا.</div>
      <a href="{{ route('site.jobs.index') }}" class="btn" style="display:inline-block;margin-top:18px;background:#C9A24B;color:#284C3D;font-size:14px;font-weight:800;padding:11px 22px;border-radius:12px">تصفّح الوظائف</a>
    </div>
  @else
    <div style="display:flex;flex-direction:column;gap:14px;margin-top:22px">
      @foreach ($jobs as $job)
        <div style="position:relative">
          <x-site.job-card :job="$job"/>
          <form method="POST" action="{{ route('site.jobs.unsave', $job->slug) }}" style="position:absolute;top:16px;left:16px;z-index:2">
            @csrf
            @method('DELETE')
            <button type="submit" title="إزالة من المحفوظات" style="width:34px;height:34px;border-radius:10px;background:#FBE6E6;color:#B23232;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:18px" icon-name="close-circle"></i></button>
          </form>
        </div>
      @endforeach
    </div>

    <div style="margin-top:22px">{{ $jobs->links() }}</div>
  @endif
</div>

<x-site.footer/>
@endsection
