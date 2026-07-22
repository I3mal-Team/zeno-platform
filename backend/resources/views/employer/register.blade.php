@extends('site.layouts.app')

@section('title', 'بيانات المنشأة')

@section('content')
<x-site.header/>

<section style="max-width:640px;margin:0 auto;padding:48px 24px 60px">
  <div style="background:#fff;border:1px solid #EFEDE6;border-radius:26px;padding:34px">
    <h1 style="font-size:28px;font-weight:900;color:#211F20">بيانات المنشأة</h1>
    <p style="font-size:15px;color:#6C665C;font-weight:500;margin-top:8px;line-height:1.7">عرّفنا بمنشأتك لتبدأ في نشر وظائفك والوصول إلى المرشحين.</p>

    @error('form')
      <div style="margin-top:18px;background:#FBE6E6;color:#B23232;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('employer.register.store') }}" style="margin-top:24px;display:flex;flex-direction:column;gap:18px">
      @csrf

      <div>
        <div style="font-size:14px;font-weight:800;color:#2B2724;margin-bottom:9px">نوع الحساب</div>
        <div style="display:flex;gap:10px">
          @foreach (['company' => 'منشأة', 'individual' => 'فرد'] as $value => $label)
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="type" value="{{ $value }}" @checked(old('type', 'company') === $value) onchange="document.getElementById('cr-field').style.display = this.value === 'company' ? 'block' : 'none'" style="position:absolute;opacity:0;pointer-events:none" class="role-radio">
              <span style="display:block;text-align:center;font-size:14px;font-weight:800;padding:13px;border-radius:14px;border:1.5px solid #E7E3DA;background:#FBFAF7;color:#6C665C">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <x-site.field name="name" label="اسم المنشأة" placeholder="مثال: كافيه ميلاج"/>
      <x-site.field name="responsible_person_name" label="اسم الشخص المسؤول" placeholder="الاسم الكامل"/>

      <div id="cr-field" style="display:{{ old('type', 'company') === 'company' ? 'block' : 'none' }}">
        <x-site.field name="commercial_registration" label="رقم السجل التجاري" placeholder="10 أرقام" dir="ltr"/>
      </div>

      <div>
        <div style="font-size:14px;font-weight:800;color:#2B2724;margin-bottom:9px">المدينة</div>
        <select name="city_id" style="width:100%;height:50px;border:1px solid {{ $errors->has('city_id') ? '#B23232' : '#E7E3DA' }};border-radius:14px;background:#FBFAF7;padding:0 14px;font-family:inherit;font-size:15px;font-weight:600;color:#2B2724;outline:none">
          <option value="">اختر المدينة</option>
          @foreach ($cities as $city)
            <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>{{ $city->name }}</option>
          @endforeach
        </select>
        @error('city_id')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
      </div>

      <x-site.field name="about" label="نبذة عن المنشأة" type="textarea" placeholder="وصف موجز لنشاط المنشأة…"/>

      <button type="submit" class="btn" style="display:flex;align-items:center;justify-content:center;gap:9px;background:#2B2724;color:#fff;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:15px;border-radius:15px;cursor:pointer">
        <i class="iconsax" style="font-size:20px;color:#F7BE17" icon-name="tick-circle"></i>حفظ ومتابعة
      </button>
    </form>
  </div>
</section>

<style>
  .role-radio:checked + span { border-color:#F7BE17 !important; background:#FDF3D6 !important; color:#8A6D12 !important; }
</style>
@endsection
