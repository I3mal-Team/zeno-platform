@extends('site.layouts.app')

@section('title', 'أكمل بياناتك')

@php
  $boxBase = 'width:100%;height:54px;border:1px solid #ECEAE3;border-radius:16px;background:#fff;padding:0 18px;font-family:inherit;font-size:15px;font-weight:600;color:#2B2724;outline:none';
  $errBorder = fn ($field) => $errors->has($field) ? '#E5484D' : '#ECEAE3';
@endphp

@section('content')
<x-site.header/>

<section style="max-width:540px;margin:0 auto;padding:40px 22px 64px">
  <h1 style="font-size:27px;font-weight:900;color:#211F20">أكمل بياناتك</h1>
  <p style="font-size:15px;color:#8A857A;font-weight:500;margin-top:8px;line-height:1.7">عرّفنا بنفسك ليصل ملفك لأصحاب العمل بسهولة.</p>

  <form method="POST" action="{{ route('profile.complete.store') }}" style="margin-top:24px;display:flex;flex-direction:column;gap:13px">
    @csrf

    <div>
      <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="الاسم الكامل"
        style="{{ $boxBase }};border-color:{{ $errBorder('full_name') }};text-align:right">
      @error('full_name')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    <div>
      <input type="text" name="national_id" value="{{ old('national_id') }}" inputmode="numeric" maxlength="10" placeholder="رقم الهوية أو الإقامة"
        style="{{ $boxBase }};border-color:{{ $errBorder('national_id') }};direction:ltr;text-align:right">
      @error('national_id')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    <div style="display:flex;gap:12px">
      <div style="flex:1.7">
        <select name="city_id" style="{{ $boxBase }};border-color:{{ $errBorder('city_id') }};color:{{ old('city_id') ? '#2B2724' : '#9A948A' }};cursor:pointer">
          <option value="" style="color:#9A948A">المدينة</option>
          @foreach ($cities as $city)
            <option value="{{ $city->id }}" style="color:#2B2724" @selected(old('city_id') == $city->id)>{{ $city->name }}</option>
          @endforeach
        </select>
        @error('city_id')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
      </div>
      <div style="flex:1">
        <input type="text" name="age" value="{{ old('age') }}" inputmode="numeric" maxlength="2" placeholder="العمر"
          style="{{ $boxBase }};border-color:{{ $errBorder('age') }};direction:ltr;text-align:right">
        @error('age')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
      </div>
    </div>

    <div>
      <select name="nationality_code" style="{{ $boxBase }};border-color:{{ $errBorder('nationality_code') }};color:{{ old('nationality_code') ? '#2B2724' : '#9A948A' }};cursor:pointer">
        <option value="" style="color:#9A948A">الجنسية</option>
        @foreach ($countries as $country)
          <option value="{{ $country->iso2 }}" style="color:#2B2724" @selected(old('nationality_code') === $country->iso2)>{{ $country->flag }} {{ $country->name }}</option>
        @endforeach
      </select>
      @error('nationality_code')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    <div>
      <input type="text" name="job_title" value="{{ old('job_title') }}" placeholder="المهنة الحالية أو المستهدفة"
        style="{{ $boxBase }};border-color:{{ $errBorder('job_title') }};text-align:right">
      @error('job_title')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    <div>
      <input type="text" name="years_of_experience" value="{{ old('years_of_experience') }}" inputmode="numeric" maxlength="2" placeholder="سنوات الخبرة"
        style="{{ $boxBase }};border-color:{{ $errBorder('years_of_experience') }};direction:ltr;text-align:right">
      @error('years_of_experience')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    <div>
      <textarea name="bio" placeholder="نبذة مختصرة عنك (اختياري)"
        style="width:100%;height:120px;resize:none;border:1px solid {{ $errBorder('bio') }};border-radius:16px;background:#fff;padding:15px 18px;font-family:inherit;font-size:15px;font-weight:600;color:#2B2724;outline:none;text-align:right">{{ old('bio') }}</textarea>
      @error('bio')<div style="font-size:13px;color:#E5484D;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    <button type="submit" style="display:flex;align-items:center;justify-content:center;gap:9px;background:#F7BE17;color:#2B2724;border:none;font-family:inherit;font-size:16px;font-weight:800;padding:16px;border-radius:16px;cursor:pointer;margin-top:8px">
      إنشاء الحساب
      <i class="iconsax" style="font-size:20px;color:#2B2724" icon-name="arrow-left"></i>
    </button>
  </form>
</section>
@endsection
