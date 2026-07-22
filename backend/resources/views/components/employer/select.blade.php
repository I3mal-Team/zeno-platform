@props(['name', 'label', 'options', 'selected' => null])

@php($hasError = $errors->has($name))

<div>
  <div style="font-size:14px;font-weight:800;color:#2B2724;margin-bottom:9px">{{ $label }}</div>
  <select name="{{ $name }}" style="width:100%;height:50px;border:1px solid {{ $hasError ? '#B23232' : '#E7E3DA' }};border-radius:14px;background:#FBFAF7;padding:0 12px;font-family:inherit;font-size:15px;font-weight:700;color:#2B2724;outline:none;cursor:pointer">
    <option value="">اختر…</option>
    @foreach ($options as $option)
      <option value="{{ $option->id }}" @selected((string) old($name, $selected) === (string) $option->id)>{{ $option->name }}</option>
    @endforeach
  </select>
  @error($name)<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
</div>
