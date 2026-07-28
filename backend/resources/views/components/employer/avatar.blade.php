@props(['name', 'size' => 52, 'radius' => 15, 'font' => 19])

@php([$background, $foreground] = App\Support\AvatarTint::for($name))

<div style="width:{{ $size }}px;height:{{ $size }}px;border-radius:{{ $radius }}px;background:{{ $background }};display:flex;align-items:center;justify-content:center;color:{{ $foreground }};font-size:{{ $font }}px;font-weight:900;flex:0 0 auto">{{ mb_substr($name, 0, 1) }}</div>
