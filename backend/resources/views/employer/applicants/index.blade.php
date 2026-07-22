@extends('employer.layouts.app', ['active' => 'applicants', 'pageTitle' => 'المتقدّمون', 'pageSubtitle' => 'كل من تقدّم على وظائفك'])

@section('title', 'المتقدّمون')

@section('content')
@if (session('status'))
  <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
@endif

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px">
  @foreach (['' => 'الكل', 'submitted' => 'جديدة', 'review' => 'قيد المراجعة', 'accepted' => 'مقبولة', 'rejected' => 'مرفوضة'] as $key => $label)
    @php($on = ($status ?? '') === $key)
    <a href="{{ route('employer.applicants.index', array_filter(['status' => $key])) }}" style="font-size:13.5px;font-weight:800;padding:9px 16px;border-radius:12px;background:{{ $on ? '#2B2724' : '#fff' }};color:{{ $on ? '#fff' : '#6C665C' }};border:1px solid {{ $on ? '#2B2724' : '#E7E3DA' }}">{{ $label }}</a>
  @endforeach
</div>

@forelse ($applicants as $application)
  @php($s = $application->status)
  @php($profile = $application->candidate->candidateProfile)
  <div class="cardh" style="background:#fff;border:1px solid #EDEAE2;border-radius:18px;padding:18px;margin-bottom:12px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="width:48px;height:48px;border-radius:14px;background:radial-gradient(circle at 30% 26%,#FDF1CC,#F6D783);color:#7A5E0E;display:flex;align-items:center;justify-content:center;flex:0 0 auto;font-size:18px;font-weight:900">{{ mb_substr($profile?->full_name ?? 'م', 0, 1) }}</div>

    <div style="flex:1;min-width:180px">
      <div style="font-size:16px;font-weight:800;color:#211F20">{{ $profile?->full_name ?? 'مرشح' }}</div>
      <div style="font-size:13px;color:#8A857A;font-weight:600;margin-top:2px">{{ $profile?->job_title ?? '—' }} · تقدّم على «{{ $application->job->title }}»</div>
    </div>

    <span style="font-size:12px;font-weight:800;padding:6px 12px;border-radius:9px;background:{{ $s->badgeBackground() }};color:{{ $s->badgeForeground() }}">{{ $s->employerLabel() }}</span>

    <div style="display:flex;align-items:center;gap:8px">
      <a href="{{ route('employer.applicants.show', $application->id) }}" class="btn" style="background:#F4F1EA;color:#2B2724;font-size:13.5px;font-weight:800;padding:9px 16px;border-radius:11px">عرض الملف</a>

      @if ($s->isDecidable())
        <form method="POST" action="{{ route('employer.applicants.accept', $application->id) }}">@csrf<button class="btn" type="submit" style="background:#E7F4EC;color:#1F8A4D;border:none;cursor:pointer;font-size:13.5px;font-weight:800;padding:9px 16px;border-radius:11px">قبول</button></form>
        <form method="POST" action="{{ route('employer.applicants.reject', $application->id) }}" onsubmit="return confirm('رفض هذا المتقدم؟')">@csrf<button class="btn" type="submit" style="background:#FBE6E6;color:#B23232;border:none;cursor:pointer;font-size:13.5px;font-weight:800;padding:9px 16px;border-radius:11px">رفض</button></form>
      @endif
    </div>
  </div>
@empty
  <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:48px 26px;text-align:center">
    <div style="width:64px;height:64px;border-radius:20px;background:#F4F1EA;display:flex;align-items:center;justify-content:center;color:#A39D90;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="profile-2user"></i></div>
    <div style="font-size:19px;font-weight:800;color:#211F20;margin-top:16px">لا يوجد متقدمون بعد</div>
    <div style="font-size:14.5px;color:#8A857A;font-weight:600;margin-top:6px">سيظهر هنا كل من يتقدّم على إعلاناتك.</div>
  </div>
@endforelse

<div style="margin-top:18px">{{ $applicants->links() }}</div>
@endsection
