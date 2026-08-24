@props(['jobs'])

<div style="display:flex;justify-content:center;position:relative">
  <div class="hide-sm" style="position:absolute;top:40px;right:6px;z-index:3;background:#fff;border:1px solid #F0EDE6;border-radius:16px;padding:11px 14px;box-shadow:0 22px 40px -20px rgba(40,35,25,.4);display:flex;align-items:center;gap:9px;animation:floaty 5s ease-in-out infinite">
    <div style="width:38px;height:38px;border-radius:11px;background:#E7F4EC;display:flex;align-items:center;justify-content:center;color:#1F8A4D"><i class="iconsax" style="font-size:20px" icon-name="tick-circle"></i></div>
    <div style="line-height:1.3"><div style="font-size:13px;font-weight:800;color:#284C3D">تم قبول طلبك</div><div style="font-size:11.5px;color:#7E8B84;font-weight:600">كافيه ميلاج · باريستا</div></div>
  </div>

  <div class="hide-sm" style="position:absolute;bottom:56px;left:0;z-index:3;background:#25D366;border-radius:15px;padding:11px 15px;box-shadow:0 22px 40px -18px rgba(37,211,102,.6);display:flex;align-items:center;gap:9px;animation:floaty2 4.2s ease-in-out infinite">
    <i class="iconsax" style="font-size:22px;color:#fff" icon-name="message-text"></i>
    <div style="line-height:1.3;color:#fff"><div style="font-size:13px;font-weight:800">تواصل عبر واتساب</div><div style="font-size:11.5px;opacity:.85;font-weight:600">رسالة جاهزة للإرسال</div></div>
  </div>

  <div style="width:290px;height:590px;border-radius:44px;background:#284C3D;padding:9px;box-shadow:0 44px 80px -30px rgba(40,35,25,.55);position:relative">
    <div style="width:100%;height:100%;border-radius:36px;background:#F4F6F3;overflow:hidden;position:relative">
      <div style="background:linear-gradient(155deg,#315C4A,#223F31);padding:44px 18px 20px;border-radius:0 0 26px 26px">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border-radius:12px;padding:7px 11px"><i class="iconsax" style="font-size:15px;color:#C9A24B" icon-name="location"></i><span style="font-size:13px;font-weight:800;color:#fff">الرياض</span></div>
          <div style="width:36px;height:36px;border-radius:11px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:#EDE9E0"><i class="iconsax" style="font-size:18px" icon-name="bell-2"></i></div>
        </div>
        <div style="font-size:15px;font-weight:800;color:#fff;margin-top:14px">وظيفتك القادمة قريبة منك</div>
        <div style="display:flex;align-items:center;gap:8px;background:#fff;border-radius:13px;padding:10px 13px;margin-top:12px"><i class="iconsax" style="font-size:17px;color:#869089" icon-name="search-normal-1"></i><span style="font-size:12.5px;color:#869089;font-weight:600">ابحث عن وظيفة…</span><span style="margin-right:auto;width:26px;height:26px;border-radius:8px;background:#C9A24B;display:flex;align-items:center;justify-content:center;color:#284C3D"><i class="iconsax" style="font-size:14px" icon-name="filter-search"></i></span></div>
      </div>

      <div style="padding:14px;display:flex;flex-direction:column;gap:10px">
        @foreach ($jobs as $job)
          <div style="background:#fff;border:1px solid #E5EAE6;border-radius:17px;padding:13px;display:flex;gap:11px;align-items:flex-start">
            <div style="width:44px;height:44px;border-radius:13px;background:{{ $job->category->tintBackground() }};display:flex;align-items:center;justify-content:center;color:{{ $job->category->tintForeground() }};flex:0 0 auto"><i class="iconsax" style="font-size:22px" icon-name="{{ $job->category->icon }}"></i></div>
            <div style="flex:1">
              <div style="font-size:14px;font-weight:800;color:#284C3D">{{ $job->title }}</div>
              <div style="font-size:11.5px;color:#7E8B84;font-weight:600;margin-top:2px">{{ $job->organization->name }}@if ($job->district) · {{ $job->district->name }}@endif</div>
              <div style="display:flex;gap:6px;margin-top:8px"><span style="font-size:10.5px;font-weight:700;color:#5A8A2E;background:#EEF5E6;padding:3px 8px;border-radius:7px">{{ $job->workType->name }}</span></div>
            </div>
            <div style="text-align:left"><div style="font-size:14px;font-weight:900;color:#284C3D">{{ $job->formattedSalary() }}</div><div style="font-size:10px;color:#869089;font-weight:600">{{ $job->salaryUnit->name }}</div></div>
          </div>
        @endforeach
      </div>

      <div style="position:absolute;bottom:12px;left:14px;right:14px;height:56px;background:#284C3D;border-radius:20px;display:flex;align-items:center;justify-content:space-around;box-shadow:0 18px 30px -16px rgba(40,35,25,.5)">
        @foreach (['home-2' => true, 'gps' => false, 'task-list' => false, 'messages-2' => false, 'user-1' => false] as $icon => $active)
          <i class="iconsax" style="font-size:22px;color:{{ $active ? '#C9A24B' : '#7E8B84' }}" icon-name="{{ $icon }}"></i>
        @endforeach
      </div>
    </div>
  </div>
</div>
