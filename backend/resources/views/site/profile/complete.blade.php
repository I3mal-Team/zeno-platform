@extends('site.layouts.app')

@section('title', 'أكمل بياناتك')

@section('content')
<x-site.header/>

<section style="max-width:540px;margin:0 auto;padding:40px 22px 64px">
  <h1 style="font-size:27px;font-weight:900;color:#22302A">أكمل بياناتك</h1>
  <p style="font-size:15px;color:#7E8B84;font-weight:500;margin-top:8px;line-height:1.7">عرّفنا بنفسك ليصل ملفك لأصحاب العمل بسهولة.</p>

  @include('site.profile._form', [
    'action' => route('profile.complete.store'),
    'submitLabel' => 'إنشاء الحساب',
    'profile' => null,
  ])
</section>
@endsection
