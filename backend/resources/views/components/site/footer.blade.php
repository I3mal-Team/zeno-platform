<footer style="margin-top:70px;background:#223F31;color:#B8C4BC">
  <div class="g-4" style="max-width:1180px;margin:0 auto;padding:56px 26px 30px;display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:30px">
    <div>
      <img src="{{ asset('assets/zeno-logo.png') }}" alt="AMS" style="height:34px;width:auto;filter:brightness(0) invert(1)">
      <p style="font-size:14.5px;color:#869089;font-weight:500;line-height:1.7;margin-top:16px;max-width:260px">منصة التوظيف المحلي التي تربط الباحثين عن عمل بالمنشآت القريبة منهم في السعودية.</p>
      <div style="display:flex;gap:10px;margin-top:18px">
        @foreach (['globe', 'message-text', 'send-2'] as $icon)
          <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;color:#EDE9E0"><i class="iconsax" style="font-size:19px" icon-name="{{ $icon }}"></i></div>
        @endforeach
      </div>
    </div>

    @foreach ($columns as $title => $links)
      <div>
        <div style="font-size:15px;font-weight:800;color:#fff;margin-bottom:14px">{{ $title }}</div>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:14.5px;font-weight:600">
          @foreach ($links as $label => $url)
            <a href="{{ $url }}" class="lnk" style="color:#A6B0AA">{{ $label }}</a>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>

  <div style="border-top:1px solid rgba(255,255,255,.08)">
    <div style="max-width:1180px;margin:0 auto;padding:20px 26px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:13.5px;color:#7E8B84;font-weight:600">
      <div>© {{ now()->year }} AMS. جميع الحقوق محفوظة.</div>
      <div>صُنع في السعودية 🇸🇦</div>
    </div>
  </div>
</footer>
