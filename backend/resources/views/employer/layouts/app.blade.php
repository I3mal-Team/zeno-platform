<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'لوحة التحكم') — زينو</title>
    <link rel="icon" href="{{ asset('assets/zeno-mark.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link href="https://iconsax.gitlab.io/i/icons.css" rel="stylesheet">
    @vite('resources/css/employer.css')
</head>
<body>
<div style="direction:rtl;display:flex;min-height:100vh;background:#F1EFE9">
    <x-employer.sidebar :active="$active ?? 'overview'"/>

    <main style="flex:1;min-width:0;display:flex;flex-direction:column;max-height:100vh;overflow-y:auto">
        <x-employer.topbar :title="$pageTitle ?? ''" :subtitle="$pageSubtitle ?? ''"/>
        <div style="padding:26px 30px 46px">@yield('content')</div>
    </main>
</div>
</body>
</html>
