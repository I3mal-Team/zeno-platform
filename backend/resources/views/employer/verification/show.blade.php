@extends('employer.layouts.app', ['active' => 'overview', 'pageTitle' => 'توثيق المنشأة', 'pageSubtitle' => 'وثّق منشأتك لتنشر إعلاناتك فوراً وتظهر بشارة الثقة'])

@section('title', 'توثيق المنشأة')

@use('App\Enums\VerificationRequestStatus')
@use('App\Enums\VerificationStatus')

@section('content')
<div class="view" style="max-width:720px">
  @if (session('status'))
    <div style="background:#E7F4EC;color:#1F8A4D;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ session('status') }}</div>
  @endif

  @error('form')
    <div style="background:#FBE6E6;color:#B23232;border-radius:14px;padding:13px 16px;font-size:14px;font-weight:700;margin-bottom:16px">{{ $message }}</div>
  @enderror

  <div style="background:#fff;border:1px solid #EDEAE2;border-radius:20px;padding:24px">
    <div style="display:flex;align-items:center;gap:14px">
      <div style="width:54px;height:54px;border-radius:16px;background:#E7F4EC;display:flex;align-items:center;justify-content:center;color:#1F8A4D;flex:0 0 auto"><i class="iconsax" style="font-size:26px" icon-name="verify"></i></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:18px;font-weight:800;color:#211F20">{{ $organization->name }}</div>
        <div style="font-size:13.5px;color:#8A857A;font-weight:600;margin-top:3px">الحالة الحالية: {{ $organization->verification_status->label() }}</div>
      </div>
      <span style="font-size:12.5px;font-weight:800;padding:6px 12px;border-radius:9px;background:{{ $organization->verification_status === VerificationStatus::Verified ? '#E7F4EC' : '#FDF3D6' }};color:{{ $organization->verification_status === VerificationStatus::Verified ? '#1F8A4D' : '#8A6D12' }}">{{ $organization->verification_status->label() }}</span>
    </div>

    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #F0EEE7">
      @if ($organization->verification_status === VerificationStatus::Verified)
        <div style="font-size:15px;color:#2B2724;font-weight:700;line-height:1.8">منشأتك موثّقة. إعلاناتك تُنشر مباشرة بدون مراجعة، وتظهر بشارة «منشأة موثّقة» للمرشحين.</div>

      @elseif ($request && $request->status === VerificationRequestStatus::Pending)
        <div style="font-size:15px;color:#2B2724;font-weight:700;line-height:1.8">طلبك قيد المراجعة — أُرسل {{ $request->created_at->diffForHumans() }}.</div>
        <div style="font-size:13.5px;color:#8A857A;font-weight:600;margin-top:6px">أرفقت {{ $request->getMedia(App\Models\VerificationRequest::DOCUMENTS_COLLECTION)->count() }} مستند. سنبلغك بالنتيجة.</div>

      @else
        @if ($request && $request->status === VerificationRequestStatus::Rejected)
          <div style="background:#FBEDEA;border-radius:14px;padding:14px 16px;margin-bottom:18px">
            <div style="font-size:14.5px;font-weight:800;color:#B2453A">لم يُقبل الطلب السابق</div>
            @if ($request->review_note)
              <div style="font-size:13.5px;color:#8A5A52;font-weight:600;margin-top:5px;line-height:1.7">{{ $request->review_note }}</div>
            @endif
            <div style="font-size:13px;color:#8A857A;font-weight:600;margin-top:6px">صحّح المستندات وأعد الإرسال.</div>
          </div>
        @endif

        <div style="font-size:15px;font-weight:800;color:#211F20">أرفق مستندات التوثيق</div>
        <div style="font-size:13.5px;color:#8A857A;font-weight:600;margin-top:5px;line-height:1.8">
          {{ $organization->type->value === 'company' ? 'صورة السجل التجاري ساري المفعول، وأي مستند يثبت صفتك في المنشأة.' : 'صورة الهوية الوطنية أو الإقامة، وأي وثيقة تثبت نشاطك التجاري.' }}
          <br>الصيغ المقبولة: JPG · PNG · PDF — حتى ٥ ملفات، ٥ ميجابايت للملف.
        </div>

        <form method="POST" action="{{ route('employer.verification.store') }}" enctype="multipart/form-data" style="margin-top:18px">
          @csrf
          <input type="file" name="documents[]" multiple required accept="image/jpeg,image/png,application/pdf" style="width:100%;border:1.5px dashed #E7E3DA;border-radius:14px;background:#FBFAF7;padding:16px;font-family:inherit;font-size:14px;font-weight:600;color:#2B2724">
          @error('documents')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror
          @error('documents.*')<div style="font-size:13px;color:#B23232;font-weight:700;margin-top:6px">{{ $message }}</div>@enderror

          <button type="submit" class="btn" style="margin-top:16px;display:flex;align-items:center;gap:8px;height:46px;padding:0 22px;border-radius:13px;background:#2B2724;border:none;cursor:pointer;color:#fff;font-family:inherit;font-size:15px;font-weight:800"><i class="iconsax" style="font-size:19px;color:#F7BE17" icon-name="verify"></i>إرسال طلب التوثيق</button>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
