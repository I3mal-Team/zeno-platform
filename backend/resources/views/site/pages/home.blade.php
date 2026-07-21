@extends('site.layouts.app')

@section('title', 'وظيفتك القريبة على بُعد خطوة')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-20">
        <h1 class="max-w-2xl text-5xl font-black leading-tight">
            وظيفتك القريبة <span class="text-amber">على بُعد خطوة</span>
        </h1>
        <p class="mt-5 max-w-xl text-lg text-body">
            زينو تربط الباحثين عن عمل بالمنشآت القريبة منهم — بخريطة تفاعلية وتواصل مباشر.
        </p>
    </section>

    <section class="mx-auto max-w-6xl px-5 pb-16">
        <h2 class="text-3xl font-black">تصفّح حسب المجال</h2>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($categories as $category)
                <x-site.category-card :$category/>
            @endforeach
        </div>
    </section>
@endsection
