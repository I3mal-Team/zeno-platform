@php($j = $job ?? null)

<form method="POST" action="{{ $action }}" style="display:flex;flex-direction:column;gap:18px;width:100%">
  @csrf
  @if (($method ?? 'POST') === 'PUT') @method('PUT') @endif

  @if ($errors->has('form'))
    <div style="background:#FBE6E6;color:#B23232;border-radius:14px;padding:12px 16px;font-size:14px;font-weight:700">{{ $errors->first('form') }}</div>
  @endif

  <div class="job-form-grid" style="display:grid;grid-template-columns:1.5fr 1fr;gap:18px;align-items:start">

    {{-- Main details --}}
    <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;display:flex;flex-direction:column;gap:16px">
      <div style="font-size:15px;font-weight:900;color:#22302A">تفاصيل الوظيفة</div>

      <x-employer.field name="title" label="المسمى الوظيفي" :value="old('title', $j?->title)" placeholder="مثال: باريستا"/>

      <div class="g-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <x-employer.select name="category_id" label="التصنيف" :options="$categories" :selected="old('category_id', $j?->category_id)"/>
        <x-employer.select name="work_type_id" label="نوع الدوام" :options="$workTypes" :selected="old('work_type_id', $j?->work_type_id)"/>
      </div>

      <div class="g-2" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        <x-employer.field name="salary_amount" label="الراتب / الأجر" type="number" :value="old('salary_amount', $j?->salary_amount)" placeholder="4500"/>
        <x-employer.select name="salary_unit_id" label="الوحدة" :options="$salaryUnits" :selected="old('salary_unit_id', $j?->salary_unit_id)"/>
        <x-employer.field name="vacancies_count" label="عدد الشواغر" type="number" :value="old('vacancies_count', $j?->vacancies_count ?? 1)"/>
      </div>

      <div class="g-2" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        <x-employer.select name="gender_requirement_id" label="الجنس المطلوب" :options="$genderRequirements" :selected="old('gender_requirement_id', $j?->gender_requirement_id)"/>
        <x-employer.select name="nationality_requirement_id" label="الجنسية" :options="$nationalityRequirements" :selected="old('nationality_requirement_id', $j?->nationality_requirement_id)"/>
        <x-employer.select name="city_id" label="المدينة" :options="$cities" :selected="old('city_id', $j?->city_id)"/>
      </div>
    </div>

    {{-- Contact + description --}}
    <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;display:flex;flex-direction:column;gap:16px">
      <div style="font-size:15px;font-weight:900;color:#22302A">التواصل والوصف</div>

      <div>
        <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">طريقة التواصل</div>
        <div style="display:flex;gap:10px">
          @foreach (['app' => 'داخل التطبيق', 'whatsapp' => 'واتساب', 'both' => 'كلاهما'] as $value => $label)
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="contact_channel" value="{{ $value }}" @checked(old('contact_channel', $j?->contact_channel->value ?? 'app') === $value) style="position:absolute;opacity:0;pointer-events:none" class="ch-radio">
              <span style="display:block;text-align:center;font-size:14px;font-weight:800;padding:12px;border-radius:13px;border:1.5px solid #DCE3DD;background:#F7F9F7;color:#5C6862">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      <div style="display:flex;flex-direction:column;flex:1">
        <div style="font-size:14px;font-weight:800;color:#284C3D;margin-bottom:9px">وصف الوظيفة</div>
        <textarea name="description" placeholder="تفاصيل المهام والمتطلبات…" style="width:100%;min-height:220px;flex:1;resize:vertical;border:1px solid {{ $errors->has('description') ? '#B23232' : '#DCE3DD' }};border-radius:14px;background:#F7F9F7;padding:14px 16px;font-family:inherit;font-size:15px;font-weight:600;color:#284C3D;outline:none;line-height:1.7">{{ old('description', $j?->description) }}</textarea>
      </div>
    </div>

  </div>

  <div style="display:flex;gap:12px">
    <button type="submit" class="btn" style="background:#C9A24B;color:#284C3D;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:14px 26px;border-radius:14px;cursor:pointer">{{ $submitLabel }}</button>
    <a href="{{ route('employer.jobs.index') }}" class="btn" style="background:#fff;color:#284C3D;border:1px solid #DCE3DD;font-size:15px;font-weight:800;padding:14px 24px;border-radius:14px">إلغاء</a>
  </div>
</form>

<style>
  .ch-radio:checked + span { border-color:#C9A24B !important; background:#F3ECD6 !important; color:#8A6D12 !important; }
  @media (max-width: 900px) {
    .job-form-grid { grid-template-columns:1fr !important; }
  }
</style>
