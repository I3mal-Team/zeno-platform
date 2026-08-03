@extends('employer.layouts.app', ['active' => 'jobs', 'pageTitle' => 'وظائفي', 'pageSubtitle' => 'كل الإعلانات التي نشرتها'])

@section('title', 'وظائفي')

@use('App\Enums\JobStatus')

@section('content')
@if (session('status'))
  <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
@endif

<div class="view">
  <div style="display:flex;gap:9px;flex-wrap:wrap;margin-bottom:18px">
    @foreach (['' => 'الكل', 'active' => 'نشطة', 'paused' => 'موقوفة', 'pending_review' => 'قيد المراجعة'] as $key => $label)
      @php($on = ($status ?? '') === $key)
      <a href="{{ route('employer.jobs.index', array_filter(['status' => $key])) }}" class="btn" style="border:1px solid {{ $on ? '#284C3D' : '#DCE3DD' }};background:{{ $on ? '#284C3D' : '#fff' }};color:{{ $on ? '#fff' : '#5C6862' }};font-size:14px;font-weight:800;padding:9px 18px;border-radius:12px">{{ $label }}</a>
    @endforeach
  </div>

  @if ($jobs->isEmpty())
    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:48px 26px;text-align:center">
      <div style="width:64px;height:64px;border-radius:20px;background:#EDF1EC;display:flex;align-items:center;justify-content:center;color:#869089;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="briefcase"></i></div>
      <div style="font-size:19px;font-weight:800;color:#22302A;margin-top:16px">لا توجد إعلانات بعد</div>
      <div style="font-size:14.5px;color:#7E8B84;font-weight:600;margin-top:6px">انشر أول وظيفة لتصل إلى المرشحين القريبين منك.</div>
      <a href="{{ route('employer.jobs.create') }}" class="btn" style="display:inline-block;margin-top:20px;background:#C9A24B;color:#284C3D;font-size:14.5px;font-weight:800;padding:12px 24px;border-radius:13px">نشر وظيفة</a>
    </div>
  @else
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
      @foreach ($jobs as $job)
        @php($state = $job->status)
        <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px">
          <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:54px;height:54px;border-radius:16px;background:{{ $job->category->tintBackground() }};display:flex;align-items:center;justify-content:center;color:{{ $job->category->tintForeground() }};flex:0 0 auto"><i class="iconsax" style="font-size:26px" icon-name="{{ $job->category->icon }}"></i></div>

            <div style="flex:1;min-width:0">
              <div style="display:flex;align-items:center;gap:9px">
                <a href="{{ route('employer.jobs.edit', $job->uuid) }}" style="font-size:18px;font-weight:800;color:#284C3D">{{ $job->title }}</a>
                <span style="font-size:12px;font-weight:800;color:{{ $state->badgeForeground() }};background:{{ $state->badgeBackground() }};padding:3px 10px;border-radius:8px">{{ $state->label() }}</span>
              </div>
              <div style="font-size:13.5px;color:#7E8B84;font-weight:600;margin-top:3px">{{ $job->workType->name }}@if ($job->district) · {{ $job->district->name }}@endif @if ($job->published_at) · {{ $job->published_at->diffForHumans() }}@endif</div>
            </div>

            <div style="text-align:left;flex:0 0 auto">
              <div style="font-size:18px;font-weight:900;color:#284C3D">{{ $job->formattedSalary() }}</div>
              <div style="font-size:11.5px;color:#869089;font-weight:700">{{ $job->salaryUnit->name }}</div>
            </div>
          </div>

          <div style="display:flex;align-items:center;gap:10px;margin-top:16px;padding-top:16px;border-top:1px solid #F0EEE7">
            <div style="flex:1;display:flex;align-items:center;gap:8px">
              <div style="width:34px;height:34px;border-radius:10px;background:#EFF2EE;display:flex;align-items:center;justify-content:center;color:#7E8B84"><i class="iconsax" style="font-size:17px" icon-name="user-1"></i></div>
              <div style="line-height:1.2"><div style="font-size:15px;font-weight:900;color:#284C3D">{{ $job->live_applications_count ?? 0 }}</div><div style="font-size:11px;color:#869089;font-weight:700">متقدّم</div></div>
            </div>
            <div style="flex:1;display:flex;align-items:center;gap:8px">
              <div style="width:34px;height:34px;border-radius:10px;background:#EFF2EE;display:flex;align-items:center;justify-content:center;color:#7E8B84"><i class="iconsax" style="font-size:17px" icon-name="eye"></i></div>
              <div style="line-height:1.2"><div style="font-size:15px;font-weight:900;color:#284C3D">{{ $job->views_count }}</div><div style="font-size:11px;color:#869089;font-weight:700">مشاهدة</div></div>
            </div>

            <a href="{{ route('employer.jobs.edit', $job->uuid) }}" class="btn" title="تعديل" style="width:38px;height:38px;border-radius:11px;background:#EFF2EE;color:#5C6862;display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:18px" icon-name="edit-2"></i></a>

            @if ($state->canTransitionTo(JobStatus::Paused))
              <form method="POST" action="{{ route('employer.jobs.pause', $job->uuid) }}">
                @csrf
                <button type="submit" class="btn" style="height:38px;padding:0 15px;border-radius:11px;background:#FBEDEA;border:none;cursor:pointer;color:#B2453A;font-family:inherit;font-size:13.5px;font-weight:800;display:flex;align-items:center;gap:6px"><i class="iconsax" style="font-size:17px" icon-name="pause-circle"></i>إيقاف</button>
              </form>
            @elseif ($state->canTransitionTo(JobStatus::Active))
              <form method="POST" action="{{ route('employer.jobs.resume', $job->uuid) }}">
                @csrf
                <button type="submit" class="btn" style="height:38px;padding:0 15px;border-radius:11px;background:#E7F4EC;border:none;cursor:pointer;color:#1F8A4D;font-family:inherit;font-size:13.5px;font-weight:800;display:flex;align-items:center;gap:6px"><i class="iconsax" style="font-size:17px" icon-name="play"></i>تفعيل</button>
              </form>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    <div style="margin-top:18px">{{ $jobs->links() }}</div>
  @endif
</div>
@endsection
