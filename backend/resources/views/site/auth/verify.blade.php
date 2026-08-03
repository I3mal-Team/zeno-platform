@extends('site.layouts.app')

@section('title', 'رمز التحقق')

@section('content')
<x-site.header/>

<section style="max-width:520px;margin:0 auto;padding:48px 24px 60px">
  <div style="background:#fff;border:1px solid #E5EAE6;border-radius:26px;padding:34px">
    <h1 style="font-size:28px;font-weight:900;color:#22302A">أدخل رمز التحقق</h1>
    <p style="font-size:15px;color:#5C6862;font-weight:500;margin-top:8px;line-height:1.7">
      أرسلنا رمزاً مكوّناً من {{ config('integrations.otp.length') }} أرقام إلى
      <span style="font-weight:800;color:#284C3D;direction:ltr;display:inline-block">{{ $phone }}</span>.
    </p>

    @if (app()->environment('local') && config('integrations.otp.generator') === 'fixed')
      <div style="margin-top:16px;background:#F3ECD6;color:#8A6D12;border-radius:14px;padding:12px 16px;font-size:13.5px;font-weight:700">وضع التطوير: الرمز الثابت هو {{ config('integrations.otp.fixed_code') }}</div>
    @endif

    @error('form')
      <div style="margin-top:18px;background:#FBE6E6;color:#B23232;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('login.verify.submit') }}" style="margin-top:24px;display:flex;flex-direction:column;gap:18px">
      @csrf

      <div>
        <input name="code" inputmode="numeric" autofocus maxlength="{{ config('integrations.otp.length') }}" placeholder="••••" style="width:100%;height:64px;border:1px solid {{ $errors->has('code') ? '#B23232' : '#DCE3DD' }};border-radius:16px;background:#F7F9F7;font-family:inherit;font-size:30px;font-weight:800;color:#284C3D;text-align:center;letter-spacing:.5em;outline:none;direction:ltr">
        @error('code')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn" style="display:flex;align-items:center;justify-content:center;gap:9px;background:#284C3D;color:#fff;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:15px;border-radius:15px;cursor:pointer">
        <i class="iconsax" style="font-size:20px;color:#C9A24B" icon-name="tick-circle"></i>تأكيد ومتابعة
      </button>
    </form>

    <div style="margin-top:16px;text-align:center">
      <a href="{{ route('login') }}" style="font-size:14px;font-weight:800;color:#7E8B84">تغيير الرقم</a>
    </div>
  </div>
</section>
@endsection
