# خطة رفع التطبيق على Google Play و App Store

مبنية على فحص فعلي للمشروع (`mobile/`) بتاريخ 2026-08-15، فرع `develop`.

الوضع الحالي باختصار:

| | القيمة |
|---|---|
| Application ID / Bundle ID | `sa.zeno.app` (متطابق على المنصتين ✅) |
| Apple Team ID | `924G2SZQ3J` (موجود في `project.pbxproj`) |
| الإصدار | `1.0.0+1` |
| Flutter | `3.32.8` (مثبّت في `.fvmrc` و CI) |
| minSdk / targetSdk / compileSdk | 23 / 35 / 36 |
| iOS Deployment Target | 13.0 |
| اسم العرض | `Zeno` |
| نوع حساب Play | مؤسسي (Organization) |

---

## القسم صفر — بلوكرز لازم تتحل قبل أي رفع

دي الحاجات اللي لو رفعت من غيرها التطبيق **هيترفض أو مش هيشتغل أصلًا**. مرتّبة بالأولوية.

**الحالة:** 1، 3، 4، 6، 7، 9 + حساب المراجعة اتعملوا ✅ — 2 اتوصّل ناقصه الـ keystore — 5، 8 لسه مطلوبين منك.

### 1. ✅ التطبيق بيشاور على سيرفر التطوير في الـ release — *اتحل*

`mobile/lib/core/databases/api/end_points.dart:123`

```dart
static AppEnvironment current = AppEnvironment.dev;
```

مفيش أي حتة في `main.dart` بتغيّر القيمة دي. يعني أي build ريليز دلوقتي هيروح على `http://10.0.2.2:8000/api/v1` — التطبيق مش هيشتغل خالص عند المستخدم، وبردو Apple هترفضه تحت 2.1 (App Completeness).

كمان مترتّب على نفس المشكلة: زرار الـ OTP autofill بتاع `4829` في `otp_view.dart:89` مشروط بـ `AppEnvironment.dev` — يعني هيبان للمستخدمين.

**اللي اتعمل:** `main.dart` بقى بيقرا `APP_ENV` من `--dart-define` ويحدد الـ environment قبل `setupServiceLocator()`.

⚠️ **نتيجة مترتبة على ده:** أي بيلد ستور لازم يمرّر `--dart-define=APP_ENV=production`. لو نسيتها، التطبيق يرجع لـ dev تاني. الأمر موجود في القسم الأول خطوة 3 وفي `codemagic.yaml` بالفعل.

### 2. 🟠 الأندرويد بيوقّع بمفاتيح الـ debug — *الكود اتظبط، ناقص الـ keystore*

`build.gradle.kts` بقى بيقرا `android/key.properties` ويعمل `signingConfigs.release` منه. لو الملف مش موجود بيرجع لتوقيع debug عشان `flutter run --release` يفضل شغّال محليًا — و Play بترفض أي AAB متوقّع بـ debug فمستحيل ينشر بالغلط.

**الباقي عليك:** تولّد الـ keystore وتعمل `key.properties` — الخطوات في القسم الأول خطوة 2.

### 3. ✅ مفيش صلاحيات iOS للصور — كراش مضمون — *اتحل*

الكود بيستخدم `FilePicker.platform.pickFiles(type: FileType.image)` في 4 شاشات على الأقل:
- `profile_view.dart:49` و `:65`
- `register_candidate_view.dart:48`
- `register_employer_view.dart:48`
- `employer_account_view.dart:44`

و `Info.plist` كان **مفيهوش** `NSPhotoLibraryUsageDescription`. على iOS ده مش error — ده كراش فوري من الـ OS أول ما المستخدم يضغط "اختر صورة".

**اللي اتعمل:** المفتاح اتضاف بنص عربي مناسب.

### 4. ✅ مفيش حذف حساب (Account Deletion) — *اتحل*

- **Apple** — App Store Review Guideline 5.1.1(v): أي تطبيق بيسمح بإنشاء حساب لازم يسمح ببدء حذفه **من داخل التطبيق**.
- **Google Play** — لازم مسار داخل التطبيق **+** رابط ويب للحذف في نموذج Data Safety.

**اللي اتعمل — الباك إند:**
- `DELETE /api/v1/auth/account` (محمي بـ `auth:sanctum`)
- `AuthService::deleteAccount()` جوّه transaction: يمسح ملف المرشّح (اللي فيه رقم الهوية وتاريخ الميلاد)، يمسح كل device tokens (فتقف الإشعارات)، يلغي كل الـ tokens، ويعمل soft delete للمستخدم مع `status = deleted`

**ليه soft delete مش حذف كامل؟** قيدين في الـ schema بيفرضوا ده:
- `jobs.created_by_user_id` و `organization_verification_requests.submitted_by_user_id` معمولين `restrictOnDelete` — يعني صاحب عمل نشر إعلان **مستحيل** يتحذف حذف كامل، الـ DB هترفض
- الـ unique index على `phone_e164` **partial** على `deleted_at IS NULL` — فالـ soft delete لوحده بيحرّر الرقم للتسجيل من جديد فورًا

الـ schema كان متصمّم للسيناريو ده أصلًا (`UserStatus::Deleted` موجود من الأول).

**اللي اتعمل — الموبايل:**
- `showDeleteAccountSheet` + `DeleteAccountButton` في `core/components/delete_account_sheet.dart` — مشتركين بين شاشة المرشّح وشاشة صاحب العمل
- الـ sheet بيوضّح اللي هيتحذف، `isDismissible: false` عشان مفيش حذف بالغلط، وبيقول إن الرقم يقدر يسجّل من جديد
- فشل الحذف **مش** بيمسح التوكن محليًا — الحساب لسه موجود، فالمستخدم يفضل داخل ويعيد المحاولة بدل ما يتقذف على شاشة الدخول

**اللي اتعمل — صفحة الويب:** `GET /delete-account` عامة (بتفتح لأي زائر — ده اللي جوجل بتطلبه: الطلب يبقى ممكن من غير تثبيت التطبيق)، و `DELETE /delete-account` محمية بـ `auth` وبتنادي **نفس** `AuthService::deleteAccount()` اللي التطبيق بينادها، فمستحيل السطحين يختلفوا في معنى «احذف حسابي». الصفحة مربوطة من الفوتر في كل صفحات الموقع.

الرابط اللي تحطه في نموذج Data Safety:

```
https://zeno.sa/delete-account
```

### 5. ✅ سياسة الخصوصية — *اتحلت، فاضل توقيع المحامي*

**الروابط اللي تقدّمها للستورين:**

```
https://zeno.sa/privacy
https://zeno.sa/delete-account
```

**اللي اتعمل:** الخصوصية اتفصلت في صفحة مستقلة `/privacy` (`PrivacyViewModel` + `PrivacyController`) بدل ما تكون قسم جوّه الشروط — المنصتين بيطلبوا رابط يكون سياسة الخصوصية وبس. و`/terms` اتوسّعت من 6 بنود لـ 13.

السياسة **مبنية على الكود مش على قالب**: 12 نوع بيانات محدّدة بأسمائها، الأطراف الثالثة متسمّية (مزوّد SMS، Firebase، واتساب، OpenStreetMap)، مدد الاحتفاظ، وحقوق المستخدم تحت نظام حماية البيانات الشخصية. كل ادعاء فيه تعليق في الكود بيشاور على السلوك اللي بيثبته.

**الباقي عليك:** ⚠️ توقيع محامي. الملفين لسه مكتوب في أول كل واحد إنها **ليست استشارة قانونية** ومحتاجة مراجعة — تحديدًا بنود المسؤولية والاختصاص القضائي، وبيانات تسجيل جهة التحكّم، وأساس نقل البيانات خارج المملكة.

### 5-ج. باجّين اتكشفوا أثناء كتابة السياسة

كتابة سياسة دقيقة تعني مطابقة كل ادعاء بالكود. المطابقة دي كشفت خللين حقيقيين:

**1. 🔴 حذف الحساب كان بيسيب السيرة الذاتية والصورة على الديسك**

`CandidateProfile` و `Application` الاتنين بيستخدموا `InteractsWithMedia`، ومكتبة الميديا بتمسح الملفات في حدث `deleting` بتاع الموديل. الكود كان بيعمل mass delete عبر الـ query builder — وده **مش بيطلق أحداث الموديل إطلاقًا**. النتيجة: الصف بيتمسح والملفات بتفضل في التخزين.

يعني وعد التطبيق والموقع بحذف «مرفقاتك» كان **غير صحيح**. اتصلّح بتحميل الموديلات وحذفها واحد واحد، وفيه اختبار بيمسك الرجعة (`Storage::fake` + عدّ صفوف `media`).

**2. 🟠 رموز الدخول كانت بتتراكم للأبد**

`otp_challenges` بيخزّن الجوال والـ IP والـ user agent مع كل طلب رمز. `OtpChallengeRepository::deleteExpiredBefore()` كانت موجودة في الكود **بس محدش بينادينها** — ومفيش أي جدولة في المشروع أصلًا.

يعني ماكانش ينفع أكتب مدة احتفاظ في السياسة لأنها ماكانتش هتكون صحيحة. اتعمل أمر `otp:purge` مجدول يوميًا 3:30 صباحًا (نافذة 30 يوم قابلة للتعديل)، وبعدين اتكتب البند في السياسة.

✅ **النص في `phone_view.dart` بقى لينكات فعلية** — `_LegalNotice` بيعرض «الشروط والأحكام» → `/terms` و«سياسة الخصوصية» → `/privacy`، والروابط في `EndPoints.siteUrl` بتتغير مع الـ environment زي الـ API.

### 5-ب. ✅ الموقع كان مكتوب فيه «AMS» مش «Zeno» — *اتحل*

آبل وجوجل بيفتحوا رابط سياسة الخصوصية ويقارنوه بالتطبيق، فسياسة خصوصية باسم منتج تاني سؤال متوقّع في المراجعة. وكان أخطر من كده: النص القانوني كان بيقول «باستخدامك تطبيق ومنصة AMS، فإنك توافق…» — اتفاق بيربط المستخدم بمنتج باسم مش موجود.

**اللي اتعمل:** كل الـ **22 موضع** اتغيّروا لـ `Zeno` — الشروط والخصوصية، الفوتر، الهيدر، `<title>` بتاع الموقع ولوحة صاحب العمل، صفحة «من نحن»، الرئيسية، صفحة الشركات، لوحة الإدارة، والـ `alt` بتاع اللوجو. واختبار `PublicPagesTest` كان بيتحقق من النص القديم واتحدّث معاهم.

كمان الـ `<title>` الافتراضي كان بيطلع `AMS — AMS` (يعني هيبقى `Zeno — Zeno`) — اتغيّر لـ `وظائف قريبة منك — Zeno`. ده اللي المراجع بيشوفه في تاب المتصفح وهو فاتح صفحة السياسة.

### 6. ✅ مخلّفات development في Info.plist — *اتحل*

كان فيه `NSAllowsLocalNetworking` واستثناء HTTP لـ IP معيّن `192.168.1.145` و `NSLocalNetworkUsageDescription`. الـ production بيستخدم HTTPS، فمفيش داعي ليها، ووجودها بيخلّي Apple تسأل عنها.

**اللي اتعمل:** بلوك `NSAppTransportSecurity` و `NSLocalNetworkUsageDescription` اتمسحوا بالكامل، واتضاف `ITSAppUsesNonExemptEncryption = false` (بيوفّر سؤال امتثال التصدير مع كل رفعة).

⚠️ **نتيجة مترتبة:** التطوير المحلي على iOS عبر HTTP عادي (`http://<LAN-IP>:8000`) مش هيشتغل دلوقتي — ATS هتحجبه. لو احتجته، أنضف حل إنك تضيف `NSAllowsLocalNetworking` في xcconfig خاص بالـ Debug بس، مش في `Info.plist` المشترك. (مش مؤثر حاليًا طالما التطوير على ويندوز والـ iOS بيتبني على Codemagic.)

### 7. ✅ التطبيق كان Universal (آيفون + آيباد) — *اتحل*

`project.pbxproj` كان فيه `TARGETED_DEVICE_FAMILY = "1,2"` في التلات configs، يعني App Store Connect هتطلب **سكرين شوتس آيباد 13"** إجباري والمراجع هيفتح التطبيق على آيباد.

**اللي اتعمل:** بقت `TARGETED_DEVICE_FAMILY = 1` (آيفون بس) في الـ 3 configs. توفّر سكرين شوتس ومخاطر مراجعة، وتقدر تضيف الآيباد في تحديث لاحق.

### 8. 🟠 ملفات Firebase ناقصة — الإشعارات مش شغّالة — *مطلوب منك*

مفيش `android/app/google-services.json` ولا `ios/Runner/GoogleService-Info.plist`.

`PushNotifications.init()` معمول guarded بالكامل (`push_notifications.dart`) فالتطبيق **مش هيكراش** — بس الإشعارات ببساطة مش موجودة. لو الإشعارات جزء من قيمة المنتج لازم تتظبط قبل الإطلاق. التفاصيل في القسم الثالث.

### 9. ✅ ملف MainActivity قديم مكرر — *اتحل*

`android/app/src/main/kotlin/sa/zeno/zeno/MainActivity.kt` كان بـ package `sa.zeno.zeno` ومش مستخدم (الـ manifest بيشاور على `.MainActivity` اللي بتترجم لـ `sa.zeno.app.MainActivity`). ملف ميت من إعادة تسمية قديمة — **اتمسح**.

### حاجات تانية

- ✅ **توحيد الاسم على `Zeno`** — اتغيّر في `AndroidManifest.xml` (`android:label`)، `Info.plist` (`CFBundleName` + `CFBundleDisplayName`)، `main.dart` (`MaterialApp.title`)، `app_ar.arb` (`appName`)، `splash_view.dart`، و User-Agent في `map_picker_screen.dart`.
- ✅ **`targetSdk` اتثبّت على 35** صراحة في `build.gradle.kts` بدل ما يتوارث من `flutter.targetSdkVersion` — عشان ترقية الـ SDK ما تحركهاش من ورا ظهرك. راجع المطلوب من Play Console وقت الرفع لو اتغيّر.
- **بلاطات OpenStreetMap** — `map_picker_screen.dart:125` بيستخدم `tile.openstreetmap.org` مباشرة. الـ `userAgentPackageName` متظبط ✅، بس سياسة استخدام بلاطات OSM المجانية مش مسموح بيها للتطبيقات التجارية ذات الاستخدام الكثيف. مش بلوكر للستور، بس مخاطرة تشغيلية — فكّر في MapTiler أو Mapbox.
- **اختبار build الريليز** — R8/minification شغّال افتراضيًا في release ومفيش `proguard-rules.pro`. اختبر AAB ريليز حقيقي على جهاز فعلي قبل الرفع، مش debug بس.

---

## القسم الأول — Google Play

### خطوة 1: ✅ الحساب — *اتعمل*

حساب Play Console مؤسسي جاهز.

✅ لأن الحساب مؤسسي، شرط الـ **12 مختبِر × 14 يوم** closed testing (المفروض على الحسابات الشخصية قبل الوصول للـ production) **مش مطبّق عليك**. تقدر تروح production مباشرة بعد الاختبار الداخلي.

**الخطوة اللي بعدها في الكونسول:** اعمل التطبيق (**Create app**) بالاسم `Zeno`، اللغة الأساسية **العربية**، ونوعه **App** مش Game، ومجاني. وفعّل **Play App Signing** وقت الإنشاء.

### خطوة 2: الـ Keystore والتوقيع

```bash
keytool -genkey -v -keystore zeno-upload.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```

⚠️ الملف ده لو ضاع مش هتقدر تحدّث التطبيق أبدًا. خد منه نسخة احتياطية في مكان آمن (ومنفصل عن الجهاز).

اعمل `mobile/android/key.properties` (متجاهَل في `.gitignore` بالفعل ✅ — سطر `key.properties` موجود):

```properties
storePassword=<الباسورد>
keyPassword=<الباسورد>
keyAlias=upload
storeFile=../zeno-upload.jks
```

✅ `build.gradle.kts` **متظبط بالفعل** — بيقرا الملف ده أوتوماتيك ويبني منه `signingConfigs.release`. مش محتاج تعدّل أي gradle.

للتأكيد إن التوقيع اشتغل صح بعد ما تعمل الملف:

```bash
cd mobile && flutter build apk --release --dart-define=APP_ENV=production && keytool -printcert -jarfile build/app/outputs/flutter-apk/app-release.apk
```

لازم تشوف الـ alias بتاعك `upload` مش `androiddebugkey`.

فعّل **Play App Signing** وقت إنشاء التطبيق في الكونسول (جوجل تمسك مفتاح التوقيع النهائي، وأنت تمسك مفتاح الرفع بس — لو ضاع منك تقدر تطلب استبداله).

### خطوة 3: بناء الـ AAB

```bash
cd mobile && flutter build appbundle --release --dart-define=APP_ENV=production
```

المخرج: `mobile/build/app/outputs/bundle/release/app-release.aab`

(جوجل ما بتقبلش APK للتطبيقات الجديدة — AAB بس.)

### خطوة 4: أصول المتجر

| العنصر | المواصفة |
|---|---|
| أيقونة التطبيق | 512×512 PNG، من غير شفافية |
| صورة الغلاف (Feature graphic) | 1024×500 PNG/JPG |
| سكرين شوتس فون | 2 على الأقل، عرض 1080px+ (المفضّل 4–8) |
| سكرين شوتس تابلت | اختياري لو مش مستهدف تابلت |
| الوصف المختصر | ≤ 80 حرف |
| الوصف الكامل | ≤ 4000 حرف |

لغة الستور الأساسية: **العربية** (التطبيق مقفول على `Locale('ar')` و RTL في `main.dart`).

### خطوة 5: نماذج الكونسول الإجبارية

كل واحد فيهم لازم يتملى قبل ما زرار النشر يشتغل:

1. **Privacy policy URL** — من بلوكر رقم 5
2. **App access** — التطبيق ورا OTP، فلازم تدّي جوجل حساب تجريبي. اكتب رقم + OTP ثابت (ارجع لـ "حساب المراجعة" تحت)
3. **Ads** — مفيش إعلانات في المشروع → "No"
4. **Content rating** — استبيان IARC. تطبيق توظيف عادي → غالبًا 3+ / Everyone
5. **Target audience** — 18+ (منصة توظيف)
6. **Data safety** — دي أهم واحدة. من فحص الكود لازم تعلن:
   - **رقم الموبايل** (OTP) — يُجمع، مرتبط بالهوية
   - **الموقع التقريبي والدقيق** (`geolocator` + `ACCESS_FINE_LOCATION` في الـ manifest) — للوظائف القريبة
   - **الملفات/المستندات** (السيرة الذاتية عبر `file_picker`)
   - **الصور** (صورة شخصية / شعار)
   - **معرّفات الجهاز** (`device_info_plus` + توكن FCM)
   - **الرسائل داخل التطبيق** (فيتشر الشات)
   - **الاسم والبريد** لو بيتجمعوا في التسجيل
   - + رابط حذف الحساب
7. **Government apps / Financial features / Health** — كلها "No"
8. **News app** — "No"

### خطوة 6: مسار الإصدار

```
Internal testing  →  Production
    (فوري)          (مباشرة — حساب مؤسسي)
```

الحساب المؤسسي مش ملزم بمسار الـ closed testing الإجباري، بس **عدّي على Internal testing الأول** — أسرع طريقة تكتشف بيها مشاكل التوقيع والـ `APP_ENV` قبل ما توصل لمراجعة حقيقية.

مراجعة أول إصدار عادةً **من أيام لأسبوعين**. التحديثات بعد كده أسرع بكتير.

---

## القسم الثاني — Apple App Store عبر Codemagic

### خطوة 1: ✅ Apple Developer Program — *اتعمل*

⚠️ **تأكّد من حاجة واحدة قبل ما تكمّل:** المشروع فيه `DEVELOPMENT_TEAM = 924G2SZQ3J` مكتوب في [project.pbxproj](../mobile/ios/Runner.xcodeproj/project.pbxproj). لازم يطابق الـ Team ID بتاع الحساب الجديد — تلاقيه في [Membership details](https://developer.apple.com/account). لو مختلف، غيّره في الملف (3 مواضع) وإلا التوقيع هيفشل على Codemagic.

```bash
grep -n "DEVELOPMENT_TEAM" mobile/ios/Runner.xcodeproj/project.pbxproj
```

### خطوة 2: تسجيل الـ App ID و إنشاء التطبيق

في [App Store Connect](https://appstoreconnect.apple.com):

1. **Certificates, Identifiers & Profiles → Identifiers** → App ID جديد لـ `sa.zeno.app`
   - فعّل **Push Notifications** (محتاجها لـ FCM)
2. **My Apps → +** → تطبيق جديد
   - Platform: iOS، Bundle ID: `sa.zeno.app`، Primary Language: **Arabic**، SKU: `zeno-ios`

### خطوة 3: مفتاح App Store Connect API (ده اللي Codemagic هيستخدمه)

**Users and Access → Integrations → App Store Connect API → +**

- Access: **App Manager**
- حمّل ملف `AuthKey_XXXXXXXX.p8` — **بيتحمّل مرة واحدة بس**
- سجّل: **Issuer ID** + **Key ID**

### خطوة 4: إعداد Codemagic

1. اربط الريبو في [codemagic.io](https://codemagic.io)
2. **Teams → Integrations → Developer Portal → Add key** — ارفع الـ `.p8` مع Issuer ID و Key ID
3. **Code signing identities → iOS certificates** — سيب Codemagic تولّد وتدير الشهادات وبروفايلات التوزيع أوتوماتيك (automatic signing). ده أسهل بكتير من الرفع اليدوي.
4. أضف متغيرات البيئة في مجموعة اسمها `appstore`:
   - `APP_STORE_APPLE_ID` — الرقم الرقمي للتطبيق من App Store Connect (مش الـ bundle id). تلاقيه في App Information → General → Apple ID.

⚠️ **مهم:** المشروع مش في جذر الريبو — هو في `mobile/`. عشان كده الـ workflow مظبوط على `working_directory: mobile`.

### خطوة 5: ملف codemagic.yaml

✅ **الملف موجود بالفعل** في [`codemagic.yaml`](../codemagic.yaml) بجذر الريبو. ملخص اللي فيه:

- `working_directory: mobile` — لأن مشروع الفلاتر في مجلد فرعي
- `flutter: '3.32.8'` — مطابق لـ `.fvmrc` و GitHub Actions
- `ios_signing` بـ `distribution_type: app_store` و `bundle_identifier: sa.zeno.app` — Codemagic تجيب/تولّد بروفايل التوزيع لوحدها، و `xcode-project use-profiles` بيربطه بالمشروع. **مش محتاج تفتح Xcode خالص.**
- رقم البيلد بيتحسب أوتوماتيك من آخر بيلد على TestFlight (+1)، وبيرجع لـ 1 في أول رفعة
- البناء بيمرّر `--dart-define=APP_ENV=production` — دي الحتة اللي بتمنع بلوكر رقم 1
- `submit_to_testflight: true` بس `submit_to_app_store: false` — عشان تراجع البيلد قبل ما يروح لمراجعة آبل
- التشغيل مربوط بـ **tag `v*`** بس، فمفيش رفع بالغلط من `git push` عادي

**حاجتين لازم تعملهم في واجهة Codemagic قبل أول تشغيل:**
1. سمّي مفتاح App Store Connect `zeno_app_store` (أو غيّر الاسم في `integrations` بالملف)
2. اعمل مجموعة متغيرات اسمها `appstore` فيها `APP_STORE_APPLE_ID`

عشان تطلق بيلد:

```bash
git tag v1.0.0 && git push origin v1.0.0
```


### خطوة 6: أصول متجر آبل

| العنصر | المواصفة |
|---|---|
| أيقونة | 1024×1024 PNG، **من غير شفافية ولا زوايا مدوّرة** |
| سكرين شوتس آيفون 6.9" | 3–10 لقطات (1320×2868) — إجباري |
| سكرين شوتس آيباد | ✅ **مش مطلوبة** — التطبيق بقى آيفون بس (بلوكر 7) |
| الاسم | ≤ 30 حرف |
| العنوان الفرعي | ≤ 30 حرف |
| الكلمات المفتاحية | ≤ 100 حرف، مفصولة بفواصل |
| الوصف | ≤ 4000 حرف |
| رابط الدعم | إجباري |

### خطوة 7: ✅ امتثال التصدير — *اتعمل*

`ITSAppUsesNonExemptEncryption = false` اتضاف في `Info.plist`، فآبل مش هتسألك عن التشفير مع كل رفعة. (صحيح طالما التطبيق بيستخدم HTTPS القياسي بس — وده وضعه الحالي.)

### خطوة 8: ✅ حساب المراجعة — *الكود اتعمل، ناقص إعداد الـ env*

**التطبيق بيسجّل دخول بـ OTP على SMS. مراجع آبل مش هيقدر يستقبل SMS.** ده سبب رفض شبه مؤكّد ومتكرر جدًا.

**اللي اتعمل:** `OtpService` بقى فيه allow-list لرقم **واحد** بيتصدرله كود ثابت ومفيش SMS بيتبعت له، وبيتخطّى الـ cooldown والـ rate limit كمان (مراجع بياخد 429 وسط تسجيل الدخول بيكتب إن التطبيق مكسور).

⚠️ **ده مختلف تمامًا عن `OTP_GENERATOR=fixed` الموجود من قبل** — ده بيخلّي **كل** الأرقام تقبل كود ثابت، وكارثة أمنية في production. الحل الجديد بيمسّ رقم واحد بس وكل حساب حقيقي يفضل بكود عشوائي.

**الباقي عليك** — في `.env` بتاع production قبل الرفع:

```
AUTH_REVIEW_PHONE=+966500000000
AUTH_REVIEW_OTP=4829
```

قواعد لازم تتبعها:
- الرقم لازم يبقى **بصيغة E.164** ومطابق للتخزين (`+9665XXXXXXXX`) — لو كتبته `0500000000` مش هيطابق
- **لا تستخدم رقم بيخص شخص حقيقي** — أي حد يعرف الرقم والكود يدخل على الحساب ده
- سيبهم فاضيين لتعطيل الخاصية بالكامل

وبعدها اكتب في **App Review Information → Notes** بوضوح:

```
Demo account (phone OTP):
Phone: +966500000000
OTP code: 4829
The OTP is fixed for this test account; no SMS is sent.
Log in as candidate, then browse "Nearby Jobs" and apply.
Account deletion: Account tab > "حذف الحساب" (bottom of the screen).
```

وكمان: **`https://api.zeno.sa` لازم يكون شغّال وعليه بيانات حقيقية وقت المراجعة.** المراجع بيفتح التطبيق فعليًا — تطبيق بيوصل لشاشة فاضية أو error بيترفض تحت 2.1.

### خطوة 9: خصوصية آبل (Privacy Nutrition Labels)

نفس المصفوفة اللي في Data Safety بتاعة جوجل — رقم الموبايل، الموقع، الملفات، الصور، المعرّفات، الرسائل.

كمان لازم ترفع **Privacy Manifest** (`PrivacyInfo.xcprivacy`) للتطبيق. أغلب الـ plugins الحديثة (`geolocator`, `device_info_plus`, `shared_preferences`, `package_info_plus`, `firebase_*`) بقى عندها manifests خاصة بيها، بس الـ app target نفسه محتاج واحد لو بيستخدم APIs محتاجة سبب استخدام — راجع تحذيرات الرفع في أول build.

### خطوة 10: المسار

```
Codemagic build  →  TestFlight (معالجة 10–60 دقيقة)  →  اختبار داخلي  →  Submit for Review
```

مراجعة آبل حاليًا عادةً **24–48 ساعة**، بس أول إصدار ممكن ياخد أطول.

---

## القسم الثالث — Firebase (للإشعارات على المنصتين)

لو الإشعارات مطلوبة في الإطلاق:

1. اعمل مشروع Firebase على [console.firebase.google.com](https://console.firebase.google.com)
2. **Android app** بـ package `sa.zeno.app` → حمّل `google-services.json` → `mobile/android/app/`
   - أضف plugin الـ Google Services في `android/app/build.gradle.kts` و `settings.gradle.kts`
3. **iOS app** بـ bundle `sa.zeno.app` → حمّل `GoogleService-Info.plist` → `mobile/ios/Runner/` (لازم يتضاف للـ Xcode target، مش نسخ للفولدر بس)
4. **APNs Key** من Apple Developer (Keys → + → Apple Push Notifications service) → ارفع الـ `.p8` في Firebase → Project Settings → Cloud Messaging
5. ضيف **Push Notifications** و **Background Modes → Remote notifications** capabilities للـ Runner target
6. الملفين دول فيهم مفاتيح — قرّر: يدخلوا الريبو (مقبول، مش أسرار حقيقية) ولا يتحطوا كـ encrypted variables في Codemagic

الكود جاهز مستنيهم — `PushNotifications.init()` guarded بالكامل وهيشتغل أوتوماتيك أول ما الملفات توجد.

---

## ترتيب التنفيذ

### ✅ اتعمل بالفعل (كود)

| # | البند | الملفات |
|---|---|---|
| 1 | ربط الـ environment بـ `APP_ENV` | `lib/main.dart` |
| 3 | `NSPhotoLibraryUsageDescription` | `ios/Runner/Info.plist` |
| 6 | مسح مخلّفات ATS + `ITSAppUsesNonExemptEncryption` | `ios/Runner/Info.plist` |
| 7 | آيفون بس (`TARGETED_DEVICE_FAMILY = 1`) | `ios/Runner.xcodeproj/project.pbxproj` |
| 9 | مسح `MainActivity` الميت | `android/app/src/main/kotlin/sa/zeno/zeno/` |
| 2 | توصيل التوقيع (ناقصه الـ keystore بس) | `android/app/build.gradle.kts` |
| 4 | حذف الحساب — API + خدمة + shee مشترك | `AuthService`, `SessionController`, `delete_account_sheet.dart` |
| — | حساب المراجعة بكود ثابت (ناقصه الـ env) | `OtpService`, `config/integrations.php` |
| — | توحيد الاسم على `Zeno` + `targetSdk = 35` | 6 ملفات |
| — | إعداد Codemagic لـ iOS | `codemagic.yaml` |

### 🔜 الباقي — مرتّب بالأولوية

الحسابين جاهزين، فالمسار الحرج بقى **سلسلة آبل**: App ID → app record → API key → Codemagic → TestFlight. كل خطوة فيها بتفتح اللي بعدها، فابدأ بيها.

**المسار الحرج (آبل — كل خطوة بتفتح اللي بعدها):**
1. تأكّد إن `DEVELOPMENT_TEAM` في المشروع يطابق الـ Team ID بتاع حسابك (القسم الثاني خطوة 1)
2. سجّل App ID لـ `sa.zeno.app` وفعّل عليه **Push Notifications**
3. اعمل الـ app record في App Store Connect — وسجّل الـ **Apple ID** الرقمي بتاعه
4. اعمل **App Store Connect API key** وحمّل الـ `.p8` (**مرة واحدة بس**)
5. اربط Codemagic: ارفع الـ `.p8`، سمّي المفتاح `zeno_app_store`، واعمل مجموعة `appstore` فيها `APP_STORE_APPLE_ID`
6. `git tag v1.0.0 && git push origin v1.0.0` → أول بيلد على TestFlight

**بالتوازي — بنية تحتية (مش معتمدة على حاجة):**
7. `api.zeno.sa` **و** `zeno.sa` على HTTPS ببيانات production حقيقية — المراجع بيفتح التطبيق وبيفتح رابط سياسة الخصوصية كمان
8. `AUTH_REVIEW_PHONE` و `AUTH_REVIEW_OTP` في `.env` بتاع production
9. **مراجعة قانونية لمحتوى `/terms`** — الجزء الوحيد الباقي من بلوكر 5
10. **بلوكر 2** — ولّد الـ keystore واعمل `key.properties`
11. **بلوكر 8** — Firebase + APNs، لو الإشعارات في سكوب الإطلاق

**بالتوازي — أندرويد:**
12. اعمل التطبيق في Play Console وفعّل Play App Signing
13. ابنِ AAB وارفعه على Internal testing

**بعد ما البيلد يشتغل على الجهازين:**
14. **اختبر ريليز حقيقي على أجهزة فعلية** — خصوصًا إن R8 شغّال ومفيش `proguard-rules.pro`
15. الأصول: أيقونة 512 و 1024، feature graphic، سكرين شوتس، نصوص المتجر بالعربي
16. املأ نماذج الكونسول: Data Safety، Content rating، App access، Privacy labels

**الإطلاق:**
17. أندرويد: Internal testing → Production
18. iOS: TestFlight → Submit for Review
