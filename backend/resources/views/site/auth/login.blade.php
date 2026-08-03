@extends('site.layouts.app')

@section('title', 'تسجيل الدخول')

@section('content')
<x-site.header/>

<section style="max-width:520px;margin:0 auto;padding:48px 24px 60px">
  <div style="background:#fff;border:1px solid #E5EAE6;border-radius:26px;padding:34px">
    <h1 style="font-size:28px;font-weight:900;color:#22302A">تسجيل الدخول</h1>
    <p style="font-size:15px;color:#5C6862;font-weight:500;margin-top:8px;line-height:1.7">أدخل رقم جوالك وسنرسل لك رمز تحقّق — بدون كلمة مرور.</p>

    @error('form')
      <div style="margin-top:18px;background:#FBE6E6;color:#B23232;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('login.code') }}" style="margin-top:24px;display:flex;flex-direction:column;gap:18px">
      @csrf

      <div>
        <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">أدخل بصفتك</div>
        <div style="display:flex;gap:10px">
          @foreach (['candidate' => 'باحث عن عمل', 'employer' => 'صاحب عمل'] as $value => $label)
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="role" value="{{ $value }}" @checked(old('role', $role->value) === $value) style="position:absolute;opacity:0;pointer-events:none" class="role-radio">
              <span style="display:block;text-align:center;font-size:14px;font-weight:800;padding:13px;border-radius:14px;border:1.5px solid #DCE3DD;background:#F7F9F7;color:#5C6862">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <div>
        <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">رقم الجوال</div>
        <div style="display:flex;align-items:center;height:52px;border:1px solid {{ $errors->has('phone') || $errors->has('country') ? '#B23232' : '#DCE3DD' }};border-radius:14px;background:#F7F9F7;overflow:hidden">
          @php($selectedIso = old('country', 'SA'))
          @php($selectedCountry = $countries->firstWhere('iso2', $selectedIso) ?? $countries->first())
          <input id="phone-input" name="phone" value="{{ old('phone') }}" inputmode="numeric" placeholder="{{ $selectedCountry?->placeholder ?? '5X XXX XXXX' }}" style="flex:1;height:100%;border:none;background:transparent;padding:0 14px;font-family:inherit;font-size:15px;font-weight:700;color:#284C3D;outline:none;direction:ltr;text-align:left">
          <select id="country-select" name="country" style="height:100%;border:none;border-right:1px solid #E5EAE6;background:#EDF1EC;padding:0 12px;font-family:inherit;font-size:14px;font-weight:800;color:#284C3D;outline:none;cursor:pointer;direction:ltr">
            @foreach ($countries as $country)
              <option value="{{ $country->iso2 }}" data-placeholder="{{ $country->placeholder }}" @selected($selectedIso === $country->iso2)>{{ $country->flag }} {{ $country->dial_code }}</option>
            @endforeach
          </select>
        </div>
        @error('phone')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
        @error('country')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
      </div>

      <button type="submit" class="btn" style="display:flex;align-items:center;justify-content:center;gap:9px;background:#284C3D;color:#fff;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:15px;border-radius:15px;cursor:pointer">
        <i class="iconsax" style="font-size:20px;color:#C9A24B" icon-name="send-2"></i>إرسال رمز التحقق
      </button>
    </form>
  </div>
</section>

<style>
  /* Inline styles on the span win over a plain rule, so the selected state is forced. */
  .role-radio:checked + span { border-color:#C9A24B !important; background:#F3ECD6 !important; color:#8A6D12 !important; }
</style>

<script>
  (function () {
    var select = document.getElementById('country-select');
    var input = document.getElementById('phone-input');
    if (!select || !input) return;
    select.addEventListener('change', function () {
      var opt = select.options[select.selectedIndex];
      input.placeholder = opt.getAttribute('data-placeholder') || '';
      input.value = '';
    });
  })();
</script>
@endsection
