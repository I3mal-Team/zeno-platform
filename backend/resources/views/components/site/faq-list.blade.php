@props(['faqs'])

<div style="display:flex;flex-direction:column;gap:12px;margin-top:36px">
  @foreach ($faqs as $index => $faq)
    <details style="background:#fff;border:1px solid #E5EAE6;border-radius:18px;overflow:hidden" @if($index === 0) open @endif>
      <summary style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:20px 24px;cursor:pointer;list-style:none">
        <span style="font-size:17px;font-weight:800;color:#284C3D">{{ $faq['question'] }}</span>
        <span style="width:30px;height:30px;border-radius:10px;background:#F7F5EF;display:flex;align-items:center;justify-content:center;color:#8A6D12;font-size:20px;font-weight:800;flex:0 0 auto">+</span>
      </summary>
      <div style="padding:0 24px 22px;font-size:15.5px;color:#5C6862;font-weight:500;line-height:1.7">{{ $faq['answer'] }}</div>
    </details>
  @endforeach
</div>

@once
  @push('head')
    <style>
      details summary::-webkit-details-marker { display: none }
      details summary span:last-child { transition: transform .2s ease }
      details[open] summary span:last-child { transform: rotate(45deg) }
    </style>
  @endpush
@endonce
