@props(['job'])

@php($orgLogo = $job->organization->getFirstMediaUrl(\App\Models\Organization::LOGO_COLLECTION))
<a href="{{ route('site.jobs.show', $job->slug) }}" class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:20px;display:flex;gap:15px;align-items:flex-start">
  <div style="width:56px;height:56px;border-radius:16px;background:{{ $orgLogo ? '#fff' : $job->category->tintBackground() }};border:1px solid #EEF1EE;display:flex;align-items:center;justify-content:center;color:{{ $job->category->tintForeground() }};flex:0 0 auto;overflow:hidden">
    @if ($orgLogo)
      <img src="{{ $orgLogo }}" alt="{{ $job->organization->name }}" style="width:100%;height:100%;object-fit:cover">
    @else
      <i class="iconsax" style="font-size:27px" icon-name="{{ $job->category->icon }}"></i>
    @endif
  </div>

  <div style="flex:1">
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <span style="font-size:18px;font-weight:800;color:#284C3D">{{ $job->title }}</span>
      @if ($job->organization->isVerified())
        <i class="iconsax" style="font-size:17px;color:#2E86C1" icon-name="verify"></i>
      @endif
    </div>
    <div style="font-size:14px;color:#7E8B84;font-weight:600;margin-top:4px">{{ $job->organization->name }}@if ($job->district) · {{ $job->district->name }}@endif</div>
    <div style="display:flex;align-items:center;gap:8px;margin-top:12px">
      <span style="font-size:12.5px;font-weight:700;color:#5A8A2E;background:#EEF5E6;padding:4px 10px;border-radius:8px">{{ $job->workType->name }}</span>
      <span style="margin-right:auto;font-size:17px;font-weight:900;color:#284C3D">{{ $job->formattedSalary() }} <span style="font-size:12px;color:#869089;font-weight:700">{{ $job->salaryUnit->name }}</span></span>
    </div>
  </div>
</a>
