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

  {{-- Application-form builder --}}
  @php($afSeed = array_values(old('application_fields', $j?->application_fields ?? [])))
  <div class="cardh" style="background:#fff;border:1px solid #E5EAE6;border-radius:20px;padding:22px;display:flex;flex-direction:column;gap:14px">
    <div>
      <div style="font-size:15px;font-weight:900;color:#22302A">نموذج التقديم <span style="font-weight:700;color:#869089;font-size:13px">(اختياري)</span></div>
      <div style="font-size:13px;color:#869089;font-weight:600;margin-top:4px">أضف الحقول التي يملؤها المتقدّم — سيرة ذاتية، صورة، سؤال، رقم، أو قائمة اختيار. اترك النموذج فارغًا للتقديم بنقرة واحدة.</div>
    </div>

    <div id="af-rows" data-seed='@json($afSeed, JSON_UNESCAPED_UNICODE)' style="display:flex;flex-direction:column;gap:12px"></div>

    <button type="button" id="af-add" class="btn" style="align-self:flex-start;background:#F3ECD6;color:#8A6D12;border:none;font-family:inherit;font-size:14px;font-weight:800;padding:11px 18px;border-radius:12px;cursor:pointer">＋ إضافة حقل</button>
  </div>

  <template id="af-tpl">
    <div class="af-row" style="background:#F7F9F7;border:1px solid #E5EAE6;border-radius:15px;padding:14px">
      <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
        <input class="af-label af-input" data-name="label" type="text" placeholder="عنوان الحقل — مثال: السيرة الذاتية" style="flex:1;min-width:200px">
        <select class="af-type af-input" data-name="type" style="width:150px">
          @foreach (\App\Enums\ApplicationFieldType::cases() as $t)
            <option value="{{ $t->value }}">{{ $t->label() }}</option>
          @endforeach
        </select>
        <label style="display:flex;align-items:center;gap:6px;font-size:13.5px;font-weight:800;color:#284C3D;cursor:pointer;white-space:nowrap">
          <input class="af-required" data-name="required" type="checkbox" value="1" style="width:17px;height:17px;accent-color:#284C3D">مطلوب
        </label>
        <button type="button" class="af-remove" title="حذف" style="background:#FBE6E6;color:#B23232;border:none;width:38px;height:38px;border-radius:11px;cursor:pointer;font-size:18px;font-weight:900;line-height:1">×</button>
      </div>
      <div class="af-options-wrap" style="margin-top:10px;display:none">
        <input class="af-options af-input" type="text" placeholder="خيارات القائمة، مفصولة بفاصلة — مثال: دوام كامل، دوام جزئي" style="width:100%">
      </div>
    </div>
  </template>

  <div style="display:flex;gap:12px">
    <button type="submit" class="btn" style="background:#C9A24B;color:#284C3D;border:none;font-family:inherit;font-size:15.5px;font-weight:800;padding:14px 26px;border-radius:14px;cursor:pointer">{{ $submitLabel }}</button>
    <a href="{{ route('employer.jobs.index') }}" class="btn" style="background:#fff;color:#284C3D;border:1px solid #DCE3DD;font-size:15px;font-weight:800;padding:14px 24px;border-radius:14px">إلغاء</a>
  </div>
</form>

<style>
  .ch-radio:checked + span { border-color:#C9A24B !important; background:#F3ECD6 !important; color:#8A6D12 !important; }
  .af-input { border:1.5px solid #DCE3DD;border-radius:12px;background:#fff;padding:11px 13px;font-family:inherit;font-size:14.5px;font-weight:600;color:#284C3D;outline:none }
  .af-input:focus { border-color:#C9A24B }
  @media (max-width: 900px) {
    .job-form-grid { grid-template-columns:1fr !important; }
  }
</style>

<script>
  (function () {
    const rows = document.getElementById('af-rows');
    const tpl = document.getElementById('af-tpl');
    const form = rows.closest('form');
    let idx = 0;

    function toggleOptions(root) {
      root.querySelector('.af-options-wrap').style.display =
        root.querySelector('.af-type').value === 'select' ? '' : 'none';
    }

    function addRow(data) {
      const root = tpl.content.firstElementChild.cloneNode(true);
      root.querySelectorAll('[data-name]').forEach((el) => {
        el.name = 'application_fields[' + idx + '][' + el.dataset.name + ']';
      });
      if (data) {
        root.querySelector('.af-label').value = data.label || '';
        root.querySelector('.af-type').value = data.type || 'text';
        root.querySelector('.af-required').checked = !!data.required;
        root.querySelector('.af-options').value = (data.options || []).join('، ');
      }
      toggleOptions(root);
      root.querySelector('.af-type').addEventListener('change', () => toggleOptions(root));
      root.querySelector('.af-remove').addEventListener('click', () => root.remove());
      rows.appendChild(root);
      idx++;
    }

    document.getElementById('af-add').addEventListener('click', () => addRow(null));

    (JSON.parse(rows.dataset.seed || '[]')).forEach(addRow);

    form.addEventListener('submit', () => {
      rows.querySelectorAll('.af-row').forEach((root) => {
        root.querySelectorAll('.af-opt-hidden').forEach((h) => h.remove());
        if (!root.querySelector('.af-label').value.trim()) {
          root.remove();
          return;
        }
        if (root.querySelector('.af-type').value !== 'select') return;
        const base = root.querySelector('.af-label').name.replace('[label]', '');
        root.querySelector('.af-options').value.split(/[،,]/).map((s) => s.trim()).filter(Boolean).forEach((opt) => {
          const h = document.createElement('input');
          h.type = 'hidden';
          h.className = 'af-opt-hidden';
          h.name = base + '[options][]';
          h.value = opt;
          root.appendChild(h);
        });
      });
    });
  })();
</script>
