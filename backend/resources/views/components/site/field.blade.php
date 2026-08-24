@props(['name', 'label', 'type' => 'text', 'placeholder' => '', 'dir' => 'rtl'])

@php($hasError = $errors->has($name))

<div>
  <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">{{ $label }}</div>

  @if ($type === 'textarea')
    <textarea name="{{ $name }}" placeholder="{{ $placeholder }}" style="width:100%;height:130px;resize:none;border:1px solid {{ $hasError ? '#B23232' : '#DCE3DD' }};border-radius:14px;background:#F7F9F7;padding:14px 16px;font-family:inherit;font-size:15px;font-weight:600;color:#284C3D;outline:none">{{ old($name) }}</textarea>
  @else
    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $placeholder }}" style="width:100%;height:50px;border:1px solid {{ $hasError ? '#B23232' : '#DCE3DD' }};border-radius:14px;background:#F7F9F7;padding:0 16px;font-family:inherit;font-size:15px;font-weight:600;color:#284C3D;outline:none;direction:{{ $dir }};text-align:right">
  @endif

  @error($name)<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
</div>
