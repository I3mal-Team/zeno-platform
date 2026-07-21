# zeno — المعمارية والحزم التقنية
### Architecture & Technology Stack

> **الحالة:** معتمد · **آخر تحديث:** 20 يوليو 2026
> **يبني على:** [22-open-decisions](22-open-decisions-and-product-questions.md) — 23 قرار محسوم

---

## 0. المبدأ الحاكم

> **سطح واحد للحقيقة، أربعة أسطح للعرض.**

المشكلة الأولى في البروتوتايب أن الموبايل والداشبورد وموقع الوظائف بنوا **ثلاثة موديلات بيانات مختلفة** لنفس الكيان. المعمارية أدناه تجعل ذلك **مستحيلاً بنيوياً**: كل سطح — API للموبايل، Blade للموقع، Filament للداشبورد والإدارة — ينادي **نفس الـ Service**.

```
                    ┌──────────────────────────────┐
   Flutter ────────▶│  Api\V1\Controller           │──┐
   موقع عام ────────▶│  Web\Controller (Blade)      │──┤
   داشبورد صاحب عمل ▶│  Filament\Employer           │──┼──▶ Service ──▶ Repository ──▶ Model
   لوحة الإدارة ────▶│  Filament\Admin              │──┘   (منطق)      (استعلامات)
                    └──────────────────────────────┘
```

---

## 1. الباك إند — القواعد الأربع

هذه **قواعد لا أسلوب**. مخالفتها عيب يُرفض في المراجعة.

| # | القاعدة | يعني |
|---|---|---|
| 1 | **لا منطق أعمال في الـ Controller** | الكنترولر يستقبل، ينادي Service واحد، يرجّع Response. أكثر من ~10 أسطر = منطق تسرّب |
| 2 | **لا استعلامات في الـ Service** | ممنوع `Model::where()` أو `DB::` داخل Service. الوصول للبيانات عبر Repository فقط |
| 3 | **التحقق في الـ Request** | ممنوع `$request->validate()` داخل Controller. FormRequest دائماً |
| 4 | **الاستجابة عبر Resource + Trait موحّد** | ممنوع `response()->json()` مباشرة |

### 1.1 التدفق الكامل

```
Route
  └─▶ FormRequest          التحقق + التطبيع + ->toDto()
        └─▶ Controller     نحيف — ينادي Service ويرجّع Resource
              └─▶ Service  منطق الأعمال · الأحداث · المعاملات · الصلاحيات
                    └─▶ Repository   الاستعلامات فقط
                          └─▶ Model  Eloquent + العلاقات + الـ Casts
              ◀─── DTO / Model
        ◀─── Resource
  ◀─── ApiResponse trait
```

### 1.2 مسؤولية كل طبقة

**Controller** — ترجمة HTTP ↔ الدومين. لا شيء غير ذلك.

```php
final class JobController extends Controller
{
    public function __construct(private readonly JobService $jobs) {}

    public function store(StoreJobRequest $request): JsonResponse
    {
        $job = $this->jobs->publish($request->toDto(), $request->user());

        return $this->createdResponse(
            new JobResource($job),
            __('messages.job.published')
        );
    }
}
```

**FormRequest** — التحقق والتطبيع وبناء الـ DTO. **التطبيع هنا لا في الـ Service** (تحويل الجوال إلى E.164، تنظيف الأرقام، تطبيع النص العربي).

```php
final class StoreJobRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:120'],
            'category_id'      => ['required', 'exists:categories,id'],
            'work_type'        => ['required', Rule::enum(WorkType::class)],
            'salary_amount'    => ['required', 'numeric', 'min:0', 'max:999999'],
            'salary_unit'      => ['required', Rule::enum(SalaryUnit::class)],
            'vacancies_count'  => ['required', 'integer', 'min:1', 'max:999'],
            'expires_at'       => ['required', 'date', 'after:today', 'before:'.now()->addDays(settings()->job_max_duration_days)],
            'location'         => ['required', 'array:lat,lng'],
            // ...
        ];
    }

    public function toDto(): StoreJobData
    {
        return StoreJobData::from($this->validated());
    }
}
```

**Service** — منطق الأعمال. **صفر استعلامات.**

```php
final class JobService
{
    public function __construct(
        private readonly JobRepositoryInterface $jobs,
        private readonly OrganizationRepositoryInterface $organizations,
    ) {}

    public function publish(StoreJobData $data, User $actor): Job
    {
        $organization = $this->organizations->findForUser($actor->id)
            ?? throw new NoOrganizationException();

        if (! $organization->canPublish()) {
            throw new OrganizationNotVerifiedException();
        }

        return DB::transaction(function () use ($data, $organization) {
            $job = $this->jobs->create($data, $organization->id);

            $this->jobs->recordStatusChange($job->id, JobStatus::Active, 'published');

            JobPublished::dispatch($job);   // الإشعارات والفهرسة والتحليلات تستمع

            return $job;
        });
    }
}
```

**Repository** — الاستعلامات فقط. **صفر منطق أعمال.**

```php
interface JobRepositoryInterface
{
    public function create(StoreJobData $data, int $organizationId): Job;
    public function findActiveById(int $id): ?Job;
    public function searchNearby(JobSearchData $filters): LengthAwarePaginator;
    public function recordStatusChange(int $jobId, JobStatus $status, string $reason): void;
}
```

```php
final class EloquentJobRepository implements JobRepositoryInterface
{
    public function searchNearby(JobSearchData $f): LengthAwarePaginator
    {
        return Job::query()
            ->active()
            ->when($f->categoryId, fn ($q, $id) => $q->where('category_id', $id))
            ->when($f->workType,   fn ($q, $t)  => $q->where('work_type', $t))
            ->when($f->point, fn ($q) => $q
                ->whereDistanceSphere('location', $f->point, '<=', $f->radiusMeters)
                ->orderByDistanceSphere('location', $f->point))
            ->paginate($f->perPage);
    }
}
```

### 1.3 الاستجابة الموحّدة

**Trait واحد** `App\Traits\ApiResponse` على الـ `Controller` الأساسي.

```jsonc
// نجاح
{
  "success": true,
  "message": "تم نشر الإعلان بنجاح",
  "data": { },
  "meta": { "pagination": { } }      // عند القوائم فقط
}

// خطأ
{
  "success": false,
  "message": "لقد سبق لك التقديم على هذه الوظيفة",
  "error": {
    "code": "APPLICATION_ALREADY_EXISTS",
    "details": { },
    "trace_id": "01JG8Z..."
  }
}
```

**قواعد ملزمة:**

- **كل خطأ له `code` ثابت بالإنجليزية** — الموبايل يتفرّع على الكود لا على النص العربي.
- الرسالة العربية من ملفات الترجمة، فتتغير بلا كسر عميل.
- `trace_id` يطابق معرّف الطلب في السجلات — دعم فني قابل للتتبع.
- **معالج استثناءات مركزي** يحوّل كل استثناء دومين إلى الغلاف أعلاه. الكنترولر لا يمسك استثناءات.

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $e) {
    $e->render(fn (DomainException $ex) => app(ApiResponder::class)->fromDomain($ex));
});
```

### 1.4 الفصل الكامل بين Web و API

> **القاعدة:** كل ما هو **فوق** الـ Service منفصل تماماً لكل سطح. وكل ما هو **من** الـ Service فنازل **مشترك ولا يُكرَّر أبداً**.

```
┌──────── منفصل بالكامل ─────────────────────────────────────┐
│  Routes · Controllers · Requests · Responses · Middleware  │
│  Guards · Rate Limits · معالجة الأخطاء · الاختبارات        │
│                                                            │
│   Api/V1        Web        Filament/Employer   Filament/Admin
└─────────────────────┬──────────────────────────────────────┘
                      │
┌─────────────────────▼──── مشترك، نسخة واحدة ───────────────┐
│  Services · Repositories · Models · DTOs · Enums           │
│  Events · Jobs · Notifications · Policies · Exceptions     │
└────────────────────────────────────────────────────────────┘
```

#### ⛔ الخط الأحمر

**ممنوع منعاً باتاً:**

```
app/Services/Api/JobService.php      ❌
app/Services/Web/JobService.php      ❌
app/Repositories/Api/…               ❌
app/Data/Api/…  ·  app/Data/Web/…    ❌
```

**السبب — وهذا جوهر المشروع كله:** المشكلة الأولى في البروتوتايب أن كل سطح بنى موديله الخاص، فانتهينا بـ **3 سكيمات و3 مجموعات حالات وتصنيفات لا تتقاطع**، وداشبورد **ينهار** على حالة ينتجها الموبايل.

الفصل مطلوب في **طبقة النقل** — لأن HTTP يختلف فعلاً بين الأسطح. أما الفصل في **طبقة الدومين** فهو إعادة إنتاج للكارثة بيدك.

> **المحصلة:** فصل نظيف فوق، مصدر حقيقة واحد تحت.

#### جدول الفصل

| الطبقة | API | Web | Filament |
|---|---|---|---|
| Routes | `routes/api/v1/*.php` | `routes/web/*.php` | يولّدها Filament |
| Controller الأساسي | `ApiController` | `WebController` | Resource/Page |
| Controller | `Api\V1\Jobs\JobController` | `Web\Jobs\JobController` | `Filament\Employer\Resources` |
| Request | `Requests\Api\V1\…` | `Requests\Web\…` | Filament Form |
| المخرَج | `Resources\V1\JobResource` | `ViewModels\Jobs\…` + Blade | Filament Table |
| المصادقة | `auth:sanctum` | `auth:web` (session) | `auth:web` / `auth:admin` |
| حدود المعدل | لكل توكن — صارمة على OTP | لكل IP — أوسع | لوحة إدارة |
| الأخطاء | غلاف JSON | صفحات خطأ Blade | إشعارات Filament |
| اللغة | `Accept-Language` | جلسة/مسار | لغة اللوحة |
| الاختبارات | `Feature/Api/V1/` | `Feature/Web/` | `Feature/Filament/` |
| **Service** | **`JobService`** | **`JobService`** | **`JobService`** |
| **Repository** | **نفسه** | **نفسه** | **نفسه** |
| **DTO** | **`StoreJobData`** | **`StoreJobData`** | **`StoreJobData`** |

#### نقطة الالتقاء الوحيدة: الـ DTO

كل Request في أي سطح ينتهي إلى **نفس الـ DTO**:

```php
// Http/Requests/Api/V1/Jobs/StoreJobRequest.php
public function toDto(): StoreJobData { return StoreJobData::from($this->validated()); }

// Http/Requests/Web/Jobs/StoreJobRequest.php   ← قواعد تحقق قد تختلف
public function toDto(): StoreJobData { return StoreJobData::from($this->validated()); }
```

قواعد التحقق قد تختلف (الويب قد يقبل رفع ملف والـ API يقبل معرّف وسائط)، **لكن مخرج التحقق واحد**. فالـ Service يستقبل شكلاً واحداً دائماً ولا يعرف من أي سطح جاء — ولا يجوز أن يعرف.

#### الأسطح الأربعة

| # | السطح | المستهلك | المسار |
|---|---|---|---|
| ① | `Api/V1` | تطبيق Flutter | `/api/v1/*` |
| ② | `Web` | الموقع العام (Blade) | `/*` |
| ③ | `Filament/Employer` | داشبورد صاحب العمل | `/employer/*` |
| ④ | `Filament/Admin` | لوحة الإدارة | `/admin/*` |

**حارس منفصل للإدارة:** جدول `admins` مستقل عن `users`، وحارس `admin` مستقل. السبب: المديرون **لا يُنشَأون عبر OTP** ولا يملكون مساراً للتسجيل الذاتي، ولا يمكن لخطأ في منطق الأدوار أن يرفع مستخدماً عادياً إلى مدير. هذا يغلق **D-31** بنيوياً لا بشرط `if`.

**مكوّنات Blade** — كل عنصر في الديزاين سيستم مكوّن، والتوكنز من `HANDOVER.md §4`:

```
resources/views/components/
├── ui/          button · badge · chip · input · select · segmented · toast · modal · drawer
├── job/         card · meta-row · status-badge · distance-badge · empty-state
├── layout/      header · footer · breadcrumb · container
└── marketing/   hero · stat-band · faq-accordion · pricing-card
```

> **قاعدة:** ممنوع لون أو مقاس مكتوب داخل صفحة. توكنز Tailwind فقط، مولّدة من نفس ملف التوكنز الذي يغذّي Filament.

### 1.5 Filament — الاستثناء المُعلن

Filament مبني على Eloquent، وإجباره على المرور بـ Repository في كل شيء يقتل قيمته. القاعدة الصريحة:

| العملية | المسار المسموح |
|---|---|
| جداول وقوائم ومرشحات (**قراءة**) | ✅ Eloquent مباشرة — هذا جوهر Filament |
| CRUD بيانات مرجعية (تصنيفات، مدن) | ✅ Eloquent مباشرة — لا منطق أعمال |
| **أي فعل يحمل قاعدة عمل** (توثيق، إيقاف حساب، حذف وظيفة، تغيير حالة طلب) | ⛔ **Service إجبارياً** |

**السبب:** الأفعال الحاملة لقواعد تُطلق أحداثاً وإشعارات وسجلات تدقيق. تنفيذها بـ Eloquent مباشرة من Filament يتجاوز كل ذلك بصمت — وهو بالضبط ما فعله البروتوتايب.

```php
Action::make('approve')
    ->requiresConfirmation()
    ->form([Textarea::make('reason')->required()])      // N-3: سبب إلزامي
    ->action(fn (Verification $r, array $data) =>
        app(VerificationService::class)->approve($r->id, auth()->user(), $data['reason']));
```

### 1.6 هيكل المجلدات

```
app/
├── Http/
│   │  ══════════ ① منفصل لكل سطح ══════════
│   ├── Controllers/
│   │   ├── ApiController.php              # قاعدة API — ApiResponse trait
│   │   ├── WebController.php              # قاعدة Web — مساعدات العرض
│   │   ├── Api/V1/
│   │   │   ├── Auth/           {RequestOtp,VerifyOtp,Session,Account}Controller
│   │   │   ├── Profile/        {Candidate,Employer}ProfileController
│   │   │   ├── Jobs/           {Job,JobSearch,JobStatus}Controller
│   │   │   ├── Applications/   {Application,ApplicationDecision}Controller
│   │   │   ├── Messaging/      {Conversation,Message}Controller
│   │   │   ├── Notifications/  {Notification,DeviceToken}Controller
│   │   │   ├── Billing/        {Subscription,Payment}Controller
│   │   │   ├── Moderation/     ReportController
│   │   │   └── Catalog/        {Category,City,District}Controller
│   │   └── Web/
│   │       ├── Marketing/      {Home,About,Pricing,Terms}Controller
│   │       ├── Jobs/           {JobIndex,JobShow}Controller
│   │       └── Contact/        ContactController
│   │
│   ├── Requests/
│   │   ├── Api/V1/{Auth,Jobs,Applications,Messaging,Profile,Billing}/
│   │   └── Web/{Contact,Jobs}/
│   │
│   ├── Resources/V1/          # ① API فقط
│   │   └── {Job,Application,Conversation,Message,User,Category}Resource.php
│   │
│   ├── ViewModels/            # ② Web فقط
│   │   └── {Jobs,Marketing}/
│   │
│   └── Middleware/
│       ├── Api/    ForceJsonResponse · ApiLocale · EnsureSubscriptionActive
│       ├── Web/    SetWebLocale · RedirectIfAuthenticated
│       └── Admin/  EnsureMfaConfirmed
│
├── Filament/                  # ③ ④ سطحان مستقلان
│   ├── Employer/{Resources,Pages,Widgets}/
│   └── Admin/{Resources,Pages,Widgets}/
│
│  ══════════ 🔒 مشترك — نسخة واحدة، ممنوع التفرّع ══════════
├── Services/{Identity,Candidate,Employer,Jobs,Applications,
│             Messaging,Notifications,Moderation,Billing,Catalog}/
├── Repositories/
│   ├── Contracts/             # الواجهات
│   └── Eloquent/              # التطبيقات
├── Models/
├── Data/                      # DTOs — نقطة الالتقاء بين الأسطح
├── Enums/
├── Events/ · Listeners/ · Jobs/ · Notifications/
├── Policies/
├── Exceptions/Domain/         # استثناءات الدومين → أكواد أخطاء
├── Support/Sms/               # SmsGateway + drivers
├── Support/Payments/          # PaymentGateway + drivers
└── Traits/ApiResponse.php

routes/
├── api.php                    # يحمّل api/v1/*
├── web.php                    # يحمّل web/*
├── api/v1/{auth,jobs,applications,messaging,notifications,billing,catalog}.php
├── web/{marketing,jobs,contact}.php
└── channels.php               # قنوات Reverb

tests/
├── Architecture/              # 🔑 تفرض القواعد آلياً
├── Feature/{Api/V1,Web,Filament}/
└── Unit/{Services,Repositories,Support}/
```

**الربط في `RepositoryServiceProvider`:**

```php
foreach ([
    JobRepositoryInterface::class          => EloquentJobRepository::class,
    ApplicationRepositoryInterface::class   => EloquentApplicationRepository::class,
    // …
] as $contract => $concrete) {
    $this->app->bind($contract, $concrete);
}
```

> **لماذا الواجهات؟** ليس لتبديل قاعدة البيانات — ذلك لن يحدث. بل لأن **اختبار Service بلا قاعدة بيانات** يصبح ممكناً، والاختبار هو ما يجعل القواعد قابلة للفرض آلياً.

### 1.7 اختبارات معمارية — الفصل مفروض آلياً

> **قاعدة لا تُفرض آلياً تُخالَف خلال شهر.** هذه الاختبارات جزء من سبرنت 0 وتحجب الدمج.

```php
// tests/Architecture/LayeringTest.php

arch('الـ Service لا يعرف HTTP')
    ->expect('App\Services')
    ->not->toUse(['App\Http', 'Illuminate\Http', 'Illuminate\Support\Facades\Request']);

arch('الـ Service لا يستعلم')
    ->expect('App\Services')
    ->not->toUse(['Illuminate\Database', 'Illuminate\Support\Facades\DB']);

arch('الـ Repository لا يعرف HTTP')
    ->expect('App\Repositories')
    ->not->toUse('App\Http');

arch('الـ Model لا يُستدعى من Controller')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Models');

arch('كنترولر API لا يرجّع View')
    ->expect('App\Http\Controllers\Api')
    ->not->toUse(['Illuminate\View', 'Illuminate\Contracts\View']);

arch('كنترولر Web لا يستخدم Resources')
    ->expect('App\Http\Controllers\Web')
    ->not->toUse('App\Http\Resources');

arch('Resources لا تُستخدم خارج API')
    ->expect('App\Http\Resources')
    ->toOnlyBeUsedIn('App\Http\Controllers\Api');

arch('الدومين لا يتفرّع حسب السطح')
    ->expect(['App\Services', 'App\Repositories', 'App\Data'])
    ->classes()->not->toHaveSuffix('ApiService')
    ->and(fn () => expect(glob(app_path('Services/{Api,Web}'), GLOB_BRACE))->toBeEmpty());

arch('كل Controller نحيف')
    ->expect('App\Http\Controllers')
    ->toHaveMethodsSmallerThan(15);   // سطر
```

| الاختبار | القاعدة المفروضة |
|---|---|
| `Service` ✗ `App\Http` | **الفصل الحقيقي** — الدومين لا يعرف من ناداه |
| `Service` ✗ `Illuminate\Database` | قاعدتك: لا استعلامات في Service |
| `Controller` ✗ `App\Models` | قاعدتك: لا منطق في Controller |
| `Api` ✗ `View` · `Web` ✗ `Resources` | الفصل بين الأسطح |
| لا مجلد `Services/Api` | **الخط الأحمر** |

> الاختبار الأول هو الأهم. طالما `App\Services` لا يستطيع استيراد `App\Http`، يصبح **مستحيلاً بنيوياً** أن يتفرّع منطق الأعمال حسب السطح — وهي المشكلة التي أنتجت الأسطح الثلاثة المتناقضة في البروتوتايب.

---

## 2. الحزم

### 2.1 Spatie

| الحزمة | الغرض في zeno | إلزامية |
|---|---|---|
| `laravel-permission` | أدوار الإدارة الثلاثة (N-2) + صلاحيات صاحب العمل | ✅ |
| `laravel-medialibrary` | الصورة الشخصية · شعار المنشأة · **مرفقات المحادثة** (D-34) · وثائق التوثيق | ✅ |
| `laravel-activitylog` | سجل التدقيق — **مطلب في N-3 و D-31** (كل فعل إداري بسبب مكتوب) | ✅ |
| `laravel-settings` | إعدادات N-1 المضبوطة من الأدمن (OTP، مدد الإعلان، سعر الاشتراك) — **مطابقة تامة للحاجة** | ✅ |
| `laravel-data` | الـ DTOs بين الطبقات | ✅ |
| `laravel-query-builder` | فلاتر البحث السبعة (D-23) من query params بأمان | ✅ |
| `laravel-sluggable` | روابط SEO للوظائف | ✅ |
| `laravel-translatable` | مسميات البيانات المرجعية القابلة للتعديل (N-1) وجاهزية الإنجليزية | 🟡 |
| `laravel-backup` | نسخ احتياطي للقاعدة والتخزين | ✅ |
| `laravel-responsecache` | كاش صفحات الموقع العام | ⚪ لاحقاً |

### 2.2 أساسية غير Spatie

| الحزمة | الغرض | ملاحظة |
|---|---|---|
| `filament/filament` v4 | لوحتان: `/admin` و `/employer` | multi-panel + tenancy |
| `bezhansalleh/filament-shield` | ربط Filament بـ spatie/permission | يولّد الصلاحيات آلياً |
| `laravel/sanctum` | توكنات API للموبايل | |
| `laravel/horizon` | إدارة الطوابير ومراقبتها | |
| `laravel/reverb` | WebSocket للمحادثات | بديل Pusher مجاني |
| `laravel/pennant` | Feature flags | يخفي ما لم يُطلق |
| `laravel/scout` + Meilisearch | **البحث العربي** | Meilisearch يتعامل مع العربية أفضل بكثير من Postgres FTS |
| `clickbar/laravel-magellan` | **PostGIS في Eloquent** | `whereDistanceSphere` · `orderByDistanceSphere` |
| `propaganistas/laravel-phone` | تطبيع الجوال السعودي إلى E.164 | يحل مشكلة الجوال في البروتوتايب |
| `laravel/pulse` | مراقبة الأداء والاستعلامات البطيئة | |
| `pxlrbt/filament-excel` | تصدير تقارير الإدارة | |

### 2.3 التطوير والجودة

`larastan/larastan` (تحليل ساكن، **level 6+**) · `laravel/pint` (تنسيق) · `pestphp/pest` (اختبار) · `barryvdh/laravel-ide-helper` · `nunomaduro/collision`

### 2.4 بلا حزمة — يُبنى داخلياً

| المكوّن | لماذا |
|---|---|
| `SmsGateway` + drivers | مزودو السعودية (Taqnyat / Unifonic / Msegat) بلا حزمة موحدة جيدة. واجهة + drivers = ملف واحد للتبديل |
| `PaymentGateway` + driver | Moyasar. نفس السبب |
| `ArabicNormalizer` | طي الألف والهمزة والتاء المربوطة وإزالة التشكيل — **يحل عطل البحث المؤكد** |
| `ArabicPluralizer` | يحل أعطال التعدد المؤكدة (D-35) |

> **قاعدة من `CLAUDE_TEMPLATE §16`، تُطبَّق على الباك إند أيضاً:** لا تُضاف حزمة بلا طرح السبب والموافقة. وكل SDK طرف ثالث يُغلَّف خلف واجهة مملوكة للمشروع.

---

## 3. الموبايل

المعمارية **معتمدة كما هي** في [`CLAUDE_TEMPLATE.md`](../CLAUDE_TEMPLATE.md): feature-first · Cubit · get_it · `Either<Failure,T>` · go_router · `ApiConsumer`.

### 3.1 نقاط الالتقاء مع الباك إند

| البند | الاتفاق |
|---|---|
| **أكواد الأخطاء** | `Failure` يحمل `code` من غلاف الاستجابة. **التفرّع على الكود لا على الرسالة** |
| **الغلاف الموحّد** | `handleRequest<T>` يفكّ `{success, message, data, error}` مرة واحدة مركزياً |
| **انتهاء الجلسة** | كود `SESSION_EXPIRED` → تسجيل خروج إجباري (تفاعل عام في `handleRequest` §6) |
| **التحديث الإجباري** | كود `FORCE_UPDATE` → شاشة تحديث |
| **الصيانة** | كود `MAINTENANCE_MODE` → شاشة صيانة |
| **الترقيم** | `meta.pagination` بشكل واحد → `PaginatedResponse<T>` واحد في `core/models/` |
| **الموقع** | يُرسل `lat`/`lng` في الطلب فقط — **ولا يُخزَّن محلياً ولا خادمياً** (D-27) |

### 3.2 ميزات تحتاج انتباهاً خاصاً

| الميزة | التحدي |
|---|---|
| **المحادثة** | Reverb عبر WebSocket + طابور محلي للرسائل المعلقة + إعادة إرسال. `sender_id` لا `me/them` |
| **مرفقات + موقع مقابلة** (D-18) | أنواع رسائل متعددة → `MessageType` enum مشترك بين الطرفين |
| **الخريطة** | `google_maps_flutter` + إذن الموقع + **مسار الرفض مصمَّم** (غير موجود في التصميم) |
| **الاشتراك** (D-01) | بوابة عند التقديم — حالة «منتهي» في `UserCubit` تحجب زر التقديم مع مسار تجديد |
| **RTL** | `EdgeInsetsDirectional` إجباري — التطبيق عربي بالكامل |

---

## 4. المونوريبو

```
zeno/
├── docs/                       # كل المواصفات
├── backend/                    # Laravel — API + Blade + Filament ×2
├── mobile/                     # Flutter
├── contracts/
│   └── openapi.yaml            # 🔑 مصدر الحقيقة للعقد
├── design/                     # البروتوتايب الأصلي (مرجع، لا يُبنى)
├── .github/workflows/
│   ├── backend.yml             # paths: backend/**, contracts/**
│   ├── mobile.yml              # paths:  mobile/**, contracts/**
│   └── contract.yml            # يتحقق أن الـ OpenAPI صالح ومطابق للراوتات
├── CLAUDE.md                   # قواعد الجذر
└── README.md
```

### 4.1 لماذا مونوريبو هنا تحديداً

**السبب الأول والأهم: العقد المولّد.**

`contracts/openapi.yaml` هو مصدر الحقيقة الوحيد للـ API. منه:
- يُولَّد **عميل Dart** (models + endpoints) → `mobile/lib/core/databases/api/generated/`
- تُولَّد **بيئة اختبار** للموبايل قبل جاهزية الباك إند
- تُتحقَّق **راوتات Laravel** مقابله في CI

> هذا يجعل انحراف السكيما — المشكلة رقم واحد في البروتوتايب — **يفشل في CI بدل أن يُكتشف بعد الإطلاق**.

الأسباب الأخرى: تغيير عابر للطبقات في PR واحد · مواصفات ملاصقة للكود · مراجعة واحدة.

### 4.2 قواعد التشغيل

| البند | القاعدة |
|---|---|
| **فروع CI** | مرشّحة بالمسار — تعديل Flutter لا يشغّل اختبارات PHP |
| **الفروع** | `feat/…` `fix/…` `refactor/…` `chore/…` من `develop` |
| **الكوميت** | `type(scope): summary` — والـ scope **يبدأ بالمنطقة**: `feat(api/jobs):` · `feat(mobile/auth):` · `feat(admin/reports):` · `chore(ci):` |
| **الإصدارات** | مستقلة: `backend-v1.3.0` · `mobile-v1.3.0+45` |
| **CODEOWNERS** | `/backend/` و `/mobile/` لهما مراجعون مختلفون |
| **الأسرار** | من CI فقط — لا `.env` ولا keystore في الريبو |
| **تغيير العقد** | تعديل `contracts/openapi.yaml` يستدعي مراجعة **الفريقين** إجبارياً |

### 4.3 قاعدة الفصل

> ممنوع أن يستورد `mobile/` أو `backend/` من الآخر مباشرة. اللقاء الوحيد المسموح هو `contracts/`.

---

## 5. البنية التحتية

| المكوّن | الاختيار | ملاحظة |
|---|---|---|
| قاعدة البيانات | PostgreSQL 16 + **PostGIS** | البحث النصف قطري جوهر المنتج |
| كاش وطوابير | Redis + Horizon | |
| بحث | Meilisearch | عربي أفضل بكثير |
| WebSocket | Laravel Reverb | |
| تخزين | S3-compatible | مع CloudFront/CDN |
| SMS | Taqnyat (خلف واجهة) | |
| دفع | Moyasar (خلف واجهة) | مدى + Apple Pay |
| خرائط | Google Maps | أفضل تغطية أحياء سعودية |
| Push | FCM | |
| نشر | Docker + CI | |
| مراقبة | Pulse + Sentry | |

### ⚠️ تنبيه لم يظهر في سجل القرارات

**D-01 أبقى منتجاً مدفوعاً** (اشتراك المرشح 5 ريال/سنة). لو كانت الشركة **مسجّلة في ضريبة القيمة المضافة**، فكل عملية بيع تستوجب **فاتورة ضريبية إلكترونية معتمدة من ZATCA** — حتى لو كانت 5 ريال.

استبعدت ZATCA من النطاق في D-28 على أساس أن باقات أصحاب العمل مؤجلة، لكن **D-01 يعيدها من الباب الخلفي**.

**ثلاثة مسارات:**
1. تأكيد أن الشركة غير مسجّلة في ضريبة القيمة المضافة (تحت حد التسجيل الإلزامي) → لا التزام حالياً
2. إدراج تكامل ZATCA في نطاق **سبرنت 8**
3. تأجيل تحصيل الاشتراك للإصدار الثاني وإطلاق المرشحين مجاناً

**يحتاج قراراً — الأثر على الجدول الزمني حقيقي.**

---

**التالي:** [`21-development-roadmap-and-sprints.md`](21-development-roadmap-and-sprints.md)
