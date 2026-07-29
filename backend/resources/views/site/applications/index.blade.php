@extends('site.layouts.app')

@section('title', 'طلباتي')

@section('content')
<x-site.header/>

<div style="max-width:760px;margin:0 auto;padding:24px 26px 56px">
  <div style="background:#26221F;border-radius:24px;padding:24px 26px;margin-bottom:20px">
    <h1 style="font-size:24px;font-weight:900;color:#fff">طلباتي</h1>
    <p style="font-size:14px;color:#C7C2B8;font-weight:600;margin-top:5px">{{ $applications->count() }} طلبات — تابع حالة كل طلب لحظة بلحظة</p>
  </div>

  @if (session('status'))
    <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
  @endif

  @forelse ($applications as $app)
    @php($cat = $app->job->category)
    @php($state = $app->status)
    @php($s = $state->value)
    @php($reached = in_array($s, ['review', 'accepted', 'rejected']))
    @php($decided = in_array($s, ['accepted', 'rejected']))
    @php($pending = '#E7E3DA')
    @php($decisionColor = $s === 'accepted' ? '#1F8A4D' : ($s === 'rejected' ? '#B23232' : $pending))
    @php($dot0 = $s === 'withdrawn' ? '#8A857A' : '#F7BE17')
    @php($dot1 = $s === 'withdrawn' ? '#8A857A' : ($reached ? '#F7BE17' : $pending))
    @php($line1 = $reached ? '#F7BE17' : $pending)

    <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:18px;margin-bottom:14px">
      <div style="display:flex;align-items:flex-start;gap:13px">
        <div style="width:48px;height:48px;border-radius:14px;background:{{ $cat?->tintBackground() ?? '#F4F1EA' }};color:{{ $cat?->tintForeground() ?? '#8A857A' }};display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:24px" icon-name="{{ $cat?->icon ?? 'briefcase' }}"></i></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:16.5px;font-weight:800;color:#211F20">{{ $app->job->title }}</div>
          <div style="font-size:13px;color:#8A857A;font-weight:600;margin-top:2px">{{ $app->job->organization->name ?? '' }}</div>
        </div>
        <span style="font-size:12.5px;font-weight:800;color:{{ $state->badgeForeground() }};background:{{ $state->badgeBackground() }};border-radius:9px;padding:6px 11px;flex:0 0 auto">{{ $state->candidateLabel() }}</span>
      </div>

      {{-- Three-step tracker: applied → in review → decision --}}
      <div style="display:flex;align-items:center;margin-top:16px;padding:0 4px">
        <span style="width:11px;height:11px;border-radius:50%;background:{{ $dot0 }};flex:0 0 auto"></span>
        <span style="flex:1;height:3px;background:{{ $line1 }}"></span>
        <span style="width:11px;height:11px;border-radius:50%;background:{{ $dot1 }};flex:0 0 auto"></span>
        <span style="flex:1;height:3px;background:{{ $decided ? $decisionColor : $pending }}"></span>
        <span style="width:11px;height:11px;border-radius:50%;background:{{ $decided ? $decisionColor : $pending }};flex:0 0 auto"></span>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:7px;font-size:10.5px;font-weight:700;color:#9A958A">
        <span>تم التقديم</span><span>قيد المراجعة</span><span>القرار</span>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:15px;padding-top:13px;border-top:1px solid #F2F0EA">
        <span style="font-size:13px;font-weight:700;color:#9A958A;direction:ltr">طلب #{{ $app->reference_number }}</span>
        @if ($s === 'accepted' && $app->conversation)
          <a href="{{ route('messages.show', $app->conversation->uuid) }}" style="display:flex;align-items:center;gap:6px;text-decoration:none;background:#2B2724;color:#fff;font-weight:800;font-size:13px;padding:9px 15px;border-radius:11px"><i class="iconsax" style="font-size:17px" icon-name="messages-2"></i>تواصل الآن</a>
        @elseif ($state->isDecidable())
          <form method="POST" action="{{ route('applications.withdraw', $app->reference_number) }}" onsubmit="return confirm('سحب طلبك على هذه الوظيفة؟')">
            @csrf
            <button type="submit" style="background:none;border:none;font-family:inherit;font-size:13px;font-weight:800;color:#B23232;cursor:pointer">سحب الطلب</button>
          </form>
        @endif
      </div>
    </div>
  @empty
    <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:52px 26px;text-align:center">
      <div style="width:84px;height:84px;border-radius:26px;background:#F6F5F1;display:flex;align-items:center;justify-content:center;color:#C9C4B9;margin:0 auto"><i class="iconsax" style="font-size:40px" icon-name="document-text-1"></i></div>
      <div style="font-size:18px;font-weight:800;color:#211F20;margin-top:16px">لا توجد طلبات بعد</div>
      <div style="font-size:14px;color:#8A857A;font-weight:600;margin-top:6px">تصفّح الوظائف وقدّم على أول فرصة تناسبك.</div>
      <a href="{{ route('site.jobs.index') }}" style="display:inline-flex;align-items:center;gap:8px;margin-top:18px;text-decoration:none;background:#F7BE17;color:#2B2724;font-weight:800;font-size:14.5px;padding:12px 22px;border-radius:14px">تصفّح الوظائف</a>
    </div>
  @endforelse
</div>
@endsection
