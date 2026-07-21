# zeno — منصة الوظائف التشغيلية

منصة توظيف سعودية قائمة على القرب الجغرافي، للوظائف التشغيلية والخدمية والموسمية —
مطاعم، نظافة، نقل، فعاليات، مبيعات، أمن، صيانة، وحرف.

تربط المرشحين بأصحاب العمل القريبين منهم، وتتيح التقديم والتواصل بأقل عدد من الخطوات.

---

## المحتويات

| المجلد | الوصف |
|---|---|
| `backend/` | Laravel 13 — API للموبايل · موقع Blade · لوحتا Filament |
| `mobile/` | تطبيق Flutter (مرشح + صاحب عمل) |
| `contracts/` | `openapi.yaml` — **مصدر الحقيقة للعقد بين الأسطح** |
| `docs/` | المواصفات الهندسية والمنتجية |
| `design/` | البروتوتايب التفاعلي الأصلي + `HANDOVER.md` (**مرجع — لا يُبنى**) |
| `docker/` | تعريفات الحاويات |

---

## البدء

**المتطلبات:** Docker · FVM (لتطبيق فلاتر)

```bash
cp backend/.env.example backend/.env     # ثم اضبط القيم
make up                                  # تشغيل كل الخدمات
make art c="key:generate"
make migrate
```

| الخدمة | العنوان |
|---|---|
| التطبيق | http://localhost:8000 |
| لوحة الإدارة | http://localhost:8000/admin |
| داشبورد صاحب العمل | http://localhost:8000/employer |
| Meilisearch | http://localhost:57700 |
| Mailpit | http://localhost:58025 |
| PostgreSQL | `localhost:55432` |
| Redis | `localhost:56379` |

> البورتات في نطاق `5xxxx` عمداً — تجنّباً للتعارض مع مشاريع أخرى على نفس الجهاز.

`make help` يعرض كل الأوامر.

---

## المعمارية

### الباك إند — أربع قواعد ملزمة

```
Route → FormRequest → Controller → Service → Repository → Model
                          ↓
                    Resource → ApiResponse trait
```

1. **لا منطق أعمال في الـ Controller**
2. **لا استعلامات في الـ Service**
3. **التحقق في الـ Request**
4. **الاستجابة عبر Resource + trait موحّد**

هذه القواعد **مفروضة آلياً** عبر `tests/Architecture/` وتحجب الدمج — لا تعتمد على المراجعة البشرية.

### فصل الأسطح

كل ما هو **فوق** الـ Service منفصل لكل سطح. وكل ما هو **من** الـ Service فنازل مشترك ولا يُكرَّر.

```
منفصل:  Routes · Controllers · Requests · Responses · Middleware · Guards · Tests
        ├─ Api/V1   ├─ Web   ├─ Filament/Employer   ├─ Filament/Admin
                              ↓
مشترك:  Services · Repositories · Models · DTOs · Enums · Events · Policies
```

> ⛔ **ممنوع** `app/Services/Api/` أو `app/Services/Web/`. تفريع الدومين حسب السطح هو ما أنتج
> ثلاث سكيمات متناقضة في البروتوتايب.

### الموبايل

feature-first · Cubit · get_it · `Either<Failure,T>` · go_router · `ApiConsumer`.
القواعد الكاملة في [`CLAUDE_TEMPLATE.md`](CLAUDE_TEMPLATE.md).

---

## الوثائق

| المستند | المحتوى |
|---|---|
| [`docs/10-domain-model-and-database-schema.md`](docs/10-domain-model-and-database-schema.md) | ~40 جدول بالقيود والفهارس والاحتفاظ وتصنيف PDPL · ERD · مخططات الحالة |
| [`docs/18-architecture-and-stack.md`](docs/18-architecture-and-stack.md) | المعمارية · الحزم · المونوريبو |
| [`docs/21-development-roadmap-and-sprints.md`](docs/21-development-roadmap-and-sprints.md) | 11 سبرنت بمعايير خروج واعتماديات |
| [`docs/22-open-decisions-and-product-questions.md`](docs/22-open-decisions-and-product-questions.md) | 45 قرار — 23 محسوم · الباقي مفتوح |
| [`design/HANDOVER.md`](design/HANDOVER.md) | تسليم التصميم — التوكنز والشاشات (مرجع UI) |

### ترتيب حجّية المصادر

1. **`docs/source/project-description.pdf`** — وصف العميل · **الحاكم في قواعد العمل**
2. **`design/HANDOVER.md`** — **الحاكم في الـ UI والتوكنز**
3. **`design/*.dc.html`** — البروتوتايب · بيانات وهمية · **ليس كوداً إنتاجياً**

عند التعارض: الأول يكسب في قواعد العمل، والثاني في التصميم. والصمت ليس تعارضاً.

---

## سير العمل

- الفرع الأساسي `main` · التطوير من `develop`
- الفروع: `feat/…` `fix/…` `refactor/…` `chore/…` `docs/…` `test/…`
- الكوميت: `type(scope): summary` — والـ scope يبدأ بالمنطقة
  `feat(api/jobs):` · `feat(mobile/auth):` · `feat(admin/reports):` · `chore(ci):`
- الإصدارات مستقلة: `backend-v1.0.0` · `mobile-v1.0.0+1`
- **تعديل `contracts/openapi.yaml` يستدعي مراجعة الفريقين**

```bash
make check      # pint + phpstan + pest — نفس ما يشغّله CI
make m-analyze  # تحليل فلاتر
```

---

## التراخيص والخصوصية

مستودع خاص. يحتوي على وصف مشروع ومواد تصميم مملوكة للعميل.
ممنوع مشاركة محتوياته خارج الفريق.

**ممنوع رفع:** `.env` · مفاتيح التوقيع · `google-services.json` · حسابات الخدمة.
كلها في `.gitignore` وتأتي من أسرار الـ CI.
