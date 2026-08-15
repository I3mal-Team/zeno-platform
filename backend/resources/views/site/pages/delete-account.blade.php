@extends('site.layouts.app')

@section('title', 'حذف الحساب')

@section('description', 'احذف حسابك وبياناتك من المنصة نهائيًا.')

@section('content')
<x-site.header/>

<section style="max-width:1180px;margin:0 auto;padding:44px 26px 20px">
  <div style="font-size:13.5px;font-weight:700;color:#869089">
    <a href="{{ route('site.home') }}" class="lnk">الرئيسية</a> › <span style="color:#284C3D">حذف الحساب</span>
  </div>
  <h1 style="font-size:40px;font-weight:900;color:#22302A;margin-top:14px;letter-spacing:-.01em">حذف الحساب</h1>
</section>

<div style="max-width:760px;margin:0 auto;padding:20px 26px 20px">

  @if (session('account_deleted'))

    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:40px;text-align:center">
      <div style="width:64px;height:64px;border-radius:20px;background:#E7F4EC;color:#1F8A4D;display:flex;align-items:center;justify-content:center;margin:0 auto"><i class="iconsax" style="font-size:30px" icon-name="tick-circle"></i></div>
      <div style="font-size:22px;font-weight:900;color:#22302A;margin-top:18px">تم حذف حسابك</div>
      <p style="font-size:15.5px;color:#5C6862;font-weight:500;line-height:1.85;margin-top:10px">حُذف ملفك وبياناتك الشخصية نهائيًا، وتم تسجيل خروجك من كل الأجهزة. يمكنك التسجيل من جديد بنفس رقم جوالك في أي وقت.</p>
      <a href="{{ route('site.home') }}" class="btn" style="display:inline-block;margin-top:22px;background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:11px 22px;border-radius:13px">العودة للرئيسية</a>
    </div>

  @else

    <div style="background:#fff;border:1px solid #E5EAE6;border-radius:22px;padding:34px">

      <p style="font-size:15.5px;color:#5C6862;font-weight:500;line-height:1.85">يمكنك حذف حسابك وبياناتك من هذه الصفحة، أو من داخل التطبيق مباشرة: <strong style="color:#284C3D">حسابي ← حذف الحساب</strong>.</p>

      <div style="margin-top:26px;background:#FBE6E6;border:1px solid #F3D4D4;border-radius:18px;padding:22px">
        <div style="display:flex;align-items:center;gap:10px">
          <i class="iconsax" style="font-size:22px;color:#B23232" icon-name="danger"></i>
          <div style="font-size:16.5px;font-weight:900;color:#8E2626">الحذف نهائي ولا يمكن التراجع عنه</div>
        </div>
        <div style="font-size:14.5px;color:#8E2626;font-weight:700;margin-top:14px">سيُحذف:</div>
        <ul style="margin-top:9px;padding-right:20px;display:flex;flex-direction:column;gap:7px">
          @foreach ([
            'ملفك الشخصي وبياناتك ومرفقاتك (السيرة الذاتية والصورة)',
            'طلبات التوظيف التي قدّمتها — وتختفي كذلك من قوائم أصحاب العمل',
            'محادثاتك ورسائلك',
            'إشعاراتك وتنبيهات الوظائف والوظائف المحفوظة',
          ] as $item)
            <li style="font-size:14.5px;color:#9A3B3B;font-weight:600;line-height:1.7">{{ $item }}</li>
          @endforeach
        </ul>
      </div>

      <p style="font-size:14.5px;color:#7E8B84;font-weight:600;line-height:1.8;margin-top:18px">يتحرّر رقم جوالك فور الحذف، فيمكنك إنشاء حساب جديد به متى شئت. تبقى الإعلانات التي نشرتها منشأتك — إن كنت صاحب عمل — مرتبطة بالمنشأة لا بحسابك الشخصي.</p>

      <div style="height:1px;background:#EEF2EF;margin:28px 0"></div>

      @auth

        <div style="font-size:14px;color:#7E8B84;font-weight:700">أنت مسجّل الدخول برقم <span style="direction:ltr;display:inline-block;color:#284C3D">{{ auth()->user()->phone_e164 }}</span></div>

        <form method="POST" action="{{ route('site.account.delete.submit') }}" style="margin-top:16px" onsubmit="return confirm('سيتم حذف حسابك نهائيًا. هل أنت متأكد؟')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn" style="width:100%;display:flex;align-items:center;justify-content:center;gap:9px;background:#B23232;color:#fff;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:15px;border-radius:15px;cursor:pointer">
            <i class="iconsax" style="font-size:20px" icon-name="trash"></i>حذف حسابي نهائيًا
          </button>
        </form>

      @else

        <div style="font-size:16px;font-weight:900;color:#22302A">سجّل الدخول للمتابعة</div>
        <p style="font-size:14.5px;color:#5C6862;font-weight:500;line-height:1.8;margin-top:8px">نحتاج التأكد من هويتك قبل حذف الحساب، فسجّل الدخول برقم جوالك ثم عُد إلى هذه الصفحة.</p>
        <a href="{{ route('login') }}" class="btn" style="display:inline-block;margin-top:16px;background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:12px 24px;border-radius:13px">تسجيل الدخول</a>

      @endauth

      <div style="margin-top:28px;background:#F7F9F7;border:1px solid #F0EDE6;border-radius:18px;padding:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div>
          <div style="font-size:15.5px;font-weight:800;color:#284C3D">تحتاج مساعدة أو لا يمكنك الدخول؟</div>
          <div style="font-size:14px;color:#7E8B84;font-weight:600;margin-top:3px">راسل فريق الدعم وسنساعدك في إتمام الطلب.</div>
        </div>
        <a href="{{ route('site.contact') }}" class="btn" style="background:#284C3D;color:#fff;font-size:14.5px;font-weight:800;padding:11px 22px;border-radius:13px">تواصل معنا</a>
      </div>

    </div>

  @endif

</div>

<x-site.footer/>
@endsection
