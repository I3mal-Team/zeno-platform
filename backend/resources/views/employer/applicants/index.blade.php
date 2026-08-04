@extends('employer.layouts.app', ['active' => 'applicants', 'pageTitle' => 'المتقدّمون', 'pageSubtitle' => 'كل من تقدّم على وظائفك'])

@section('title', 'المتقدّمون')

@section('content')
@if (session('status'))
  <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
@endif

<div class="view">
  <div style="display:flex;gap:9px;flex-wrap:wrap;margin-bottom:18px">
    @foreach (['' => 'الكل', 'submitted' => 'جديد', 'accepted' => 'مقبول', 'rejected' => 'مرفوض'] as $key => $label)
      @php($on = ($status ?? '') === $key)
      <a href="{{ route('employer.applicants.index', array_filter(['status' => $key, 'q' => $search ?? ''])) }}" class="btn" style="border:1px solid {{ $on ? '#284C3D' : '#DCE3DD' }};background:{{ $on ? '#284C3D' : '#fff' }};color:{{ $on ? '#fff' : '#5C6862' }};font-size:14px;font-weight:800;padding:9px 18px;border-radius:12px">{{ $label }}</a>
    @endforeach
  </div>

  @if ($applicants->isEmpty())
    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:48px 26px;text-align:center">
      <div style="width:64px;height:64px;border-radius:20px;background:#EDF1EC;display:flex;align-items:center;justify-content:center;color:#869089;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="user-2-search"></i></div>
      <div style="font-size:19px;font-weight:800;color:#22302A;margin-top:16px">لا يوجد متقدمون بعد</div>
      <div style="font-size:14.5px;color:#7E8B84;font-weight:600;margin-top:6px">سيظهر هنا كل من يتقدّم على إعلاناتك.</div>
    </div>
  @else
    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;overflow:hidden">
      @foreach ($applicants as $application)
        @php($profile = $application->candidate->candidateProfile)
        @php($name = $profile?->full_name ?? 'مرشح')
        @php($state = $application->status)
        <div class="rowh" style="display:flex;align-items:center;gap:16px;padding:18px 22px;border-bottom:1px solid #F2F0E9">
          <x-employer.avatar :name="$name"/>

          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:9px">
              {{-- Opening the profile is what moves a submission into review, so the name carries that link. --}}
              <a href="{{ route('employer.applicants.show', $application->id) }}" style="font-size:16.5px;font-weight:800;color:#284C3D">{{ $name }}</a>
              <span style="font-size:12px;font-weight:800;color:{{ $state->badgeForeground() }};background:{{ $state->badgeBackground() }};padding:3px 10px;border-radius:8px">{{ $state->employerLabel() }}</span>
              @if ($application->shortlisted)<span style="font-size:12px;font-weight:800;color:#8A6D12;background:#F3ECD6;padding:3px 10px;border-radius:8px"><i class="iconsax" style="font-size:13px;vertical-align:-1px" icon-name="star-1"></i> مرشّح</span>@endif
            </div>
            <div style="font-size:13.5px;color:#7E8B84;font-weight:600;margin-top:3px">تقدّم على {{ $application->job->title }}@if ($profile?->city) · {{ $profile->city->name }}@endif @if ($profile?->years_of_experience) · {{ $profile->years_of_experience }} سنوات @endif · طلب #{{ $application->reference_number }}</div>
          </div>

          @if ($state->isDecidable())
            <div style="display:flex;gap:9px">
              <form method="POST" action="{{ route('employer.applicants.reject', $application->id) }}" onsubmit="return confirm('رفض هذا المتقدم؟')">
                @csrf
                <button type="submit" class="btn" title="رفض" style="width:42px;height:42px;border-radius:12px;background:#FBEDEA;border:none;cursor:pointer;color:#B2453A;display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:20px" icon-name="x"></i></button>
              </form>
              <form method="POST" action="{{ route('employer.applicants.accept', $application->id) }}">
                @csrf
                <button type="submit" class="btn" style="display:flex;align-items:center;gap:7px;height:42px;padding:0 18px;border-radius:12px;background:#1F8A4D;border:none;cursor:pointer;color:#fff;font-family:inherit;font-size:14px;font-weight:800"><i class="iconsax" style="font-size:18px" icon-name="tick-circle"></i>قبول</button>
              </form>
            </div>
          @elseif ($application->conversation)
            {{-- The thread is opened by acceptance (D-19), so a rejected row has none to link to. --}}
            <a href="{{ route('employer.messages.show', $application->conversation->uuid) }}" class="btn" style="display:flex;align-items:center;gap:7px;height:42px;padding:0 18px;border-radius:12px;background:#EFF2EE;border:1px solid #DCE3DD;color:#284C3D;font-size:14px;font-weight:800"><i class="iconsax" style="font-size:18px" icon-name="messages-2"></i>مراسلة</a>
          @endif
        </div>
      @endforeach
    </div>

    <div style="margin-top:18px">{{ $applicants->links() }}</div>
  @endif
</div>
@endsection
