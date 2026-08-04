@extends('employer.layouts.app', ['active' => 'applicants', 'pageTitle' => 'ملف المتقدم', 'pageSubtitle' => 'تقدّم على «'.$application->job->title.'»'])

@section('title', 'ملف المتقدم')

@php($s = $application->status)
@php($profile = $application->candidate->candidateProfile)

@section('content')
<div style="max-width:760px">
  @if (session('status'))
    <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
  @endif

  <a href="{{ route('employer.applicants.index') }}" style="font-size:14px;font-weight:800;color:#7E8B84;display:inline-block;margin-bottom:14px">‹ كل المتقدمين</a>

  <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:26px">
    <div style="display:flex;align-items:center;gap:16px">
      <div style="width:64px;height:64px;border-radius:18px;background:radial-gradient(circle at 30% 26%,#F3ECD6,#F6D783);color:#7A5E0E;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900">{{ mb_substr($profile?->full_name ?? 'م', 0, 1) }}</div>
      <div style="flex:1">
        <div style="font-size:22px;font-weight:900;color:#22302A">{{ $profile?->full_name ?? 'مرشح' }}</div>
        <div style="font-size:14px;color:#7E8B84;font-weight:700;margin-top:2px">{{ $profile?->job_title ?? '—' }}</div>
      </div>
      <span style="font-size:12.5px;font-weight:800;padding:7px 14px;border-radius:10px;background:{{ $s->badgeBackground() }};color:{{ $s->badgeForeground() }}">{{ $s->employerLabel() }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:22px">
      <div style="background:#F7F9F7;border:1px solid #E5EAE6;border-radius:14px;padding:14px"><div style="font-size:12.5px;color:#869089;font-weight:700">العمر</div><div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:5px">{{ $profile?->age ?? '—' }}</div></div>
      <div style="background:#F7F9F7;border:1px solid #E5EAE6;border-radius:14px;padding:14px"><div style="font-size:12.5px;color:#869089;font-weight:700">سنوات الخبرة</div><div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:5px">{{ $profile?->years_of_experience ?? '—' }}</div></div>
      <div style="background:#F7F9F7;border:1px solid #E5EAE6;border-radius:14px;padding:14px"><div style="font-size:12.5px;color:#869089;font-weight:700">المدينة</div><div style="font-size:16px;font-weight:800;color:#284C3D;margin-top:5px">{{ $profile?->city?->name ?? '—' }}</div></div>
    </div>

    @if ($profile?->skills)
      <div style="margin-top:20px"><div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">المهارات</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          @foreach ($profile->skills as $skill)
            <span style="font-size:13px;font-weight:700;color:#56524A;background:#F4F6F3;border-radius:10px;padding:7px 13px">{{ $skill }}</span>
          @endforeach
        </div>
      </div>
    @endif

    @if ($profile?->bio)
      <div style="margin-top:20px"><div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:8px">نبذة</div>
        <div style="font-size:14.5px;line-height:1.85;color:#56524A;font-weight:500">{{ $profile->bio }}</div>
      </div>
    @endif

    @php($resumeUrl = $profile?->getFirstMediaUrl(\App\Models\CandidateProfile::RESUME_COLLECTION))
    @if ($resumeUrl)
      <div style="margin-top:20px">
        <a href="{{ $resumeUrl }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:12px 20px;border-radius:13px;text-decoration:none"><i class="iconsax" style="font-size:20px" icon-name="document-download"></i>تحميل السيرة الذاتية</a>
      </div>
    @endif

    @php($answers = $application->answerSummary())
    @if ($answers)
      <div style="margin-top:24px;padding-top:22px;border-top:1px solid #EEF1EE">
        <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:12px">إجابات نموذج التقديم</div>
        <div style="display:flex;flex-direction:column;gap:12px">
          @foreach ($answers as $answer)
            <div style="background:#F7F9F7;border:1px solid #E5EAE6;border-radius:14px;padding:14px 16px">
              <div style="font-size:12.5px;color:#869089;font-weight:700;margin-bottom:6px">{{ $answer['label'] }}</div>
              @if ($answer['file_url'] === null)
                <div style="font-size:15px;font-weight:700;color:#284C3D;white-space:pre-line">{{ $answer['value'] }}</div>
              @elseif ($answer['is_image'])
                <a href="{{ $answer['file_url'] }}" target="_blank" rel="noopener"><img src="{{ $answer['file_url'] }}" alt="{{ $answer['label'] }}" style="max-width:220px;max-height:220px;border-radius:12px;border:1px solid #E5EAE6"></a>
              @else
                <a href="{{ $answer['file_url'] }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:800;color:#284C3D;background:#F3ECD6;border-radius:11px;padding:9px 14px"><i class="iconsax" style="font-size:19px" icon-name="document-download"></i>عرض الملف</a>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    @endif

    @if ($s->value === 'accepted')
      <div style="margin-top:22px;background:#E7F4EC;border-radius:14px;padding:15px;display:flex;align-items:center;gap:12px">
        <i class="iconsax" style="font-size:24px;color:#1F8A4D" icon-name="messages-2"></i>
        <div style="font-size:14.5px;font-weight:700;color:#1F5C36">تم القبول — تواصل مع المرشح عبر {{ $application->contact_channel->label() }}. جوال المرشح: <span style="direction:ltr;display:inline-block">{{ $application->candidate->phone_e164 }}</span></div>
      </div>
    @elseif ($s->isDecidable())
      <div style="display:flex;gap:12px;margin-top:24px">
        <form method="POST" action="{{ route('employer.applicants.accept', $application->id) }}">@csrf<button class="btn" type="submit" style="background:#1F8A4D;color:#fff;border:none;cursor:pointer;font-size:15px;font-weight:800;padding:13px 28px;border-radius:14px">قبول المتقدم</button></form>
        <form method="POST" action="{{ route('employer.applicants.reject', $application->id) }}" onsubmit="return confirm('رفض هذا المتقدم؟')">@csrf<button class="btn" type="submit" style="background:#fff;color:#B23232;border:1px solid #F0D5D5;cursor:pointer;font-size:15px;font-weight:800;padding:13px 28px;border-radius:14px">رفض</button></form>
      </div>
    @endif
  </div>
</div>
@endsection
