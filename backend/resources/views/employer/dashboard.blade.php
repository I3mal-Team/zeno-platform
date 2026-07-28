@extends('employer.layouts.app', ['active' => 'overview', 'pageTitle' => $organization->name, 'pageSubtitle' => 'مرحبًا بعودتك، إليك ملخّص نشاطك'])

@section('title', 'نظرة عامة')

@section('content')
<div class="view">
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
    @foreach ($stats as $stat)
      <div class="cardh" style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:20px">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="width:44px;height:44px;border-radius:13px;background:{{ $stat['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $stat['fg'] }}"><i class="iconsax" style="font-size:22px" icon-name="{{ $stat['icon'] }}"></i></div>
          @if ($stat['delta'])
            <span style="font-size:12.5px;font-weight:800;color:{{ $stat['deltaFg'] }};background:{{ $stat['deltaBg'] }};padding:4px 9px;border-radius:8px">{{ $stat['delta'] }}</span>
          @endif
        </div>
        <div style="font-size:32px;font-weight:900;color:#211F20;margin-top:16px">{{ $stat['value'] }}</div>
        <div style="font-size:14px;color:#8A857A;font-weight:700;margin-top:2px">{{ $stat['label'] }}</div>
      </div>
    @endforeach
  </div>

  <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:16px;margin-top:16px">
    {{-- Weekly applications --}}
    <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:24px">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-size:18px;font-weight:800;color:#211F20">الطلبات هذا الأسبوع</div>
          <div style="font-size:13.5px;color:#8A857A;font-weight:600;margin-top:2px">إجمالي {{ $chartTotal }} طلبًا@if ($chartDelta !== null) · {{ $chartDelta >= 0 ? '+' : '' }}{{ $chartDelta }}% عن الأسبوع الماضي @endif</div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:800;color:#8A6D12"><span style="width:10px;height:10px;border-radius:3px;background:#F7BE17"></span>الطلبات</div>
      </div>
      <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:14px;height:190px;margin-top:24px">
        @foreach ($chart as $bar)
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:9px;height:100%;justify-content:flex-end">
            <div style="width:100%;max-width:38px;border-radius:9px 9px 4px 4px;background:{{ $bar['highlight'] ? '#F7BE17' : '#EDE6D2' }};height:{{ max($bar['height'], 3) }}%"></div>
            <div style="font-size:12.5px;color:#9A958A;font-weight:700">{{ $bar['day'] }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Quick actions --}}
    <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:24px">
      <div style="font-size:18px;font-weight:800;color:#211F20">إجراءات سريعة</div>
      <div style="display:flex;flex-direction:column;gap:11px;margin-top:16px">
        <a href="{{ route('employer.jobs.create') }}" class="btn" style="display:flex;align-items:center;gap:13px;background:#FBF6E7;border:1px solid #F1E6C4;border-radius:14px;padding:14px">
          <div style="width:42px;height:42px;border-radius:12px;background:#F7BE17;display:flex;align-items:center;justify-content:center;color:#2B2724;flex:0 0 auto"><i class="iconsax" style="font-size:21px" icon-name="add"></i></div>
          <div style="flex:1"><div style="font-size:15px;font-weight:800;color:#2B2724">نشر وظيفة جديدة</div><div style="font-size:12.5px;color:#9A857A;font-weight:600">تصل للباحثين القريبين فورًا</div></div>
          <i class="iconsax" style="font-size:19px;color:#C9A94F" icon-name="chevron-left"></i>
        </a>
        <a href="{{ route('employer.applicants.index') }}" class="btn" style="display:flex;align-items:center;gap:13px;background:#F7F5EF;border:1px solid #EBE7DE;border-radius:14px;padding:14px">
          <div style="width:42px;height:42px;border-radius:12px;background:#E7F4EC;display:flex;align-items:center;justify-content:center;color:#1F8A4D;flex:0 0 auto"><i class="iconsax" style="font-size:21px" icon-name="task-list"></i></div>
          <div style="flex:1"><div style="font-size:15px;font-weight:800;color:#2B2724">مراجعة المتقدّمين</div><div style="font-size:12.5px;color:#9A958A;font-weight:600">{{ $newApplications > 0 ? 'لديك '.$newApplications.' طلبات جديدة' : 'لا توجد طلبات جديدة' }}</div></div>
          <i class="iconsax" style="font-size:19px;color:#C9C4B9" icon-name="chevron-left"></i>
        </a>
        <a href="{{ route('employer.messages.index') }}" class="btn" style="display:flex;align-items:center;gap:13px;background:#F7F5EF;border:1px solid #EBE7DE;border-radius:14px;padding:14px">
          <div style="width:42px;height:42px;border-radius:12px;background:#E2EEF4;display:flex;align-items:center;justify-content:center;color:#2E6E8A;flex:0 0 auto"><i class="iconsax" style="font-size:21px" icon-name="messages-2"></i></div>
          <div style="flex:1"><div style="font-size:15px;font-weight:800;color:#2B2724">الرسائل</div><div style="font-size:12.5px;color:#9A958A;font-weight:600">تواصل مع المتقدّمين</div></div>
          <i class="iconsax" style="font-size:19px;color:#C9C4B9" icon-name="chevron-left"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- Recent applicants --}}
  <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:8px 8px 6px;margin-top:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 18px 12px">
      <div style="font-size:18px;font-weight:800;color:#211F20">أحدث المتقدّمين</div>
      <a href="{{ route('employer.applicants.index') }}" style="font-size:14px;font-weight:800;color:#8A6D12;display:flex;align-items:center;gap:5px">عرض الكل<i class="iconsax" style="font-size:17px" icon-name="arrow-left"></i></a>
    </div>

    @forelse ($recent as $application)
      @php($profile = $application->candidate->candidateProfile)
      @php($name = $profile?->full_name ?? 'مرشح')
      @php($state = $application->status)
      <a href="{{ route('employer.applicants.show', $application->id) }}" class="rowh" style="display:flex;align-items:center;gap:14px;padding:13px 18px;border-radius:14px">
        <x-employer.avatar :name="$name" :size="46" :radius="14" :font="17"/>
        <div style="flex:1;min-width:0">
          <div style="font-size:15.5px;font-weight:800;color:#2B2724">{{ $name }}</div>
          <div style="font-size:13px;color:#8A857A;font-weight:600;margin-top:2px">{{ $application->job->title }}@if ($profile?->city) · {{ $profile->city->name }}@endif @if ($profile?->years_of_experience) · {{ $profile->years_of_experience }} سنوات خبرة @endif</div>
        </div>
        <div style="font-size:13px;color:#9A958A;font-weight:700">{{ $application->created_at->diffForHumans() }}</div>
        <span style="font-size:12.5px;font-weight:800;color:{{ $state->badgeForeground() }};background:{{ $state->badgeBackground() }};padding:6px 12px;border-radius:9px">{{ $state->employerLabel() }}</span>
      </a>
    @empty
      <div style="padding:34px 18px 40px;text-align:center">
        <div style="width:64px;height:64px;border-radius:20px;background:#F4F1EA;display:flex;align-items:center;justify-content:center;color:#A39D90;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="user-2-search"></i></div>
        <div style="font-size:17px;font-weight:800;color:#211F20;margin-top:14px">لا يوجد متقدمون بعد</div>
        <div style="font-size:14px;color:#8A857A;font-weight:600;margin-top:5px">انشر إعلاناً لتبدأ باستقبال الطلبات.</div>
      </div>
    @endforelse
  </div>
</div>
@endsection
