@extends('employer.layouts.app', ['active' => 'jobs', 'pageTitle' => 'تعديل الإعلان', 'pageSubtitle' => $job->title])

@section('title', 'تعديل الإعلان')

@use('App\Enums\JobStatus')

@section('content')
@include('employer.jobs._form', [
    'action' => route('employer.jobs.update', $job->uuid),
    'method' => 'PUT',
    'submitLabel' => 'حفظ التعديلات',
    'job' => $job,
])

@if ($job->status->canTransitionTo(JobStatus::Closed))
  {{-- The listing card carries only edit and pause, so closing for good lives here. --}}
  <div style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;margin-top:16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:220px">
      <div style="font-size:16px;font-weight:800;color:#22302A">إغلاق الإعلان</div>
      <div style="font-size:13.5px;color:#7E8B84;font-weight:600;margin-top:3px">يُخفي الإعلان نهائياً ويوقف استقبال الطلبات. لا يمكن التراجع.</div>
    </div>
    <form method="POST" action="{{ route('employer.jobs.close', $job->uuid) }}" onsubmit="return confirm('إغلاق الإعلان نهائياً؟')">
      @csrf
      <button type="submit" class="btn" style="height:42px;padding:0 20px;border-radius:12px;background:#FBEDEA;border:none;cursor:pointer;color:#B2453A;font-family:inherit;font-size:14px;font-weight:800;display:flex;align-items:center;gap:7px"><i class="iconsax" style="font-size:18px" icon-name="x"></i>إغلاق الإعلان</button>
    </form>
  </div>
@endif
@endsection
