@extends('site.layouts.app')

@section('title', 'تواصل معنا')

@section('content')
<x-site.header/>

<section style="max-width:1180px;margin:0 auto;padding:56px 26px 20px;text-align:center">
  <h1 style="font-size:44px;font-weight:900;color:#22302A;letter-spacing:-.01em">تواصل معنا</h1>
  <p style="font-size:17px;color:#5C6862;font-weight:500;margin-top:12px">لديك سؤال، اقتراح، أو تحتاج مساعدة؟ فريقنا جاهز للرد عليك.</p>
</section>

<div class="g-contact" style="max-width:1180px;margin:0 auto;padding:24px 26px 20px;display:grid;grid-template-columns:1fr 380px;gap:22px;align-items:start">

  <div style="background:#fff;border:1px solid #E5EAE6;border-radius:24px;padding:32px">
    @if (session('contact_sent'))
      <div style="text-align:center;padding:26px 0">
        <div style="width:64px;height:64px;border-radius:20px;background:#E7F4EC;color:#1F8A4D;display:flex;align-items:center;justify-content:center;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="tick-circle"></i></div>
        <div style="font-size:21px;font-weight:900;color:#22302A;margin-top:18px">تم إرسال رسالتك</div>
        <div style="font-size:15px;color:#5C6862;font-weight:500;margin-top:8px">شكرًا لتواصلك. سيرد عليك فريقنا خلال 24 ساعة عمل.</div>
        <a href="{{ route('site.contact') }}" class="btn" style="display:inline-block;margin-top:20px;background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:11px 22px;border-radius:13px">إرسال رسالة أخرى</a>
      </div>
    @else
      <div style="font-size:21px;font-weight:900;color:#22302A">أرسل لنا رسالة</div>

      <form method="POST" action="{{ route('site.contact.send') }}" style="margin-top:22px;display:flex;flex-direction:column;gap:16px">
        @csrf

        <x-site.field name="name" label="الاسم" placeholder="اسمك الكامل"/>
        <x-site.field name="email" label="البريد الإلكتروني" type="email" placeholder="you@email.com" dir="ltr"/>

        <div>
          <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">نوع الطلب</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            @foreach ($topics as $value => $label)
              <label style="cursor:pointer">
                <input type="radio" name="topic" value="{{ $value }}" @checked(old('topic', 'support') === $value) style="position:absolute;opacity:0;pointer-events:none" class="topic-radio">
                <span style="display:inline-block;font-size:13.5px;font-weight:700;padding:9px 16px;border-radius:12px;border:1px solid #DCE3DD;background:#F7F9F7;color:#5C6862">{{ $label }}</span>
              </label>
            @endforeach
          </div>
          @error('topic')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
        </div>

        <x-site.field name="message" label="رسالتك" type="textarea" placeholder="اكتب تفاصيل استفسارك هنا…"/>

        <button type="submit" class="btn" style="display:flex;align-items:center;justify-content:center;gap:9px;background:#284C3D;color:#fff;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:15px;border-radius:15px;cursor:pointer">
          <i class="iconsax" style="font-size:20px;color:#C9A24B" icon-name="send-2"></i>إرسال الرسالة
        </button>
      </form>
    @endif
  </div>

  <div style="display:flex;flex-direction:column;gap:14px">
    @foreach ($channels as $channel)
      @php($tag = $channel['href'] ? 'a' : 'div')
      <{{ $tag }} @if($channel['href']) href="{{ $channel['href'] }}" @endif class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:20px;display:flex;align-items:center;gap:14px">
        <div style="width:48px;height:48px;border-radius:14px;background:{{ $channel['bg'] }};color:{{ $channel['fg'] }};display:flex;align-items:center;justify-content:center;flex:0 0 auto"><i class="iconsax" style="font-size:23px" icon-name="{{ $channel['icon'] }}"></i></div>
        <div>
          <div style="font-size:13.5px;color:#7E8B84;font-weight:700">{{ $channel['label'] }}</div>
          <div style="font-size:15.5px;font-weight:800;color:#284C3D;margin-top:2px;direction:ltr;text-align:right">{{ $channel['value'] }}</div>
        </div>
      </{{ $tag }}>
    @endforeach

    <div style="background:linear-gradient(155deg,#315C4A,#223F31);border-radius:20px;padding:24px;color:#fff">
      <div style="font-size:16px;font-weight:800">ساعات العمل</div>
      @foreach ($workingHours as $slot)
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px">
          <span style="font-size:14px;color:#B8C4BC;font-weight:600">{{ $slot['days'] }}</span>
          <span style="font-size:14px;font-weight:800;color:{{ $slot['closed'] ? '#C9A24B' : '#fff' }}">{{ $slot['hours'] }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>

<x-site.footer/>
@endsection

@push('head')
<style>
  @media (max-width: 900px) { .g-contact { grid-template-columns: 1fr !important } }
  .topic-radio:checked + span { background: #284C3D !important; border-color: #284C3D !important; color: #fff !important }
</style>
@endpush
