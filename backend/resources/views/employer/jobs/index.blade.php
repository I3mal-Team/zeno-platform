@extends('employer.layouts.app', ['active' => 'jobs', 'pageTitle' => 'وظائفي', 'pageSubtitle' => 'كل الإعلانات التي نشرتها'])

@section('title', 'وظائفي')

@use('App\Enums\JobStatus')

@section('content')

@if (session('status'))
  <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
@endif

<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px">
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    @foreach (['' => 'الكل', 'active' => 'نشطة', 'paused' => 'موقوفة', 'pending_review' => 'قيد المراجعة'] as $key => $label)
      @php($on = ($status ?? '') === $key)
      <a href="{{ route('employer.jobs.index', array_filter(['status' => $key])) }}" style="font-size:13.5px;font-weight:800;padding:9px 16px;border-radius:12px;background:{{ $on ? '#2B2724' : '#fff' }};color:{{ $on ? '#fff' : '#6C665C' }};border:1px solid {{ $on ? '#2B2724' : '#E7E3DA' }}">{{ $label }}</a>
    @endforeach
  </div>
  <a href="{{ route('employer.jobs.create') }}" class="btn" style="display:flex;align-items:center;gap:8px;background:#F7BE17;color:#2B2724;border-radius:13px;padding:11px 20px;font-size:14.5px;font-weight:800"><i class="iconsax" style="font-size:19px" icon-name="add"></i>نشر وظيفة</a>
</div>

@forelse ($jobs as $job)
  @php($s = $job->status)
  <div class="cardh" style="background:#fff;border:1px solid #EDEAE2;border-radius:18px;padding:18px;margin-bottom:12px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="width:50px;height:50px;border-radius:14px;background:{{ $job->category->tintBackground() }};color:{{ $job->category->tintForeground() }};display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:24px" icon-name="{{ $job->category->icon }}"></i></div>

    <div style="flex:1;min-width:180px">
      <div style="font-size:16px;font-weight:800;color:#211F20">{{ $job->title }}</div>
      <div style="font-size:13px;color:#8A857A;font-weight:600;margin-top:2px">{{ $job->formattedSalary() }} ريال · {{ $job->workType->name }}</div>
    </div>

    <div style="display:flex;align-items:center;gap:6px;color:#8A857A;font-size:13.5px;font-weight:700"><i class="iconsax" style="font-size:18px" icon-name="profile-2user"></i>{{ $job->live_applications_count ?? 0 }} متقدم</div>

    <span style="font-size:12px;font-weight:800;padding:6px 12px;border-radius:9px;background:{{ $s->badgeBackground() }};color:{{ $s->badgeForeground() }}">{{ $s->label() }}</span>

    <div style="display:flex;align-items:center;gap:8px">
      <a href="{{ route('employer.jobs.edit', $job->uuid) }}" class="btn" title="تعديل" style="width:38px;height:38px;border-radius:11px;background:#F4F1EA;color:#2B2724;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:18px" icon-name="edit-2"></i></a>

      @if ($s->canTransitionTo(JobStatus::Paused))
        <form method="POST" action="{{ route('employer.jobs.pause', $job->uuid) }}">@csrf<button class="btn" type="submit" title="إيقاف" style="width:38px;height:38px;border-radius:11px;background:#FDF3D6;color:#8A6D12;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:18px" icon-name="pause"></i></button></form>
      @elseif ($s->canTransitionTo(JobStatus::Active))
        <form method="POST" action="{{ route('employer.jobs.resume', $job->uuid) }}">@csrf<button class="btn" type="submit" title="استئناف" style="width:38px;height:38px;border-radius:11px;background:#E7F4EC;color:#1F8A4D;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:18px" icon-name="play"></i></button></form>
      @endif

      @if ($s->canTransitionTo(JobStatus::Closed))
        <form method="POST" action="{{ route('employer.jobs.close', $job->uuid) }}" onsubmit="return confirm('إغلاق الإعلان نهائياً؟')">@csrf<button class="btn" type="submit" title="إغلاق" style="width:38px;height:38px;border-radius:11px;background:#FBE6E6;color:#B23232;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:18px" icon-name="close-circle"></i></button></form>
      @endif
    </div>
  </div>
@empty
  <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:48px 26px;text-align:center">
    <div style="width:64px;height:64px;border-radius:20px;background:#F4F1EA;display:flex;align-items:center;justify-content:center;color:#A39D90;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="briefcase"></i></div>
    <div style="font-size:19px;font-weight:800;color:#211F20;margin-top:16px">لا توجد إعلانات بعد</div>
    <div style="font-size:14.5px;color:#8A857A;font-weight:600;margin-top:6px">انشر أول وظيفة لتصل إلى المرشحين القريبين منك.</div>
    <a href="{{ route('employer.jobs.create') }}" class="btn" style="display:inline-block;margin-top:20px;background:#F7BE17;color:#2B2724;font-size:14.5px;font-weight:800;padding:12px 24px;border-radius:13px">نشر وظيفة</a>
  </div>
@endforelse

<div style="margin-top:18px">{{ $jobs->links() }}</div>
@endsection
