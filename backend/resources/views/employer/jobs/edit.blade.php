@extends('employer.layouts.app', ['active' => 'jobs', 'pageTitle' => 'تعديل الإعلان', 'pageSubtitle' => $job->title])

@section('title', 'تعديل الإعلان')

@section('content')
@include('employer.jobs._form', [
    'action' => route('employer.jobs.update', $job->uuid),
    'method' => 'PUT',
    'submitLabel' => 'حفظ التعديلات',
    'job' => $job,
])
@endsection
