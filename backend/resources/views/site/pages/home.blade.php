@extends('site.layouts.app')

@section('title', 'وظيفتك القريبة على بُعد خطوة')

@section('content')
<x-site.header/>

{{-- HERO --}}
<section id="top" style="position:relative;overflow:hidden">
  <div style="position:absolute;top:-160px;left:-120px;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(247,190,23,.18),transparent 66%);pointer-events:none"></div>
  <div style="position:absolute;top:120px;right:-160px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle,rgba(43,39,36,.05),transparent 66%);pointer-events:none"></div>

  <div class="g-hero" style="max-width:1180px;margin:0 auto;padding:70px 26px 84px;display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center;position:relative">
    <div>
      <div style="display:inline-flex;align-items:center;gap:9px;background:#fff;border:1px solid #E5EAE6;border-radius:100px;padding:7px 15px;font-size:13.5px;font-weight:800;color:#8A6D12;box-shadow:0 10px 22px -16px rgba(40,35,25,.3)">
        <span style="width:8px;height:8px;border-radius:50%;background:#C9A24B;animation:pulseDot 1.8s ease-in-out infinite"></span>
        منصة التوظيف المحلي الأولى في السعودية
      </div>

      <h1 style="font-size:56px;line-height:1.14;font-weight:900;color:#22302A;margin-top:22px;letter-spacing:-.01em;text-wrap:balance">وظيفتك القريبة<br><span style="position:relative;white-space:nowrap">على بُعد خطوة<span style="position:absolute;right:0;left:0;bottom:6px;height:14px;background:#C9A24B;opacity:.9;z-index:-1;border-radius:4px"></span></span></h1>

      <p style="font-size:19px;line-height:1.7;color:#5C6862;font-weight:500;margin-top:20px;max-width:480px">AMS تربط الباحثين عن عمل بالمنشآت القريبة منهم — وظائف الدوام الجزئي والكامل والعمل بالساعة، بخريطة تفاعلية وتواصل مباشر عبر واتساب.</p>

      <div style="display:flex;gap:13px;margin-top:32px;flex-wrap:wrap">
        <a href="#download" class="btn" style="display:flex;align-items:center;gap:11px;background:#284C3D;color:#fff;padding:13px 22px;border-radius:16px;box-shadow:0 16px 30px -16px rgba(40,35,25,.55)">
          <i class="iconsax" style="font-size:26px;color:#C9A24B" icon-name="mobile"></i>
          <span style="text-align:right;line-height:1.2"><span style="display:block;font-size:11px;font-weight:600;color:#A6B0AA">حمّله على</span><span style="display:block;font-size:16px;font-weight:800">App Store</span></span>
        </a>
        <a href="#download" class="btn" style="display:flex;align-items:center;gap:11px;background:#fff;color:#284C3D;padding:13px 22px;border-radius:16px;border:1px solid #E5EAE6;box-shadow:0 16px 30px -22px rgba(40,35,25,.4)">
          <i class="iconsax" style="font-size:26px;color:#284C3D" icon-name="shop"></i>
          <span style="text-align:right;line-height:1.2"><span style="display:block;font-size:11px;font-weight:600;color:#869089">حمّله على</span><span style="display:block;font-size:16px;font-weight:800">Google Play</span></span>
        </a>
      </div>

      <div style="display:flex;align-items:center;gap:16px;margin-top:30px">
        <div style="display:flex">
          <div style="width:40px;height:40px;border-radius:50%;background:radial-gradient(circle at 30% 26%,#FBD46B,#B88A2E);border:2.5px solid #F4F6F3"></div>
          <div style="width:40px;height:40px;border-radius:50%;background:radial-gradient(circle at 30% 26%,#3A352F,#241F1C);border:2.5px solid #F4F6F3;margin-right:-12px"></div>
          <div style="width:40px;height:40px;border-radius:50%;background:radial-gradient(circle at 30% 26%,#7FB98F,#3F7A55);border:2.5px solid #F4F6F3;margin-right:-12px"></div>
          <div style="width:40px;height:40px;border-radius:50%;background:#284C3D;border:2.5px solid #F4F6F3;margin-right:-12px;display:flex;align-items:center;justify-content:center;color:#C9A24B;font-size:12px;font-weight:800">+8k</div>
        </div>
        <div style="line-height:1.4">
          <div style="display:flex;align-items:center;gap:4px;color:#C9A24B;font-size:15px">
            @for ($i = 0; $i < 5; $i++)<i class="iconsax" icon-name="star"></i>@endfor
            <span style="color:#284C3D;font-weight:800;margin-right:4px">4.9</span>
          </div>
          <div style="font-size:13.5px;color:#7E8B84;font-weight:600">من أكثر من 8,000 مستخدم</div>
        </div>
      </div>
    </div>

    <x-site.phone-mockup :jobs="$previewJobs"/>
  </div>
</section>

{{-- STATS --}}
<section style="max-width:1180px;margin:0 auto;padding:0 26px">
  <div class="g-4" style="background:#284C3D;border-radius:26px;padding:34px 30px;display:grid;grid-template-columns:repeat(4,1fr);gap:20px;box-shadow:0 30px 60px -34px rgba(40,35,25,.6);position:relative;overflow:hidden">
    <div style="position:absolute;top:-60px;left:-40px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(247,190,23,.16),transparent 68%)"></div>
    @foreach ($stats as $index => $stat)
      <div style="position:relative;text-align:center{{ $index > 0 ? ';border-right:1px solid rgba(255,255,255,.1)' : '' }}">
        <div style="font-size:40px;font-weight:900;color:{{ $index === 0 ? '#C9A24B' : '#fff' }}">{!! $stat['value'] !!}</div>
        <div style="font-size:14px;color:#B8C4BC;font-weight:700;margin-top:4px">{{ $stat['label'] }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- CATEGORIES --}}
<section id="cats" style="max-width:1180px;margin:0 auto;padding:84px 26px 20px">
  <x-site.section-heading eyebrow="المجالات" title="تصفّح حسب المجال"
    body="فرص عمل في كل القطاعات التي تحتاجها المدينة — من المطاعم إلى التوصيل والصيانة."/>

  <div class="g-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:40px">
    @foreach ($categories as $category)
      <a href="{{ route('site.jobs.index', ['category' => $category->code]) }}" class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;display:flex;align-items:center;gap:16px;cursor:pointer">
        <div style="width:58px;height:58px;border-radius:17px;background:{{ $category->tintBackground() }};display:flex;align-items:center;justify-content:center;color:{{ $category->tintForeground() }};flex:0 0 auto"><i class="iconsax" style="font-size:28px" icon-name="{{ $category->icon }}"></i></div>
        <div style="flex:1">
          <div style="font-size:17px;font-weight:800;color:#284C3D">{{ $category->name }}</div>
          <div style="font-size:13.5px;color:#869089;font-weight:700;margin-top:3px">{{ $category->jobs_count }} وظيفة</div>
        </div>
        <i class="iconsax" style="font-size:20px;color:#CFCabf" icon-name="chevron-left"></i>
      </a>
    @endforeach
  </div>
</section>

{{-- HOW IT WORKS --}}
<section id="how" style="max-width:1180px;margin:0 auto;padding:84px 26px 20px">
  <x-site.section-heading eyebrow="كيف يعمل" title="ثلاث خطوات لوظيفتك"
    body="من التسجيل حتى التواصل مع صاحب العمل — كل شيء في دقائق."/>

  <div class="g-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:44px;position:relative">
    @foreach ($steps as $step)
      <div style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:28px 24px;position:relative">
        <div style="position:absolute;top:22px;left:24px;font-size:52px;font-weight:900;color:#EDF1EC;line-height:1">{{ $step['n'] }}</div>
        <div style="width:56px;height:56px;border-radius:17px;background:#284C3D;display:flex;align-items:center;justify-content:center;color:#C9A24B;position:relative"><i class="iconsax" style="font-size:27px" icon-name="{{ $step['icon'] }}"></i></div>
        <div style="font-size:20px;font-weight:800;color:#284C3D;margin-top:20px">{{ $step['title'] }}</div>
        <div style="font-size:15px;color:#5C6862;font-weight:500;line-height:1.65;margin-top:8px">{{ $step['body'] }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- FEATURES --}}
<section id="features" style="max-width:1180px;margin:0 auto;padding:84px 26px 20px">
  <x-site.section-heading eyebrow="المميزات" title="لماذا AMS؟"/>

  <div class="g-map" style="background:#fff;border:1px solid #E5EAE6;border-radius:26px;padding:12px;margin-top:40px;display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:stretch;overflow:hidden">
    <div style="padding:32px 30px;display:flex;flex-direction:column;justify-content:center">
      <div style="display:inline-flex;align-items:center;gap:8px;background:#E2EEF4;color:#2E6E8A;font-size:13px;font-weight:800;padding:6px 13px;border-radius:100px;width:max-content"><i class="iconsax" style="font-size:16px" icon-name="gps"></i>الخريطة</div>
      <h3 style="font-size:28px;font-weight:900;color:#22302A;margin-top:16px;line-height:1.3">وظائف على بُعد خطوات منك</h3>
      <p style="font-size:16px;color:#5C6862;font-weight:500;line-height:1.7;margin-top:12px">خريطة تفاعلية تعرض الفرص القريبة أولًا، مع فلترة بالمسافة والمجال ونوع الدوام. لا مزيد من التنقّل الطويل.</p>
      <div style="display:flex;gap:22px;margin-top:22px">
        <div><div style="font-size:24px;font-weight:900;color:#284C3D">5 كم</div><div style="font-size:13px;color:#869089;font-weight:700">نطاق البحث</div></div>
        <div><div style="font-size:24px;font-weight:900;color:#284C3D">لحظي</div><div style="font-size:13px;color:#869089;font-weight:700">تحديث الفرص</div></div>
      </div>
    </div>
    <div style="position:relative;border-radius:18px;background:linear-gradient(150deg,#EDEBE4,#E2DFD6);min-height:320px;overflow:hidden">
      <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(43,39,36,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(43,39,36,.05) 1px,transparent 1px);background-size:38px 38px"></div>
      <div style="position:absolute;top:36%;left:44%;width:26px;height:26px"><span style="position:absolute;inset:0;border-radius:50%;background:rgba(247,190,23,.4);animation:ring 2.4s ease-out infinite"></span><span style="position:absolute;inset:0;width:26px;height:26px;border-radius:50%;background:#C9A24B;border:3px solid #fff;box-shadow:0 6px 14px -4px rgba(40,35,25,.5)"></span></div>
      <div style="position:absolute;top:22%;right:26%;width:40px;height:40px;border-radius:13px 13px 13px 4px;background:#284C3D;display:flex;align-items:center;justify-content:center;color:#C9A24B;box-shadow:0 12px 22px -10px rgba(40,35,25,.5)"><i class="iconsax" style="font-size:20px" icon-name="coffee"></i></div>
      <div style="position:absolute;bottom:26%;left:22%;width:40px;height:40px;border-radius:13px 13px 13px 4px;background:#fff;display:flex;align-items:center;justify-content:center;color:#B2453A;box-shadow:0 12px 22px -10px rgba(40,35,25,.4)"><i class="iconsax" style="font-size:20px" icon-name="truck"></i></div>
      <div style="position:absolute;bottom:34%;right:30%;width:40px;height:40px;border-radius:13px 13px 13px 4px;background:#fff;display:flex;align-items:center;justify-content:center;color:#4F7A2E;box-shadow:0 12px 22px -10px rgba(40,35,25,.4)"><i class="iconsax" style="font-size:20px" icon-name="shop"></i></div>
    </div>
  </div>

  <div class="g-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:16px">
    @foreach ($features as $feature)
      <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:26px">
        <div style="width:54px;height:54px;border-radius:16px;background:{{ $feature['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $feature['fg'] }}"><i class="iconsax" style="font-size:26px" icon-name="{{ $feature['icon'] }}"></i></div>
        <div style="font-size:19px;font-weight:800;color:#284C3D;margin-top:18px">{{ $feature['title'] }}</div>
        <div style="font-size:15px;color:#5C6862;font-weight:500;line-height:1.65;margin-top:8px">{{ $feature['body'] }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- EMPLOYERS --}}
<section id="employers" style="padding:84px 0 20px">
  <div style="max-width:1180px;margin:0 auto;padding:0 26px">
    <div style="background:linear-gradient(155deg,#315C4A,#223F31);border-radius:30px;padding:52px 48px;position:relative;overflow:hidden">
      <div style="position:absolute;top:-80px;right:-60px;width:340px;height:340px;border-radius:50%;background:radial-gradient(circle,rgba(247,190,23,.14),transparent 66%)"></div>
      <div class="g-2" style="position:relative;display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center">
        <div>
          <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(247,190,23,.14);color:#C9A24B;font-size:13.5px;font-weight:800;padding:7px 14px;border-radius:100px"><i class="iconsax" style="font-size:17px" icon-name="briefcase"></i>لأصحاب العمل</div>
          <h2 style="font-size:36px;font-weight:900;color:#fff;margin-top:18px;line-height:1.3">وظّف بسرعة، وأدر طلباتك من مكان واحد</h2>
          <p style="font-size:17px;color:#B8C4BC;font-weight:500;line-height:1.7;margin-top:14px">انشر إعلان وظيفة في دقائق، استقبل الطلبات من الباحثين القريبين، وتواصل معهم مباشرة — عبر التطبيق أو لوحة تحكم الويب.</p>
          <div style="display:flex;gap:12px;margin-top:26px;flex-wrap:wrap">
            <a href="{{ route('employer.dashboard') }}" class="btn" style="background:#C9A24B;color:#284C3D;font-size:15px;font-weight:800;padding:14px 24px;border-radius:15px;box-shadow:0 16px 30px -14px rgba(247,190,23,.5)">افتح لوحة أصحاب العمل</a>
            <a href="#download" class="btn" style="background:rgba(255,255,255,.08);color:#fff;font-size:15px;font-weight:800;padding:14px 24px;border-radius:15px;border:1px solid rgba(255,255,255,.14)">حمّل التطبيق</a>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
          @foreach ($employerPoints as $point)
            <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:17px;padding:18px 20px;display:flex;align-items:center;gap:15px">
              <div style="width:48px;height:48px;border-radius:14px;background:rgba(247,190,23,.16);display:flex;align-items:center;justify-content:center;color:#C9A24B;flex:0 0 auto"><i class="iconsax" style="font-size:23px" icon-name="{{ $point['icon'] }}"></i></div>
              <div><div style="font-size:16.5px;font-weight:800;color:#fff">{{ $point['title'] }}</div><div style="font-size:13.5px;color:#A6B0AA;font-weight:600;margin-top:2px">{{ $point['body'] }}</div></div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- LIVE JOBS --}}
<section id="jobs" style="max-width:1180px;margin:0 auto;padding:84px 26px 20px">
  <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap">
    <div>
      <div style="font-size:14px;font-weight:800;color:#8A6D12;letter-spacing:.04em">وظائف حديثة</div>
      <h2 style="font-size:38px;font-weight:900;color:#22302A;margin-top:10px;letter-spacing:-.01em">فرص أُضيفت للتو</h2>
    </div>
    <a href="{{ route('site.jobs.index') }}" class="btn" style="background:#fff;border:1px solid #E5EAE6;color:#284C3D;font-size:15px;font-weight:800;padding:12px 20px;border-radius:14px;display:flex;align-items:center;gap:8px">تصفّح كل الوظائف<i class="iconsax" style="font-size:18px" icon-name="arrow-left"></i></a>
  </div>

  <div class="g-2" style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-top:34px">
    @forelse ($latestJobs as $job)
      <x-site.job-card :job="$job"/>
    @empty
      <div style="grid-column:1/-1;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:40px;text-align:center;color:#7E8B84;font-weight:600">لا توجد وظائف منشورة بعد.</div>
    @endforelse
  </div>
</section>

{{-- DOWNLOAD --}}
<section id="download" style="max-width:1180px;margin:0 auto;padding:84px 26px 20px">
  <div class="g-2" style="background:#C9A24B;border-radius:30px;padding:56px 48px;position:relative;overflow:hidden;display:grid;grid-template-columns:1.1fr .9fr;gap:30px;align-items:center">
    <div style="position:absolute;bottom:-90px;left:-50px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,.16)"></div>
    <div style="position:relative">
      <h2 style="font-size:40px;font-weight:900;color:#284C3D;line-height:1.25;letter-spacing:-.01em">حمّل AMS وابدأ<br>رحلتك المهنية اليوم</h2>
      <p style="font-size:17px;color:#5A4A16;font-weight:600;line-height:1.6;margin-top:14px">متاح على iOS و Android — مجانًا بالكامل للباحثين عن عمل.</p>
      <div style="display:flex;gap:13px;margin-top:28px;flex-wrap:wrap">
        @foreach ([['mobile', 'App Store'], ['play', 'Google Play']] as [$icon, $store])
          <a href="#" class="btn" style="display:flex;align-items:center;gap:11px;background:#284C3D;color:#fff;padding:14px 24px;border-radius:16px;box-shadow:0 18px 32px -16px rgba(40,35,25,.5)">
            <i class="iconsax" style="font-size:27px;color:#C9A24B" icon-name="{{ $icon }}"></i>
            <span style="text-align:right;line-height:1.2"><span style="display:block;font-size:11px;font-weight:600;color:#A6B0AA">حمّله على</span><span style="display:block;font-size:16px;font-weight:800">{{ $store }}</span></span>
          </a>
        @endforeach
      </div>
    </div>
    <div style="position:relative;display:flex;justify-content:center;align-items:center">
      <div style="width:180px;height:180px;border-radius:44px;background:#284C3D;display:flex;align-items:center;justify-content:center;box-shadow:0 30px 56px -22px rgba(40,35,25,.55);position:relative">
        <div style="position:absolute;inset:0;border-radius:44px;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:20px 20px"></div>
        <div style="position:relative;text-align:center;color:#C9A24B"><i class="iconsax" style="font-size:64px" icon-name="mobile"></i><div style="font-size:13px;font-weight:800;color:#A6B0AA;margin-top:8px">امسح للتحميل</div></div>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section id="faq" style="max-width:820px;margin:0 auto;padding:84px 26px 20px">
  <div style="text-align:center">
    <div style="font-size:14px;font-weight:800;color:#8A6D12;letter-spacing:.04em">الأسئلة الشائعة</div>
    <h2 style="font-size:38px;font-weight:900;color:#22302A;margin-top:10px;letter-spacing:-.01em">أسئلة قد تهمّك</h2>
  </div>

  <div style="display:flex;flex-direction:column;gap:12px;margin-top:36px">
    @foreach ($faqs as $index => $faq)
      <details style="background:#fff;border:1px solid #E5EAE6;border-radius:18px;overflow:hidden" @if($index === 0) open @endif>
        <summary style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:20px 24px;cursor:pointer;list-style:none">
          <span style="font-size:17px;font-weight:800;color:#284C3D">{{ $faq['question'] }}</span>
          <span style="width:30px;height:30px;border-radius:10px;background:#F7F5EF;display:flex;align-items:center;justify-content:center;color:#8A6D12;font-size:20px;font-weight:800;flex:0 0 auto">+</span>
        </summary>
        <div style="padding:0 24px 22px;font-size:15.5px;color:#5C6862;font-weight:500;line-height:1.7">{{ $faq['answer'] }}</div>
      </details>
    @endforeach
  </div>
</section>

<x-site.footer/>
@endsection

@push('head')
<style>
  details summary::-webkit-details-marker { display: none; }
  details[open] summary span:last-child { transform: rotate(45deg); }
  details summary span:last-child { transition: transform .2s ease; }
</style>
@endpush
