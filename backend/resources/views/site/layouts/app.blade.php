<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'وظائف قريبة منك') — Zeno</title>
    <meta name="description" content="@yield('description', 'منصة التوظيف المحلي التي تربط الباحثين عن عمل بالمنشآت القريبة منهم في السعودية.')">

    <link rel="icon" href="{{ asset('assets/zeno-mark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://iconsax.gitlab.io/i/icons.css" rel="stylesheet">

    @vite('resources/css/site.css')
    @stack('head')
</head>
<body>
    <div style="direction:rtl;overflow-x:hidden;background:#F4F6F3">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
