@props(['target', 'id', 'label' => 'إبلاغ عن هذا الإعلان'])

@auth
  @php($reasons = app(App\Services\ReportService::class)->reasonsFor(App\Enums\ReportTarget::from($target)))

  {{-- A native dialog keeps this to one component with no script beyond opening it. --}}
  <button type="button" onclick="document.getElementById('report-{{ $target }}-{{ $id }}').showModal()" class="btn" style="display:inline-flex;align-items:center;gap:7px;background:none;border:none;cursor:pointer;font-family:inherit;font-size:13.5px;font-weight:800;color:#7E8B84;padding:0">
    <i class="iconsax" style="font-size:17px" icon-name="flag"></i>{{ $label }}
  </button>

  <dialog id="report-{{ $target }}-{{ $id }}" style="border:none;border-radius:22px;padding:0;max-width:460px;width:calc(100% - 32px);background:#fff;box-shadow:0 30px 60px -30px rgba(40,35,25,.5)">
    <form method="POST" action="{{ route('site.report', ['target' => $target, 'id' => $id]) }}" style="direction:rtl;padding:26px">
      @csrf

      <div style="font-size:19px;font-weight:900;color:#22302A">{{ $label }}</div>
      <div style="font-size:13.5px;color:#7E8B84;font-weight:600;margin-top:5px;line-height:1.7">اختر السبب، وسيراجع فريقنا البلاغ. لن يعرف الطرف الآخر من أرسله.</div>

      <div style="display:flex;flex-direction:column;gap:9px;margin-top:18px">
        @foreach ($reasons as $reason)
          <label style="display:flex;align-items:center;gap:10px;border:1px solid #DCE3DD;border-radius:13px;padding:12px 14px;cursor:pointer;background:#F7F9F7">
            <input type="radio" name="report_reason_id" value="{{ $reason->id }}" required style="accent-color:#284C3D;width:17px;height:17px">
            <span style="font-size:14.5px;font-weight:700;color:#284C3D">{{ $reason->name }}</span>
          </label>
        @endforeach
      </div>

      <textarea name="details" maxlength="1000" rows="3" placeholder="تفاصيل إضافية (اختياري)" style="width:100%;margin-top:12px;border:1px solid #DCE3DD;border-radius:13px;background:#F7F9F7;padding:12px 14px;font-family:inherit;font-size:14.5px;font-weight:600;color:#284C3D;outline:none;resize:vertical"></textarea>

      <div style="display:flex;gap:10px;margin-top:18px">
        <button type="submit" class="btn" style="flex:1;height:46px;border-radius:13px;background:#B2453A;border:none;cursor:pointer;color:#fff;font-family:inherit;font-size:15px;font-weight:800">إرسال البلاغ</button>
        <button type="button" onclick="this.closest('dialog').close()" class="btn" style="height:46px;padding:0 20px;border-radius:13px;background:#EFF2EE;border:1px solid #DCE3DD;cursor:pointer;color:#284C3D;font-family:inherit;font-size:15px;font-weight:800">إلغاء</button>
      </div>
    </form>
  </dialog>
@endauth
