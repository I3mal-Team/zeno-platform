@extends('site.layouts.app')

@section('title', $organization->name)
@section('description', Str::limit($organization->about ?? ('وظائف ' . $organization->name . ' على Zeno'), 150))

@section('content')
<x-site.header/>

<div style="max-width:1120px;margin:0 auto;padding:18px 26px 0;display:flex;align-items:center;gap:8px;font-size:13.5px;font-weight:700;color:#7E8B84">
  <a href="{{ route('site.jobs.index') }}" class="lnk">الوظائف</a>
  <i class="iconsax" style="font-size:15px" icon-name="chevron-left"></i>
  <span style="color:#284C3D">{{ $organization->name }}</span>
</div>

{{-- Cover + header card --}}
<section style="max-width:1120px;margin:0 auto;padding:16px 26px 0">
  <div style="height:170px;border-radius:24px 24px 0 0;background:linear-gradient(135deg,#315C4A,#223F31);position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(247,190,23,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(247,190,23,.06) 1px,transparent 1px);background-size:34px 34px"></div>
    <div style="position:absolute;top:-50px;left:60px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(247,190,23,.18),transparent 66%)"></div>
  </div>

  <div style="background:#fff;border:1px solid #E5EAE6;border-top:none;border-radius:0 0 24px 24px;padding:0 30px 26px">
    <div style="display:flex;align-items:flex-end;gap:20px;margin-top:-46px;flex-wrap:wrap">
      @php($logo = $organization->getFirstMediaUrl(App\Models\Organization::LOGO_COLLECTION))
      @if ($logo)
        <img src="{{ $logo }}" alt="{{ $organization->name }}" style="width:104px;height:104px;border-radius:26px;border:5px solid #fff;object-fit:cover;flex:0 0 auto;box-shadow:0 18px 34px -18px rgba(40,35,25,.4)">
      @else
        <div style="width:104px;height:104px;border-radius:26px;background:#F3ECD6;border:5px solid #fff;display:flex;align-items:center;justify-content:center;color:#8A6D12;flex:0 0 auto;font-size:42px;font-weight:900;box-shadow:0 18px 34px -18px rgba(40,35,25,.4)">{{ mb_substr($organization->name, 0, 1) }}</div>
      @endif

      <div style="flex:1;min-width:220px;padding-bottom:6px">
        <div style="display:flex;align-items:center;gap:9px">
          <span style="font-size:28px;font-weight:900;color:#22302A">{{ $organization->name }}</span>
          @if ($organization->isVerified())
            <i class="iconsax" style="font-size:22px;color:#2E86C1" icon-name="verify" title="منشأة موثّقة"></i>
          @endif
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:16px;margin-top:7px;font-size:14px;font-weight:600;color:#7E8B84">
          <span style="display:flex;align-items:center;gap:5px"><i class="iconsax" style="font-size:16px" icon-name="buildings-2"></i>{{ $organization->type->label() }}</span>
          @if ($organization->city)
            <span style="display:flex;align-items:center;gap:5px"><i class="iconsax" style="font-size:16px" icon-name="location"></i>{{ $organization->city->name }}</span>
          @endif
          <span style="display:flex;align-items:center;gap:5px"><i class="iconsax" style="font-size:16px" icon-name="briefcase"></i>{{ $jobs->count() }} وظيفة متاحة</span>
        </div>
      </div>

      @if ($jobs->isNotEmpty())
        <div style="display:flex;gap:10px;padding-bottom:6px">
          <a href="#jobs" class="btn" style="background:#C9A24B;color:#284C3D;font-size:14.5px;font-weight:800;padding:0 22px;height:46px;border-radius:13px;display:flex;align-items:center;gap:8px;box-shadow:0 14px 26px -14px rgba(247,190,23,.6)"><i class="iconsax" style="font-size:19px" icon-name="briefcase"></i>عرض الوظائف</a>
        </div>
      @endif
    </div>
  </div>
</section>

<div style="max-width:1120px;margin:0 auto;padding:20px 26px 60px;display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start" class="company-grid">

  {{-- Main --}}
  <div style="display:flex;flex-direction:column;gap:20px;min-width:0">
    @if ($organization->about)
      <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:26px">
        <div style="font-size:19px;font-weight:900;color:#22302A">نبذة عن المنشأة</div>
        <p style="font-size:15.5px;color:#566159;font-weight:500;line-height:1.85;margin-top:12px;white-space:pre-line">{{ $organization->about }}</p>
      </div>
    @endif

    <div id="jobs" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:19px;font-weight:900;color:#22302A">الوظائف المتاحة</div>
        <span style="font-size:13px;font-weight:800;color:#8A6D12;background:#F3ECD6;padding:5px 12px;border-radius:9px">{{ $jobs->count() }} وظيفة</span>
      </div>

      <div style="display:flex;flex-direction:column;gap:12px;margin-top:16px">
        @forelse ($jobs as $job)
          <a href="{{ route('site.jobs.show', $job->slug) }}" class="cardh" style="border:1px solid #E5EAE6;border-radius:16px;padding:18px;display:flex;gap:14px;align-items:flex-start">
            <div style="width:50px;height:50px;border-radius:14px;background:{{ $job->category->tintBackground() }};display:flex;align-items:center;justify-content:center;color:{{ $job->category->tintForeground() }};flex:0 0 auto"><i class="iconsax" style="font-size:25px" icon-name="{{ $job->category->icon }}"></i></div>
            <div style="flex:1;min-width:0">
              <div style="font-size:17px;font-weight:800;color:#22302A">{{ $job->title }}</div>
              <div style="font-size:13.5px;color:#7E8B84;font-weight:600;margin-top:3px">@if ($job->district){{ $job->district->name }} · @endif{{ $job->published_at?->diffForHumans() }}</div>
              <div style="display:flex;gap:8px;margin-top:10px">
                <span style="font-size:12px;font-weight:700;color:#5A8A2E;background:#EEF5E6;padding:4px 10px;border-radius:8px">{{ $job->workType->name }}</span>
              </div>
            </div>
            <div style="text-align:left;flex:0 0 auto">
              <div style="font-size:18px;font-weight:900;color:#284C3D">{{ $job->formattedSalary() }}</div>
              <div style="font-size:11px;color:#869089;font-weight:700">{{ $job->salaryUnit->name }}</div>
            </div>
          </a>
        @empty
          <div style="padding:30px 18px;text-align:center;color:#7E8B84;font-size:14.5px;font-weight:600">لا توجد وظائف متاحة حالياً في هذه المنشأة.</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px">
      <div style="font-size:16px;font-weight:900;color:#22302A;margin-bottom:14px">معلومات</div>
      <div style="display:flex;flex-direction:column;gap:14px">
        @php($info = array_filter([
          ['ic' => 'buildings-2', 'k' => 'نوع المنشأة', 'v' => $organization->type->label()],
          $organization->city ? ['ic' => 'location', 'k' => 'المدينة', 'v' => $organization->city->name] : null,
          ['ic' => 'verify', 'k' => 'حالة التوثيق', 'v' => $organization->verification_status->label()],
          $organization->created_at ? ['ic' => 'calendar-1', 'k' => 'على Zeno منذ', 'v' => $organization->created_at->translatedFormat('F Y')] : null,
        ]))
        @foreach ($info as $item)
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:12px;background:#EFF2EE;display:flex;align-items:center;justify-content:center;color:#7E8B84;flex:0 0 auto"><i class="iconsax" style="font-size:19px" icon-name="{{ $item['ic'] }}"></i></div>
            <div style="line-height:1.3;min-width:0">
              <div style="font-size:12px;color:#869089;font-weight:700">{{ $item['k'] }}</div>
              <div style="font-size:14.5px;font-weight:800;color:#284C3D">{{ $item['v'] }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;text-align:center">
      <x-site.report-button target="organization" :id="$organization->id" label="إبلاغ عن هذه المنشأة"/>
    </div>
  </div>
</div>

<x-site.footer/>
@endsection
