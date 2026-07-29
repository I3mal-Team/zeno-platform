@extends('site.layouts.app')

@section('title', 'لوحتي')

@section('content')
<x-site.header/>

<div style="max-width:1080px;margin:0 auto;padding:28px 26px 56px">

  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px">
    <div>
      <h1 style="font-size:25px;font-weight:900;color:#211F20">أهلاً{{ $fullName ? '، '.\Illuminate\Support\Str::before($fullName, ' ') : '' }} 👋</h1>
      <p style="font-size:14.5px;color:#8A857A;font-weight:600;margin-top:5px">هذه نظرة سريعة على طلباتك ومحادثاتك.</p>
    </div>
    <a href="{{ route('profile.edit') }}" style="display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #E7E3DA;color:#2B2724;font-size:14px;font-weight:800;padding:11px 16px;border-radius:13px;text-decoration:none"><i class="iconsax" style="font-size:18px;color:#8A6D12" icon-name="edit-2"></i>تعديل ملفي</a>
  </div>

  {{-- Nudge to finish the optional fields until the profile is fully complete. --}}
  @if ($completion < 100)
    <a href="{{ route('profile.edit') }}" style="display:block;text-decoration:none;background:linear-gradient(135deg,#FDF3D6,#FBEBC0);border:1px solid #F3E3B0;border-radius:20px;padding:18px 20px;margin-bottom:22px">
      <div style="display:flex;align-items:center;gap:13px">
        <div style="width:46px;height:46px;border-radius:14px;background:#F7BE17;color:#2B2724;display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:24px" icon-name="user-edit"></i></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:15.5px;font-weight:900;color:#5C4A0F">أكمل ملفك ليزيد ظهورك لأصحاب العمل</div>
          <div style="font-size:13px;font-weight:700;color:#8A6D12;margin-top:2px">نسبة الاكتمال {{ $completion }}% — أضف مهنتك وخبرتك ونبذة عنك.</div>
        </div>
        <span style="font-size:13px;font-weight:900;color:#5C4A0F;flex:0 0 auto">أكمل الآن ←</span>
      </div>
      <div style="height:8px;border-radius:6px;background:rgba(92,74,15,.14);margin-top:14px;overflow:hidden">
        <div style="height:100%;width:{{ max(6, $completion) }}%;background:#F7BE17;border-radius:6px"></div>
      </div>
    </a>
  @endif

  {{-- Overview stat tiles --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:24px">
    @foreach ($stats as $stat)
      <div style="background:#fff;border:1px solid #EDEAE2;border-radius:18px;padding:18px">
        <div style="width:44px;height:44px;border-radius:13px;background:{{ $stat['bg'] }};color:{{ $stat['fg'] }};display:flex;align-items:center;justify-content:center"><i class="iconsax" style="font-size:22px" icon-name="{{ $stat['icon'] }}"></i></div>
        <div style="font-size:26px;font-weight:900;color:#211F20;margin-top:12px">{{ $stat['value'] }}</div>
        <div style="font-size:13px;color:#8A857A;font-weight:700;margin-top:2px">{{ $stat['label'] }}</div>
      </div>
    @endforeach
  </div>

  <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start">

    {{-- Recent applications --}}
    <div style="flex:1.4;min-width:320px;background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:20px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div style="font-size:17px;font-weight:900;color:#211F20">أحدث طلباتك</div>
        <a href="{{ route('applications.index') }}" style="font-size:13px;font-weight:800;color:#8A6D12;text-decoration:none">عرض الكل</a>
      </div>

      @forelse ($applications->take(3) as $app)
        @php($state = $app->status)
        @php($cat = $app->job->category)
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:{{ ! $loop->last ? '1px solid #F3F1EB' : 'none' }}">
          <div style="width:44px;height:44px;border-radius:13px;background:{{ $cat?->tintBackground() ?? '#F4F1EA' }};color:{{ $cat?->tintForeground() ?? '#8A857A' }};display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:22px" icon-name="{{ $cat?->icon ?? 'briefcase' }}"></i></div>
          <div style="flex:1;min-width:0">
            <div style="font-size:15px;font-weight:800;color:#211F20;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $app->job->title }}</div>
            <div style="font-size:12.5px;color:#8A857A;font-weight:600;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $app->job->organization->name ?? '' }}</div>
          </div>
          <span style="font-size:12px;font-weight:800;color:{{ $state->badgeForeground() }};background:{{ $state->badgeBackground() }};border-radius:9px;padding:5px 10px;flex:0 0 auto">{{ $state->candidateLabel() }}</span>
        </div>
      @empty
        <div style="padding:30px 10px;text-align:center;color:#8A857A;font-size:14px;font-weight:600;line-height:1.7">لم تقدّم على أي وظيفة بعد.<br><a href="{{ route('site.jobs.index') }}" style="color:#8A6D12;font-weight:800;text-decoration:none">تصفّح الوظائف</a></div>
      @endforelse
    </div>

    {{-- Recent conversations --}}
    <div style="flex:1;min-width:300px;background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:20px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div style="font-size:17px;font-weight:900;color:#211F20">رسائلك</div>
        <a href="{{ route('messages.index') }}" style="font-size:13px;font-weight:800;color:#8A6D12;text-decoration:none">عرض الكل</a>
      </div>

      @forelse ($conversations->take(3) as $conversation)
        @php($name = $conversation->organization->name)
        <a href="{{ route('messages.show', $conversation->uuid) }}" style="display:flex;align-items:center;gap:12px;padding:11px 0;text-decoration:none;border-bottom:{{ ! $loop->last ? '1px solid #F3F1EB' : 'none' }}">
          <div style="width:44px;height:44px;border-radius:13px;background:#2B2724;color:#F7BE17;display:flex;align-items:center;justify-content:center;flex:0 0 auto;font-size:16px;font-weight:900">{{ mb_substr($name, 0, 1) }}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:14.5px;font-weight:800;color:#211F20;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $name }}</div>
            <div style="font-size:12px;color:#8A857A;font-weight:600;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ optional($conversation->application->job)->title ?? '' }}</div>
          </div>
          @if (($conversation->unread_count ?? 0) > 0)
            <span style="min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:#F7BE17;color:#2B2724;font-size:11px;font-weight:900;display:flex;align-items:center;justify-content:center;flex:0 0 auto">{{ $conversation->unread_count }}</span>
          @endif
        </a>
      @empty
        <div style="padding:30px 10px;text-align:center;color:#8A857A;font-size:14px;font-weight:600;line-height:1.7">لا توجد محادثات بعد.<br>تُفتح تلقائياً بعد قبول تقديمك.</div>
      @endforelse
    </div>

  </div>
</div>
@endsection
