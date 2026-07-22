@extends('employer.layouts.app', ['active' => 'overview', 'pageTitle' => $organization->name, 'pageSubtitle' => 'مرحبًا بعودتك، إليك ملخّص نشاطك'])

@section('title', 'نظرة عامة')

@section('content')
<div class="view">
  <div class="g-4" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
    @foreach ($stats as $stat)
      <div class="cardh" style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:20px">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="width:44px;height:44px;border-radius:13px;background:{{ $stat['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $stat['fg'] }}"><i class="iconsax" style="font-size:22px" icon-name="{{ $stat['icon'] }}"></i></div>
        </div>
        <div style="font-size:32px;font-weight:900;color:#211F20;margin-top:16px">{{ $stat['value'] }}</div>
        <div style="font-size:13.5px;color:#8A857A;font-weight:700;margin-top:2px">{{ $stat['label'] }}</div>
      </div>
    @endforeach
  </div>

  <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:44px 26px;margin-top:16px;text-align:center">
    <div style="width:64px;height:64px;border-radius:20px;background:#F4F1EA;display:flex;align-items:center;justify-content:center;color:#A39D90;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="briefcase"></i></div>
    <div style="font-size:19px;font-weight:800;color:#2B2724;margin-top:18px">لم تنشر أي إعلان بعد</div>
    <div style="font-size:15px;color:#8A857A;font-weight:600;margin-top:6px">انشر أول إعلان لتبدأ باستقبال المتقدّمين.</div>
  </div>
</div>
@endsection
