{{--
  Shared shell for the public legal pages (privacy policy, account deletion).
  Reviewers open these signed out, so nothing here may depend on an auth session.
--}}
@extends('site.layouts.app')

@section('title', $pageTitle)

@section('content')
<x-site.header/>

<section style="max-width:1180px;margin:0 auto;padding:44px 26px 20px">
  <div style="font-size:13.5px;font-weight:700;color:#869089">
    <a href="{{ route('site.home') }}" class="lnk">الرئيسية</a> › <span style="color:#284C3D">{{ $pageTitle }}</span>
  </div>
  <h1 style="font-size:40px;font-weight:900;color:#22302A;margin-top:14px;letter-spacing:-.01em">{{ $pageTitle }}</h1>
  @if (! empty($intro))
    <p style="font-size:16px;color:#5C6862;font-weight:500;line-height:1.85;margin-top:12px;max-width:760px">{{ $intro }}</p>
  @endif
  <div style="font-size:14px;color:#7E8B84;font-weight:600;margin-top:8px">آخر تحديث: {{ $lastUpdated }}</div>
</section>

<div class="g-terms" style="max-width:1180px;margin:0 auto;padding:20px 26px 20px;display:grid;grid-template-columns:250px 1fr;gap:30px;align-items:start">

  <aside style="position:sticky;top:92px;background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:20px">
    <div style="font-size:13px;font-weight:800;color:#7E8B84;letter-spacing:.04em;margin-bottom:14px">المحتويات</div>
    <div style="display:flex;flex-direction:column;gap:11px">
      @foreach ($sections as $section)
        <a href="#{{ $section['id'] }}" class="lnk" style="font-size:14.5px;font-weight:700;color:#5C6862">{{ $section['number'] }}. {{ $section['title'] }}</a>
      @endforeach
    </div>
  </aside>

  <div style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:34px">
    @foreach ($sections as $index => $section)
      <section id="{{ $section['id'] }}" style="scroll-margin-top:90px;padding-top:{{ $index === 0 ? '0' : '26px' }}">
        <h2 style="font-size:22px;font-weight:900;color:#22302A">{{ $section['number'] }}. {{ $section['title'] }}</h2>

        @foreach ($section['paragraphs'] as $paragraph)
          <p style="font-size:15.5px;color:#5C6862;font-weight:500;line-height:1.85;margin-top:12px">{{ $paragraph }}</p>
        @endforeach

        @if ($section['list'])
          <ul style="margin-top:14px;padding-right:20px;display:flex;flex-direction:column;gap:9px">
            @foreach ($section['list'] as $item)
              <li style="font-size:15px;color:#5C6862;font-weight:500;line-height:1.7">{{ $item }}</li>
            @endforeach
          </ul>
        @endif
      </section>
    @endforeach

    <div style="margin-top:34px;background:#F7F9F7;border:1px solid #F0EDE6;border-radius:18px;padding:22px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
      <div>
        <div style="font-size:16px;font-weight:800;color:#284C3D">{{ $ctaTitle }}</div>
        <div style="font-size:14px;color:#7E8B84;font-weight:600;margin-top:3px">{{ $ctaSubtitle }}</div>
      </div>
      <a href="mailto:{{ $contactEmail }}" class="btn" style="background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:11px 22px;border-radius:13px">{{ $contactEmail }}</a>
    </div>
  </div>
</div>

<x-site.footer/>
@endsection

@push('head')
<style>@media (max-width: 900px) { .g-terms { grid-template-columns: 1fr !important } .g-terms aside { position: static !important } }</style>
@endpush
