@extends('site.layouts.app')

@section('title', 'الأسعار')
@section('description', 'باقات أصحاب العمل — ابدأ مجانًا وارتقِ عندما تحتاج.')

@section('content')
<x-site.header/>

<section style="max-width:1180px;margin:0 auto;padding:64px 26px 30px;text-align:center">
  <div style="display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #ECEAE3;border-radius:100px;padding:7px 15px;font-size:13.5px;font-weight:800;color:#8A6D12">باقات أصحاب العمل</div>
  <h1 style="font-size:46px;font-weight:900;color:#211F20;margin-top:18px;line-height:1.2;letter-spacing:-.01em">أسعار بسيطة<br>تنمو مع منشأتك</h1>
  <p style="font-size:17px;color:#6C665C;font-weight:500;line-height:1.7;margin:16px auto 0;max-width:560px">ابدأ مجانًا، وارتقِ عندما تحتاج مزيدًا من الإعلانات والوصول. بلا عقود ولا رسوم خفية.</p>

  <div style="display:inline-flex;align-items:center;gap:4px;background:#F1EEE7;border-radius:100px;padding:5px;margin-top:28px">
    <a href="{{ route('site.pricing') }}" style="padding:9px 22px;border-radius:100px;font-size:14.5px;font-weight:800;background:{{ $yearly ? 'transparent' : '#fff' }};color:{{ $yearly ? '#8A857A' : '#2B2724' }}">شهري</a>
    <a href="{{ route('site.pricing', ['cycle' => 'year']) }}" style="display:flex;align-items:center;gap:7px;padding:9px 22px;border-radius:100px;font-size:14.5px;font-weight:800;background:{{ $yearly ? '#fff' : 'transparent' }};color:{{ $yearly ? '#2B2724' : '#8A857A' }}">
      سنوي<span style="font-size:11.5px;font-weight:800;color:#1F8A4D;background:#E7F4EC;padding:2px 8px;border-radius:100px">وفّر 20%</span>
    </a>
  </div>
</section>

<section style="max-width:1180px;margin:0 auto;padding:0 26px">
  <div class="g-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;align-items:start">
    @foreach ($plans as $plan)
      <div style="position:relative;background:{{ $plan['cardBg'] }};border:1px solid {{ $plan['border'] }};border-radius:26px;padding:30px 26px">
        @if ($plan['popular'])
          <span style="position:absolute;top:-13px;right:50%;transform:translateX(50%);background:#F7BE17;color:#2B2724;font-size:12.5px;font-weight:800;padding:5px 14px;border-radius:100px;white-space:nowrap">الأكثر شيوعًا</span>
        @endif

        <div style="width:52px;height:52px;border-radius:16px;background:{{ $plan['iconBg'] }};display:flex;align-items:center;justify-content:center;color:{{ $plan['iconFg'] }}"><i class="iconsax" style="font-size:25px" icon-name="{{ $plan['icon'] }}"></i></div>

        <div style="font-size:22px;font-weight:900;color:{{ $plan['titleColor'] }};margin-top:18px">{{ $plan['name'] }}</div>
        <div style="font-size:14px;color:{{ $plan['subColor'] }};font-weight:600;line-height:1.6;margin-top:6px;min-height:44px">{{ $plan['tagline'] }}</div>

        <div style="display:flex;align-items:flex-end;gap:8px;margin-top:18px">
          <span style="font-size:40px;font-weight:900;color:{{ $plan['titleColor'] }};line-height:1">{{ $plan['price'] }}</span>
          @if ($plan['unit'])<span style="font-size:13.5px;color:{{ $plan['subColor'] }};font-weight:700;padding-bottom:5px">{{ $plan['unit'] }}</span>@endif
        </div>

        <a href="{{ route('employer.dashboard') }}" class="btn" style="display:block;text-align:center;margin-top:22px;background:{{ $plan['buttonBg'] }};color:{{ $plan['buttonFg'] }};border:1px solid {{ $plan['buttonBorder'] }};font-size:15px;font-weight:800;padding:13px;border-radius:14px">{{ $plan['cta'] }}</a>

        <div style="height:1px;background:{{ $plan['divider'] }};margin:24px 0"></div>

        <div style="display:flex;flex-direction:column;gap:13px">
          @foreach ($plan['features'] as $feature)
            <div style="display:flex;align-items:center;gap:11px">
              <span style="width:24px;height:24px;border-radius:8px;background:{{ $feature['on'] ? '#E7F4EC' : '#F1EEE7' }};color:{{ $feature['on'] ? '#1F8A4D' : '#B0AA9E' }};display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:14px" icon-name="{{ $feature['on'] ? 'tick-circle' : 'x' }}"></i></span>
              <span style="font-size:14.5px;font-weight:600;color:{{ $feature['on'] ? ($plan['popular'] ? '#EDE9E0' : '#3F3A33') : '#A8A296' }}">{{ $feature['label'] }}</span>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>

  <p style="text-align:center;font-size:13.5px;color:#8A857A;font-weight:600;margin-top:26px">جميع الأسعار بالريال السعودي · شاملة ضريبة القيمة المضافة · يمكنك الإلغاء في أي وقت</p>
</section>

<section style="max-width:980px;margin:0 auto;padding:64px 26px 20px">
  <x-site.section-heading eyebrow="مقارنة" title="قارن بين الباقات"/>

  <div style="background:#fff;border:1px solid #EFEDE6;border-radius:22px;margin-top:36px;overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;min-width:560px">
      <thead>
        <tr style="background:#FBFAF7">
          @foreach (['الميزة', 'مجاني', 'أعمال', 'مؤسسات'] as $index => $header)
            <th style="padding:16px 20px;text-align:right;font-size:14.5px;font-weight:800;color:#2B2724{{ $index === 2 ? ';background:#FBF6E7' : '' }}">{{ $header }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach ($comparison as $row)
          <tr style="border-top:1px solid #F2EFE8">
            <td style="padding:15px 20px;font-size:14.5px;font-weight:700;color:#3F3A33">{{ $row['label'] }}</td>
            <td style="padding:15px 20px;font-size:14.5px;font-weight:700;color:#6C665C">{{ $row['free'] }}</td>
            <td style="padding:15px 20px;font-size:14.5px;font-weight:800;color:#2B2724;background:#FBF6E7">{{ $row['business'] }}</td>
            <td style="padding:15px 20px;font-size:14.5px;font-weight:700;color:#6C665C">{{ $row['enterprise'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>

<section style="max-width:820px;margin:0 auto;padding:64px 26px 20px">
  <x-site.section-heading eyebrow="الأسئلة" title="أسئلة عن الأسعار"/>
  <x-site.faq-list :faqs="$faqs"/>
</section>

<section style="max-width:1180px;margin:0 auto;padding:64px 26px 20px">
  <div style="background:linear-gradient(155deg,#34302B,#26221F);border-radius:30px;padding:52px 44px;text-align:center">
    <h2 style="font-size:34px;font-weight:900;color:#fff;letter-spacing:-.01em">ابدأ التوظيف اليوم — مجانًا</h2>
    <p style="font-size:17px;color:#C7C2B8;font-weight:500;margin-top:12px">أنشئ حساب منشأتك وانشر أول وظيفة خلال دقائق.</p>
    <div style="display:flex;gap:12px;justify-content:center;margin-top:26px;flex-wrap:wrap">
      <a href="{{ route('employer.dashboard') }}" class="btn" style="background:#F7BE17;color:#2B2724;font-size:15px;font-weight:800;padding:14px 26px;border-radius:15px">افتح لوحة التحكم</a>
      <a href="{{ route('site.contact') }}" class="btn" style="background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.14);font-size:15px;font-weight:800;padding:14px 26px;border-radius:15px">تواصل مع المبيعات</a>
    </div>
  </div>
</section>

<x-site.footer/>
@endsection
