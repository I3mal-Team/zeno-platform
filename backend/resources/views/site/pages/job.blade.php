@extends('site.layouts.app')

@section('title', $job->title)
@section('description', Str::limit($job->description ?? '', 150))

@section('content')
<x-site.header/>

<div style="max-width:820px;margin:0 auto;padding:34px 26px">
  <a href="{{ route('site.jobs.index') }}" class="lnk" style="font-size:14px;font-weight:700;color:#9A958A">‹ كل الوظائف</a>

  <div style="background:linear-gradient(155deg,#34302B,#26221F);border-radius:26px;padding:34px;margin-top:16px;color:#fff">
    <div style="display:flex;gap:16px;align-items:flex-start">
      <div style="width:62px;height:62px;border-radius:18px;background:{{ $job->category->tintBackground() }};display:flex;align-items:center;justify-content:center;color:{{ $job->category->tintForeground() }};flex:0 0 auto"><i class="iconsax" style="font-size:30px" icon-name="{{ $job->category->icon }}"></i></div>
      <div style="flex:1">
        <h1 style="font-size:28px;font-weight:900">{{ $job->title }}</h1>
        <div style="display:flex;align-items:center;gap:7px;margin-top:6px;font-size:15px;color:#C7C2B8;font-weight:600">
          <a href="{{ route('site.companies.show', $job->organization->slug) }}" class="lnk" style="color:inherit;text-decoration:underline;text-underline-offset:3px">{{ $job->organization->name }}</a>
          @if ($job->organization->isVerified())<i class="iconsax" style="font-size:17px;color:#5FB0E8" icon-name="verify"></i>@endif
        </div>
      </div>
    </div>

    <div class="g-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:26px">
      @foreach ([['الراتب', $job->formattedSalary().' '.$job->salaryUnit->name], ['نوع الدوام', $job->workType->name], ['الموقع', $job->district?->name ?? $job->city->name]] as [$label, $value])
        <div style="background:rgba(255,255,255,.06);border-radius:15px;padding:15px">
          <div style="font-size:12.5px;color:#B7B1A6;font-weight:700">{{ $label }}</div>
          <div style="font-size:16px;font-weight:800;margin-top:4px">{{ $value }}</div>
        </div>
      @endforeach
    </div>
  </div>

  @if ($job->description)
    <div style="background:#fff;border:1px solid #EFEDE6;border-radius:22px;padding:28px;margin-top:16px">
      <div style="font-size:18px;font-weight:800;color:#2B2724">الوصف الوظيفي</div>
      <p style="font-size:15.5px;color:#6C665C;font-weight:500;line-height:1.8;margin-top:12px;white-space:pre-line">{{ $job->description }}</p>
    </div>
  @endif

  <div style="background:#fff;border:1px solid #EFEDE6;border-radius:22px;padding:28px;margin-top:16px">
    <div style="font-size:18px;font-weight:800;color:#2B2724">تفاصيل إضافية</div>
    <div class="g-2" style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-top:16px">
      @foreach (array_filter([
        'الجنس المطلوب' => $job->genderRequirement->name,
        'الجنسية المطلوبة' => $job->nationalityRequirement->name,
        'عدد المطلوبين' => $job->vacancies_count,
        'ساعات الأسبوع' => $job->hours_per_week,
        'ينتهي في' => $job->expires_at?->translatedFormat('j F Y'),
      ]) as $label => $value)
        <div>
          <div style="font-size:13px;color:#9A958A;font-weight:700">{{ $label }}</div>
          <div style="font-size:15.5px;font-weight:700;color:#2B2724;margin-top:2px">{{ $value }}</div>
        </div>
      @endforeach
    </div>
  </div>

  @if (session('status'))
    <div style="margin-top:20px;background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700">{{ session('status') }}</div>
  @endif

  @auth
    @if (auth()->user()->role === 'candidate')
      <form method="POST" action="{{ route('site.jobs.apply', $job->slug) }}" style="margin-top:20px">
        @csrf
        <button type="submit" class="btn" style="width:100%;display:flex;align-items:center;justify-content:center;gap:9px;background:#F7BE17;color:#2B2724;border:none;font-family:inherit;font-size:16px;font-weight:800;padding:16px;border-radius:16px;cursor:pointer;box-shadow:0 16px 30px -14px rgba(247,190,23,.5)">
          <i class="iconsax" style="font-size:22px" icon-name="send-2"></i>قدّم على الوظيفة
        </button>
      </form>
    @else
      <div style="margin-top:20px;background:#FDF3D6;color:#8A6D12;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;text-align:center">سجّل الدخول بحساب باحث عن عمل للتقديم على الوظائف.</div>
    @endif
  @else
    <a href="{{ route('login', ['role' => 'candidate']) }}" class="btn" style="display:flex;align-items:center;justify-content:center;gap:9px;background:#F7BE17;color:#2B2724;font-size:16px;font-weight:800;padding:16px;border-radius:16px;margin-top:20px;box-shadow:0 16px 30px -14px rgba(247,190,23,.5)">
      <i class="iconsax" style="font-size:22px" icon-name="user-1"></i>سجّل الدخول للتقديم
    </a>
  @endauth

  <div style="margin-top:22px;padding-top:18px;border-top:1px solid #F0EEE7;text-align:center">
    <x-site.report-button target="job" :id="$job->id"/>
  </div>
</div>

<x-site.footer/>
@endsection
