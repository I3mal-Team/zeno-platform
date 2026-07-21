# سير العمل — Git Flow

## الفروع الدائمة

| الفرع | الدور | من يدمج فيه |
|---|---|---|
| `main` | **الإنتاج** — كل commit عليه قابل للنشر | `release/*` و `hotfix/*` فقط |
| `develop` | **التكامل** — أحدث ما اكتمل | `feature/*` |

> **ممنوع الدفع المباشر إلى `main` أو `develop`.** كل شيء عبر Pull Request.

## الفروع المؤقتة

| النوع | يتفرّع من | يُدمج في | مثال |
|---|---|---|---|
| `feature/*` | `develop` | `develop` | `feature/otp-authentication` |
| `release/*` | `develop` | `main` + `develop` | `release/1.0.0` |
| `hotfix/*` | `main` | `main` + `develop` | `hotfix/otp-rate-limit` |

الأنواع الأخرى المسموحة على مسار الميزة: `fix/` · `refactor/` · `chore/` · `docs/` · `test/`.

---

## دورة الميزة

```bash
git checkout develop && git pull
git checkout -b feature/otp-authentication

# … العمل …

make check          # pint + phpstan + pest — لا ترفع قبل أن يمر
git push -u origin feature/otp-authentication
# افتح PR إلى develop
```

**بعد المراجعة:** يُدمج بـ **squash** إلى `develop`، ويُحذف الفرع.

## دورة الإصدار

```bash
git checkout -b release/1.0.0 develop
# تثبيت الأرقام، إصلاحات نهائية فقط — لا ميزات جديدة
# PR → main، ثم:
git tag backend-v1.0.0
git tag mobile-v1.0.0+1
# ثم دمج main رجوعاً إلى develop
```

## دورة الإصلاح العاجل

```bash
git checkout -b hotfix/otp-rate-limit main
# الإصلاح
# PR → main، ثم دمج main رجوعاً إلى develop
```

> **`hotfix` هو الاستثناء الوحيد الذي يتفرّع من `main`.** أي شيء آخر يبدأ من `develop`.

---

## رسائل الـ Commit

`type(scope): imperative summary` — والـ scope يبدأ بالمنطقة.

| النوع | متى |
|---|---|
| `feat` | ميزة جديدة |
| `fix` | إصلاح خلل |
| `refactor` | إعادة هيكلة بلا تغيير سلوك |
| `chore` | بنية تحتية وأدوات |
| `docs` | توثيق |
| `test` | اختبارات |

```
feat(api/jobs): add nearby search with radius filter
fix(mobile/auth): stop otp resend timer leaking after dispose
chore(ci): run architecture tests on pull requests
```

**النطاقات:** `api/*` · `site/*` · `admin/*` · `employer/*` · `mobile/*` · `db` · `ci` · `docs`

- عنوان واحد بصيغة الأمر، بلا نقطة في آخره.
- المتن يشرح **لماذا** لا **ماذا** — الـ diff يوضّح «ماذا».
- تغيير منطقي واحد لكل commit.

---

## متطلبات الـ Pull Request

- [ ] وصف التغيير وسببه
- [ ] لقطات شاشة أو تسجيل لأي تغيير في الواجهة
- [ ] `make check` أخضر (باك إند)
- [ ] `make m-analyze` و `make m-test` أخضران (موبايل)
- [ ] **اختبارات المعمارية تمر** — تحجب الدمج
- [ ] لا كود معلّق ولا سجلات تصحيح متروكة
- [ ] تحديث `docs/` في نفس الـ PR إن تغيّرت المعمارية

**تعديل `contracts/openapi.yaml` يستدعي مراجعة الفريقين — الباك إند والموبايل.**

---

## قواعد ملزمة

- **لا رفع بالقوة على فرع مشترك** (`main` · `develop`).
- **لا أسرار في الريبو** — `.env` ومفاتيح التوقيع و `google-services.json` تأتي من أسرار الـ CI.
- أعِد ترتيب فرعك على `develop` قبل فتح الـ PR، وحُلّ التعارضات محلياً.
- الفرع الذي يتجاوز أسبوعاً يُعاد ترتيبه على `develop` دورياً.
