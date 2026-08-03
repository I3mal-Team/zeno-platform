@props(['eyebrow', 'title', 'body' => null])

<div style="text-align:center;max-width:560px;margin:0 auto">
  <div style="font-size:14px;font-weight:800;color:#8A6D12;letter-spacing:.04em">{{ $eyebrow }}</div>
  <h2 style="font-size:38px;font-weight:900;color:#22302A;margin-top:10px;letter-spacing:-.01em">{{ $title }}</h2>
  @if ($body)
    <p style="font-size:17px;color:#5C6862;font-weight:500;margin-top:12px;line-height:1.6">{{ $body }}</p>
  @endif
</div>
