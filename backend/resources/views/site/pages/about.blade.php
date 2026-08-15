@extends('site.layouts.app')

@section('title', 'من نحن')
@section('description', 'وُلدت Zeno من فكرة بسيطة: أفضل وظيفة لك قد تكون على بُعد شارع واحد.')

@section('content')
<x-site.header/>

<section style="max-width:1180px;margin:0 auto;padding:64px 26px 40px;text-align:center">
  <div style="display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #E5EAE6;border-radius:100px;padding:7px 15px;font-size:13.5px;font-weight:800;color:#8A6D12">قصتنا</div>
  <h1 style="font-size:46px;font-weight:900;color:#22302A;margin-top:18px;line-height:1.2;letter-spacing:-.01em">نقرّب المسافة بين<br>الناس والفرص</h1>
  <p style="font-size:18px;color:#5C6862;font-weight:500;line-height:1.75;margin:18px auto 0;max-width:640px">وُلدت Zeno من فكرة بسيطة: أفضل وظيفة لك قد تكون على بُعد شارع واحد. نبني منصة توظيف محلية تجعل إيجاد عمل قريب أمرًا سهلًا وسريعًا وإنسانيًا.</p>
</section>

<section style="max-width:1180px;margin:0 auto;padding:0 26px">
  <div class="g-4" style="background:#284C3D;border-radius:26px;padding:34px 30px;display:grid;grid-template-columns:repeat(4,1fr);gap:20px;box-shadow:0 30px 60px -34px rgba(40,35,25,.6)">
    @foreach ($stats as $index => $stat)
      <div style="text-align:center{{ $index > 0 ? ';border-right:1px solid rgba(255,255,255,.1)' : '' }}">
        <div style="font-size:40px;font-weight:900;color:{{ $stat['accent'] ? '#C9A24B' : '#fff' }}">{{ $stat['value'] }}</div>
        <div style="font-size:14px;color:#B8C4BC;font-weight:700;margin-top:4px">{{ $stat['label'] }}</div>
      </div>
    @endforeach
  </div>
</section>

<section class="g-2" style="max-width:1180px;margin:0 auto;padding:64px 26px 20px;display:grid;grid-template-columns:1fr 1fr;gap:18px">
  @foreach ([['رسالتنا', 'تمكين كل باحث عن عمل من الوصول لفرصة قريبة تناسبه، وتمكين كل منشأة من إيجاد الكفاءة المناسبة بسرعة — عبر تقنية بسيطة تحترم وقت الجميع.', 'flash-1'], ['رؤيتنا', 'أن تصبح Zeno الوجهة الأولى للتوظيف المحلي في المنطقة، حيث تُقاس المسافة بين الموهبة والفرصة بالدقائق لا بالأشهر.', 'magic-star']] as [$title, $body, $icon])
    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:24px;padding:32px">
      <div style="width:54px;height:54px;border-radius:16px;background:#284C3D;display:flex;align-items:center;justify-content:center;color:#C9A24B"><i class="iconsax" style="font-size:26px" icon-name="{{ $icon }}"></i></div>
      <div style="font-size:22px;font-weight:900;color:#22302A;margin-top:18px">{{ $title }}</div>
      <p style="font-size:16px;color:#5C6862;font-weight:500;line-height:1.75;margin-top:10px">{{ $body }}</p>
    </div>
  @endforeach
</section>

<section style="max-width:1180px;margin:0 auto;padding:64px 26px 20px">
  <x-site.section-heading eyebrow="قيمنا" title="ما نؤمن به"/>
  <div class="g-3" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:40px">
    @foreach ($values as $value)
      <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:26px">
        <div style="width:54px;height:54px;border-radius:16px;background:{{ $value['bg'] }};display:flex;align-items:center;justify-content:center;color:{{ $value['fg'] }}"><i class="iconsax" style="font-size:26px" icon-name="{{ $value['icon'] }}"></i></div>
        <div style="font-size:19px;font-weight:800;color:#284C3D;margin-top:18px">{{ $value['title'] }}</div>
        <div style="font-size:15px;color:#5C6862;font-weight:500;line-height:1.65;margin-top:8px">{{ $value['body'] }}</div>
      </div>
    @endforeach
  </div>
</section>

<section style="max-width:820px;margin:0 auto;padding:64px 26px 20px">
  <x-site.section-heading eyebrow="رحلتنا" title="محطات Zeno"/>
  <div style="margin-top:40px;position:relative;padding-right:26px;border-right:2px solid #E5EAE6">
    @foreach ($milestones as $milestone)
      <div style="position:relative;padding-bottom:30px">
        <span style="position:absolute;right:-34px;top:4px;width:14px;height:14px;border-radius:50%;background:{{ $milestone['dot'] }};border:3px solid {{ $milestone['ring'] }}"></span>
        <div style="font-size:13.5px;font-weight:800;color:#8A6D12">{{ $milestone['year'] }}</div>
        <div style="font-size:19px;font-weight:800;color:#284C3D;margin-top:4px">{{ $milestone['title'] }}</div>
        <div style="font-size:15px;color:#5C6862;font-weight:500;line-height:1.7;margin-top:6px">{{ $milestone['body'] }}</div>
      </div>
    @endforeach
  </div>
</section>

<section style="max-width:1180px;margin:0 auto;padding:44px 26px 20px">
  <x-site.section-heading eyebrow="الفريق" title="من يقف خلف Zeno"/>
  <div class="g-4" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:40px">
    @foreach ($team as $member)
      <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:26px;text-align:center">
        <div style="width:72px;height:72px;border-radius:50%;background:{{ $member['bg'] }};color:{{ $member['fg'] }};display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;margin:0 auto">{{ $member['initial'] }}</div>
        <div style="font-size:17px;font-weight:800;color:#284C3D;margin-top:16px">{{ $member['name'] }}</div>
        <div style="font-size:13.5px;color:#7E8B84;font-weight:700;margin-top:3px">{{ $member['role'] }}</div>
      </div>
    @endforeach
  </div>
</section>

<section style="max-width:1180px;margin:0 auto;padding:64px 26px 20px">
  <div style="background:#C9A24B;border-radius:30px;padding:52px 44px;text-align:center">
    <h2 style="font-size:36px;font-weight:900;color:#284C3D;letter-spacing:-.01em">انضم إلى رحلتنا</h2>
    <p style="font-size:17px;color:#5A4A16;font-weight:600;margin-top:12px">سواء كنت تبحث عن عمل أو عن موظفين — Zeno معك.</p>
    <div style="display:flex;gap:12px;justify-content:center;margin-top:26px;flex-wrap:wrap">
      <a href="{{ route('site.jobs.index') }}" class="btn" style="background:#284C3D;color:#fff;font-size:15px;font-weight:800;padding:14px 26px;border-radius:15px">تصفّح الوظائف</a>
      <a href="{{ route('site.contact') }}" class="btn" style="background:#fff;color:#284C3D;font-size:15px;font-weight:800;padding:14px 26px;border-radius:15px">تواصل معنا</a>
    </div>
  </div>
</section>

<x-site.footer/>
@endsection
