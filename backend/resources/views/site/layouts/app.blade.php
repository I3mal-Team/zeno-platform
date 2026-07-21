<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'زينو') — زينو</title>
    <meta name="description" content="@yield('description', 'منصة الوظائف التشغيلية والخدمية والموسمية — الفرصة الأقرب إليك.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="bg-paper text-ink font-sans antialiased">
    <x-site.header/>
    <main>@yield('content')</main>
    <x-site.footer/>
</body>
</html>
