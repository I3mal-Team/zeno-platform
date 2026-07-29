@extends('site.layouts.app')

@section('title', 'تعديل بياناتي')

@section('content')
<x-site.header/>

<section style="max-width:540px;margin:0 auto;padding:40px 22px 64px">
  <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:6px;color:#8A857A;font-size:14px;font-weight:800;text-decoration:none;margin-bottom:14px"><i class="iconsax" style="font-size:18px" icon-name="arrow-right-1"></i>رجوع للوحة</a>

  <h1 style="font-size:27px;font-weight:900;color:#211F20">تعديل بياناتي</h1>
  <p style="font-size:15px;color:#8A857A;font-weight:500;margin-top:8px;line-height:1.7">حدّث بياناتك ليبقى ملفك جاهزاً لأصحاب العمل.</p>

  @if (session('status'))
    <div style="margin-top:18px;background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ session('status') }}</div>
  @endif

  @include('site.profile._form', [
    'action' => route('profile.update'),
    'method' => 'PUT',
    'submitLabel' => 'حفظ التعديلات',
    'profile' => $profile,
  ])
</section>
@endsection
