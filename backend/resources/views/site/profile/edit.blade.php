@extends('site.layouts.app')

@section('title', 'تعديل بياناتي')

@section('content')
<x-site.header/>

<section style="max-width:540px;margin:0 auto;padding:40px 22px 64px">
  <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:6px;color:#7E8B84;font-size:14px;font-weight:800;text-decoration:none;margin-bottom:14px"><i class="iconsax" style="font-size:18px" icon-name="arrow-right-1"></i>رجوع للوحة</a>

  <h1 style="font-size:27px;font-weight:900;color:#22302A">تعديل بياناتي</h1>
  <p style="font-size:15px;color:#7E8B84;font-weight:500;margin-top:8px;line-height:1.7">حدّث بياناتك ليبقى ملفك جاهزاً لأصحاب العمل.</p>

  @if (session('status'))
    <div style="margin-top:18px;background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ session('status') }}</div>
  @endif

  @php($avatarUrl = $profile->getFirstMediaUrl(\App\Models\CandidateProfile::AVATAR_COLLECTION))
  <div style="margin-top:20px;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:20px;display:flex;align-items:center;gap:18px">
    <div style="width:74px;height:74px;border-radius:20px;overflow:hidden;flex:0 0 auto;background:radial-gradient(circle at 30% 26%,#F3ECD6,#F6D783);display:flex;align-items:center;justify-content:center">
      @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="الصورة الشخصية" style="width:100%;height:100%;object-fit:cover">
      @else
        <span style="font-size:30px;font-weight:900;color:#7A5E0E">{{ mb_substr($profile->full_name ?: 'م', 0, 1) }}</span>
      @endif
    </div>
    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" style="flex:1">
      @csrf
      <div style="font-size:15px;font-weight:800;color:#284C3D">الصورة الشخصية</div>
      <div style="font-size:13px;color:#869089;font-weight:600;margin-top:3px;margin-bottom:10px">JPG أو PNG أو WEBP</div>
      <label style="display:inline-flex;align-items:center;gap:8px;background:#F3ECD6;color:#8A6D12;font-size:14px;font-weight:800;padding:10px 16px;border-radius:12px;cursor:pointer">
        <i class="iconsax" style="font-size:18px" icon-name="camera"></i>{{ $avatarUrl ? 'تغيير الصورة' : 'رفع صورة' }}
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required onchange="this.form.submit()" style="display:none">
      </label>
      @error('avatar')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:8px">{{ $message }}</div>@enderror
    </form>
  </div>

  @include('site.profile._form', [
    'action' => route('profile.update'),
    'method' => 'PUT',
    'submitLabel' => 'حفظ التعديلات',
    'profile' => $profile,
  ])
</section>
@endsection
