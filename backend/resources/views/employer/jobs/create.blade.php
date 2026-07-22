@extends('employer.layouts.app', ['active' => 'jobs', 'pageTitle' => 'نشر وظيفة', 'pageSubtitle' => 'أضف تفاصيل الوظيفة لتصل إلى المرشحين'])

@section('title', 'نشر وظيفة')

@section('content')
@include('employer.jobs._form', [
    'action' => route('employer.jobs.store'),
    'method' => 'POST',
    'submitLabel' => 'نشر الإعلان',
    'job' => null,
])
@endsection
