# CLAUDE_TEMPLATE.md

Reusable engineering rulebook for a new Flutter application. Copy this to `CLAUDE.md` in a
new repository, then replace every `<placeholder>` and delete rules the project does not adopt.

Placeholders used throughout: `<app>`, `<feature>`, `<name>`, `<Feature>`, `<verb>`.

> **Rule zero:** this file describes intent and conventions. The code is authoritative. When
> the two disagree, fix one of them in the same change — never leave them contradicting.

---

## 1. Toolchain

- **Pin the SDK version.** Use FVM (`.fvmrc`) so local builds, the lockfile, and CI resolve the
  same Flutter/Dart SDK. Prefer `fvm flutter ...` over a globally-installed SDK in every command,
  script, and doc.
- Commit the lockfile (`pubspec.lock`). Do not regenerate it casually.
- Record the pinned version in this file and keep it in sync when it is bumped.
- Bump the SDK in a dedicated commit, never bundled with feature work.

## 2. Common commands

Document the exact invocation for each task so nobody guesses:

| Task | Command |
|---|---|
| Install deps | `fvm flutter pub get` |
| Run app | `fvm flutter run` |
| Static analysis / lint | `fvm flutter analyze` |
| All tests | `fvm flutter test` |
| Single test file | `fvm flutter test test/path/to/file_test.dart` |
| Single test by name | `fvm flutter test --plain-name "<test name>"` |
| Build release | `fvm flutter build apk --release` / `... ipa --release` |
| Regenerate localizations | `<localization codegen command>` |
| Regenerate asset class | `<asset codegen command>` |
| Format | `fvm dart format .` |

Any command that must run after editing generated-source inputs belongs in this table.

## 3. Do / Don't

These are the highest-priority rules. Violating one is a defect, not a style preference.

- **Don't add packages without asking first.** Propose the package and the reason, then wait for
  approval. Using packages already declared in `pubspec.yaml` is fine.
- **Don't hand-edit generated files.** Anything produced by codegen (localizations, asset classes,
  serialization, freezed/mocks) is regenerated — edit the *source* (ARB file, asset folder,
  annotated class) and rerun the generator. List the generated paths explicitly in this file.
- **Don't hardcode colors, text styles, dimensions, or user-facing strings.** Use the design-system
  classes (`AppColors`, `AppTextStyles`, `AppDimensions`) and the localization accessor.
- **Don't hardcode URLs, endpoint paths, or route paths.** They live in `EndPoints` and
  `routes_keys.dart` respectively.
- **Use `safeEmit(state)`** instead of raw `emit` in cubits — it guards against emitting after
  `close()` and prevents a whole class of "emit after dispose" crashes.
- **Don't put business logic in widgets.** Widgets render state and dispatch intent; decisions live
  in cubits, use-cases, or repositories.
- **Don't call an API client directly from the presentation layer.** Always go through a repository.
- **Don't swallow errors.** Every failure path resolves to a typed `Failure`, and every `Failure`
  reaching the UI produces a user-visible outcome.
- **Don't leave `print`/`debugPrint` in committed code.** Use the project logger.

## 4. Architecture

### 4.1 Pattern at a glance

- **Feature-first / vertical slicing** under `lib/features/<feature>/`; cross-cutting concerns are
  isolated in `lib/core/`. A feature owns its models, repos, cubits, and screens.
- **Layered Clean-ish architecture.** Every feature has `data` (models + repos) and `presentation`
  (cubits + views). Add a `domain` layer (entities + repository contract + use-cases) when the
  feature has real business rules, multiple data sources, or logic worth unit-testing in isolation.
  Trivial CRUD features may stay two-layer — but the direction of travel is toward full three-layer.
- **State management:** BLoC `Cubit` (`flutter_bloc`), one cubit per concern. Cubits emit immutable,
  `Equatable`-backed states.
- **Dependency injection:** a single `get_it` container (`getIt`) wired in
  `lib/core/services/service_locator.dart`. Repositories and data sources are singletons; cubits and
  use-cases are factories.
- **Functional error handling:** every repository method returns `Future<Either<Failure, T>>`
  (Dartz). No exceptions cross the repository boundary.
- **Navigation:** declarative routing via `go_router`, with `StatefulShellRoute.indexedStack`
  driving the bottom nav when the app has one.
- **Networking:** a single API gateway abstraction with an implementation behind it, plus chained
  interceptors for cross-cutting request concerns.

### 4.2 Dependency rule

Dependencies point inward only:

```
presentation  ──▶  domain  ──▶  (nothing)
     │                ▲
     └──────▶  data ──┘        data implements domain contracts
```

- `presentation` may not import from another feature's `data` or `presentation`.
- `data` may not import `presentation`.
- `domain` imports nothing from Flutter or any transport/serialization library.
- Cross-feature sharing goes through `core/`, never feature-to-feature.

### 4.3 Folder structure

```
lib/
├─ main.dart                          # composition root: bootstrap, MultiBlocProvider, MaterialApp.router
├─ generated/                         # codegen output (DO NOT edit by hand)
├─ l10n/                              # localization sources (ARB)
├─ core/
│  ├─ components/                     # shared widgets (buttons, fields, toasts, app bars, shimmers)
│  ├─ constants/                      # AppConstants — keys, box names, durations
│  ├─ cubit_extension/                # safeEmit + cubit utilities
│  ├─ databases/api/                  # ApiConsumer, implementation, EndPoints, interceptors
│  ├─ enums/                          # cross-feature enums
│  ├─ errors/                         # Failure, Exceptions, error wrappers
│  ├─ extensions/                     # shared Dart/Flutter extensions
│  ├─ init/                           # runtime variables, logger
│  ├─ managers/                       # GLOBAL cubits mounted in main.dart
│  │  ├─ user_cubit/                  #   auth/user state
│  │  ├─ locale_cubit/                #   locale switching
│  │  ├─ connectivity_cubit/          #   network state
│  │  └─ app_config_cubit/            #   server-driven config
│  ├─ models/                         # cross-feature models (UserEntity, PaginatedResponse)
│  ├─ navigation_bar/                 # shell + custom nav bar
│  ├─ params/                         # request/param value objects (LoginParam, ...)
│  ├─ routing/                        # app_router.dart, routes.dart, routes_branches.dart, routes_keys.dart
│  ├─ services/                       # service_locator (get_it) and platform services
│  ├─ styles/                         # AppColors, AppTextStyles, AppDimensions, theme
│  ├─ utils/                          # helpers, secure-storage manager, analytics, generated assets
│  └─ views/                          # global views: loading, no_internet, web_view, error
└─ features/<feature>/
   ├─ data/
   │  ├─ models/                      # JSON-serializable DTOs
   │  ├─ datasources/                 # remote/local sources (when the feature warrants them)
   │  └─ repos/
   │     ├─ <feature>_repo.dart       # abstract contract
   │     └─ <feature>_repo_impl.dart  # implementation via ApiConsumer + handleRequest
   ├─ domain/                         # for features with real business rules
   │  ├─ entities/                    #   plain Dart models, no JSON
   │  ├─ repositories/                #   abstract domain-level repo
   │  └─ usecases/                    #   one class per action
   └─ presentation/
      ├─ manager/                     # cubits — folder each: <name>_cubit.dart + <name>_state.dart
      └─ views/                       # <name>_view.dart screens + widgets/ for screen-local widgets
```

### 4.4 Layer responsibilities

**Data layer — `features/<feature>/data/`**

- `models/` — DTOs that mirror the API JSON shape, constructed via `fromJson`, serialized via
  `toJson`. DTOs are transport objects; do not leak them into `domain`.
- `datasources/` — thin wrappers over a single transport (REST, local DB, cache). Introduce them
  when a repo has more than one source to coordinate.
- `repos/<feature>_repo.dart` — **abstract class** declaring `Future<Either<Failure, T>>` methods.
  Callers depend on this abstraction, never the implementation.
- `repos/<feature>_repo_impl.dart` — concrete implementation taking the `ApiConsumer` abstraction in
  its constructor, wrapping every call in the shared request helper.
- Register only the impl in DI, but type-hint the abstract class in consumer constructors so fakes
  can be substituted in tests.

**Domain layer — `features/<feature>/domain/`**

- `entities/` — pure Dart, no JSON, no Flutter imports, no transport concerns.
- `repositories/<feature>_repository.dart` — abstract repo at the domain boundary.
- `usecases/<verb>_usecase.dart` — **one class per action**, with a single public `call()`.
  Cubits compose use-cases instead of touching repos directly.

**Presentation layer — `features/<feature>/presentation/`**

- `manager/<name>_cubit/` — one folder per cubit holding `<name>_cubit.dart` and
  `<name>_state.dart` (joined with `part`). Cubits dispatch repo/use-case calls and `safeEmit` a
  state.
- `views/` — `<name>_view.dart` screens plus a `widgets/` subfolder for screen-local widgets.
  Screens consume cubits via `BlocBuilder` / `BlocConsumer` / `BlocListener`.

## 5. Networking

- **`ApiConsumer`** — an abstract gateway interface. Repositories depend on this, never on the
  concrete HTTP client, and never on `Dio` types directly.
- **One registered implementation** built asynchronously when it needs runtime data (package info,
  device info) before its first request. It sets default headers once (platform, version code,
  version name, language) and registers interceptors in a **fixed, documented order** — order is
  load-bearing, so record it here and preserve it.
- **Interceptors** carry cross-cutting request concerns (auth token, locale, device id, logging).
  A new cross-cutting header is a new interceptor, not a per-call argument.
- **Auth headers are applied per request**, resolved from secure storage at call time rather than
  set statically on the client — so a token refresh or logout takes effect immediately.
- **`EndPoints` is the single source of truth** for paths and base URLs. Resolve environment
  (dev/staging/prod) through getters that read the environment manager on each access, so flipping
  environments at runtime takes effect without a restart.
- **If the app talks to more than one backend**, expose an explicit per-backend method variant
  rather than passing raw base URLs from features. Document whether each variant shares the
  singleton's client state — a fresh client per call means interceptor/instance state is *not*
  shared, which is a real source of bugs.

## 6. Error handling

- Repository methods return `Future<Either<Failure, T>>`. Left is a typed `Failure`, right is the
  success payload.
- **A single shared request wrapper** (`handleRequest<T>(request, fromJson)`) is the standard way to
  call the API from a repo. It:
  1. normalizes response shape (`String` / `Map` / `List`),
  2. maps transport exceptions to `ServerFailure`,
  3. centralizes global reactions to specific server error codes (session expiry, forced logout,
     forced update, maintenance mode).
- **Repos use the wrapper, not raw `try`/`catch`.** Bypassing it silently opts out of every global
  behavior above.
- `Failure` subtypes are defined in `core/errors/` and carry a user-safe message plus optional
  diagnostic detail. Never surface a raw exception string to the user.
- Cubits `fold` the `Either` into an explicit success or error state. There is no third "silently
  did nothing" path.

## 7. Dependency injection

- `getIt = GetIt.instance`, wired in `lib/core/services/service_locator.dart`.
- `setupServiceLocator()` is awaited in `main()` **before `runApp`**. It registers the HTTP client,
  the API gateway, then every `<Feature>RepoImpl` as a singleton.
- Split registration into per-area functions (`setup<Area>Dependencies(getIt)`) once the locator
  grows past comfortable reading length.
- **Lifetime rules:**
  - Repositories, data sources, API gateway, platform services → **singleton** (lazy where the
    construction cost is non-trivial).
  - Cubits and use-cases → **factory**, so each screen gets a fresh instance with no leaked state.
- Adding a feature: construct `<Feature>RepoImpl(getIt<ApiConsumer>())`, register the singleton,
  then provide the cubit via `BlocProvider(create: (_) => <Name>Cubit(getIt<<Feature>Repo>()))` at
  the right scope — global in `main.dart` only if it must outlive screens, otherwise at the route.
- **Never call `getIt<T>()` from inside a widget's `build`.** Resolve at the provider boundary.

## 8. Routing

- The router is built **once** at startup in `AppRouter.router`.
- Shape: an entry route at `/`, then a `StatefulShellRoute.indexedStack` whose branches live in
  `routes_branches.dart`. All other screens are flat `GoRoute`s in `routes.dart`.
- Screens that must appear **above** the bottom-nav shell set `parentNavigatorKey: parentKey`.
- `parentKey` and `shellKey` are exported `GlobalKey<NavigatorState>`s used by the router and by
  navigation helpers (`popUntilPath`, static `back([result])`).
- **`routes_keys.dart` holds every path string.** Never hard-code a path in feature code.
- Provide a navigation controller so code outside the widget tree can switch tabs programmatically.
- Wrap each shell branch child in `KeyedSubtree(key: ValueKey('<branch>_${locale.languageCode}'))`
  so a locale change forces a rebuild. Preserve this when adding branches.
- Register router observers (analytics/screen tracking, debug tooling) in one place.

## 9. State management

- **Global cubits** are mounted once in `main.dart` via `MultiBlocProvider`. Reserve this for state
  that genuinely spans the app: user/auth, locale, connectivity, remote config, shared reference
  data. Everything else is per-screen.
- Global cubits that need to hydrate call their load method at construction.
- **Cross-cubit reactions** belong in a root-level `BlocListener` (e.g. clearing per-user caches on
  logout, refetching on login) — not in ad-hoc calls scattered through screens. Document each such
  wiring so it survives refactors.
- **Per-screen cubits** are mounted in the route builder, constructed from `getIt`.
- Use `safeEmit` in any cubit that can emit after `close()` — i.e. any cubit awaiting async work.
- **State classes are immutable and `Equatable`.** Emitting an identical state must be a no-op.
- **Mutable UI-only scratch state** (in-progress form fields, current tab, transient selections)
  lives on the cubit instance, *not* in the state class, when the value is not part of the rebuild
  contract. Anything the UI must rebuild on belongs in the state.
- One cubit per concern. A cubit that owns unrelated concerns should be split.

## 10. Persistence

Pick the right store; do not blur them:

| Store | Use for |
|---|---|
| Hive | structured app-local data, cached domain objects |
| SharedPreferences | small primitive flags: selected environment, debug toggles, fresh-install detection |
| Secure storage | **all** credentials and tokens, behind a single manager class |
| Remote DB / cloud storage | realtime or shared data; commit its security rules to the repo |

- **Never store tokens or PII in SharedPreferences or Hive.** Secure storage only.
- Access every store through a dedicated manager/service — no direct store calls from widgets.
- Initialize stores in the documented bootstrap order (§11) before anything reads them.

## 11. App bootstrap ordering (load-bearing)

`main()` has a strict order. Document the project's actual sequence here and require new
initialization to be inserted at the correct point rather than appended to the end:

1. `WidgetsFlutterBinding.ensureInitialized()` + orientation lock
2. Platform/backend SDK init (Firebase or equivalent)
3. Fresh-install / migration checks
4. **Environment manager init** — must precede any URL construction
5. Debug tooling init
6. Crash-reporting handlers (`FlutterError.onError` + `PlatformDispatcher.instance.onError`)
7. Notifications init
8. Third-party SDK init
9. Release-only analytics init (`!kDebugMode`)
10. Local storage init + open required boxes
11. **`setupServiceLocator()`** — anything API-touching must come after this
12. Env/config file load
13. `runApp(...)`

Rules: nothing may read config before step 4; nothing may resolve from DI before step 11; expensive
non-blocking work should not sit in front of `runApp` — defer it to the first frame instead.

## 12. Design system

- **Three source-of-truth classes**, and nothing outside them defines visual constants:
  - `AppColors` — every color. No `Color(0xFF...)` literals in features.
  - `AppTextStyles` — every text style. No inline `TextStyle(...)` in features.
  - `AppDimensions` — spacing scale, radii, icon sizes, breakpoints.
- Theme is configured once (seed color, global font family, component themes) and consumed via the
  theme, not re-declared per widget.
- Prefer a **spacing scale** (4/8/12/16/24/32) over arbitrary padding values.
- Shared widgets live in `core/components/` — buttons, inputs, toasts, dialogs, app bars, loading
  and empty states. A widget used by two or more features is promoted there; a widget used by one
  screen stays in that screen's `widgets/`.
- Support light and dark themes from day one if the product needs either — retrofitting is costly.

## 13. UI/UX conventions

- Every async screen handles **four states explicitly**: loading, empty, error (with retry), and
  content. No screen may render a blank body while loading.
- Use skeletons/shimmers over spinners for content-shaped loads.
- Errors are shown in-context (inline or toast), never as a raw exception string.
- Forms validate on submit and surface field-level messages; the submit control disables while a
  request is in flight to prevent double submission.
- **Support RTL** if any supported locale is RTL: use `EdgeInsetsDirectional` and `start`/`end`
  rather than `left`/`right`, and verify screens in both directions.
- Respect safe areas and text-scale settings; never assume a fixed font size fits.
- Provide semantic labels for interactive elements and images that carry meaning.
- Keep `build` methods shallow — extract subtrees into named widgets, not `Widget _buildX()` methods,
  so they get their own rebuild scope and a class name in the widget tree.
- Prefer `const` constructors wherever possible.

## 14. Localization

- All user-facing strings live in the ARB sources under `lib/l10n/`. **Zero hardcoded strings in
  widgets** — including error messages, button labels, and validation text.
- Edit the ARB files, then regenerate. **Never hand-edit generated localization output.**
- Key naming: `<screen/feature>_<element>_<variant>` in `lowerCamelCase`. Keep keys stable — renaming
  a key is a breaking change across every locale file.
- Add the key to **every** supported locale in the same commit; an untranslated key is a bug, not a
  follow-up.
- Locale changes are driven by a `LocaleCubit` that rebuilds `MaterialApp.router`.

## 15. Naming conventions

| Thing | Convention | Example |
|---|---|---|
| Files & folders | `snake_case` | `booking_details_view.dart` |
| Classes, enums, typedefs | `PascalCase` | `BookingRepoImpl` |
| Members, locals, params | `lowerCamelCase` | `selectedDate` |
| Private members | leading underscore | `_controller` |
| Global constants | `k` prefix | `kLanguageBoxName` |
| Abstract repo | `<feature>_repo.dart` → `<Feature>Repo` | `AuthRepo` |
| Repo impl | `<feature>_repo_impl.dart` → `<Feature>RepoImpl` | `AuthRepoImpl` |
| Use-case | `<verb>_<noun>_usecase.dart` → `<Verb><Noun>UseCase` | `SendMessageUseCase` |
| Cubit | `<name>_cubit.dart` → `<Name>Cubit` | `LoginCubit` |
| State | `<name>_state.dart` → `<Name>State` + variants | `LoginLoading` |
| Screen | `<name>_view.dart` → `<Name>View` | `ProfileView` |
| DTO | `<name>_model.dart` → `<Name>Model` | `UserModel` |
| Entity | `<name>.dart` → `<Name>` | `User` |
| Request params | `<name>_param.dart` → `<Name>Param` | `LoginParam` |
| Global static config | `App` prefix | `AppColors`, `AppConstants` |
| Test file | mirrors source + `_test` | `login_cubit_test.dart` |

- Booleans read as predicates: `isLoading`, `hasError`, `canSubmit`.
- Async methods that fetch are named `fetch*` / `load*`; mutating ones use the verb (`submitLogin`).
- Do not abbreviate beyond well-known terms (`id`, `url`, `api`, `dto`).

## 16. Dependency management

- **Ask before adding any dependency.** State the package, the problem it solves, and why the
  platform/stdlib or an existing dependency is insufficient.
- Prefer a small amount of first-party code over a package that solves a slightly different problem.
- Vet candidates on: maintenance recency, null-safety, platform support, transitive weight, license,
  and whether it duplicates something already in the tree.
- **Wrap third-party SDKs behind a project-owned interface** when they touch more than one call
  site — so replacing the vendor is a one-file change.
- Pin versions with meaningful constraints; commit the lockfile; upgrade deliberately in a dedicated
  commit with the analyzer and tests green.
- Remove dependencies when the last usage disappears.

## 17. Testing

- **Every repository, cubit, and use-case is unit-tested.** These are the layers with logic, and
  they are all constructor-injected specifically to make this cheap.
- Test folder structure **mirrors `lib/`** exactly: `test/features/<feature>/...`.
- Naming: `<source>_test.dart`; `group` by class/method, `test` names read as behavior statements
  ("emits Error when the repo returns a ServerFailure").
- **Cubit tests** use `bloc_test` and assert the full emitted state sequence, not just the final
  state. Cover the success path, each failure path, and any guard/short-circuit.
- **Repository tests** mock the `ApiConsumer` abstraction and assert both `Left(Failure)` and
  `Right(T)` branches, plus correct request construction.
- Mock at the **abstraction** (`<Feature>Repo`, `ApiConsumer`) — never mock a concrete HTTP client.
- **Widget tests** for shared `core/components/` and for screens with meaningful conditional
  rendering. Assert on user-visible text and semantics, not on internal widget structure.
- Tests must be deterministic: no real network, no real clock, no `sleep`. Inject time and randomness.
- A bug fix ships with a regression test that fails before the fix.
- `fvm flutter analyze` and `fvm flutter test` must both be clean before a PR is opened.

## 18. Git workflow

- **Branch from the integration branch** (`develop` or `main` — state which). Never commit directly
  to it.
- Branch naming: `<type>/<short-kebab-description>` where type is
  `feat` | `fix` | `refactor` | `chore` | `docs` | `test`.
- **Conventional commits:** `type(scope): imperative summary`, e.g. `feat(auth): add otp resend`.
  Body explains *why*, not what the diff already shows.
- One logical change per commit. Keep formatting-only churn in its own commit.
- Rebase your branch on the integration branch before opening a PR; resolve conflicts locally.
- **PR requirements:** description of the change and reasoning, screenshots or a recording for any UI
  change, analyzer and tests green, no commented-out code, no leftover debug logging.
- Never commit secrets, keystores, `.env` files, or service-account JSON. They come from CI secrets
  and are listed in `.gitignore`.
- Never force-push a shared branch.

## 19. CI/CD

- CI runs on push to the integration branch and on every PR. At minimum it must run: dependency
  install, static analysis, and the full test suite. A red pipeline blocks merge.
- Release artifacts are produced by CI, not from a developer machine.
- **Signing material is injected at build time from CI secrets** (e.g. a base64-encoded keystore
  materialized into a properties file) and never stored in the repo.
- Distribution to testers is automated (Fastlane lane or equivalent) and documented here with the
  exact command.
- Version and build number are bumped in a dedicated commit; document the scheme
  (`<major>.<minor>.<patch>+<build>`).

## 20. Documentation standards

- **This file is the entry point.** Keep it current — an architectural change and its documentation
  land in the same PR.
- Document *decisions and invariants*, not a file listing that the repo already provides. Load-bearing
  ordering, lifetime rules, and "don't do X because Y" are worth writing down; a folder tree that
  restates `ls` is not.
- Public APIs in `core/` carry doc comments explaining intent and constraints.
- Comments explain **why**, never **what**. Delete a comment that only restates the code.
- Historical design notes may live as top-level `*.md` files, clearly marked as historical. **The
  code is authoritative** when notes and code disagree.
- Every new pattern introduced (new layer, new codegen step, new required ordering) gets a short
  section here so the next person does not have to reverse-engineer it.

## 21. Recipes

**New endpoint** → add the path to `EndPoints`. Never hard-code a URL in a feature.

**New repo** → create both `<feature>_repo.dart` (abstract) and `<feature>_repo_impl.dart`; the impl
takes `ApiConsumer` and wraps every call in the shared request helper; register the impl as a
singleton in the service locator; consumers type-hint the abstraction.

**New cubit** → one folder under `manager/` containing `<name>_cubit.dart` + `<name>_state.dart`
(joined with `part`); states are immutable and `Equatable`; use `safeEmit`; keep non-rebuild UI
scratch state on the cubit instance rather than in the state class.

**New screen** → add the path to `routes_keys.dart`, add the route to `routes.dart`, mount the cubit
via `BlocProvider` in the route builder, and set `parentNavigatorKey: parentKey` if the screen must
appear above the bottom nav.

**New asset** → drop the file into `assets/<bucket>/`, ensure `pubspec.yaml`'s `flutter.assets`
covers that bucket, and regenerate the asset class. Never edit the generated file.

**New string** → add the key to **every** ARB locale file, then regenerate. Never hand-edit the
generated localization output.

**New global cubit** → justify why it must outlive screens; mount it in `main.dart`'s
`MultiBlocProvider`; document any cross-cubit listener wiring it depends on.

**New third-party SDK** → get approval first; initialize it at the correct position in the bootstrap
order; wrap it behind a project-owned interface if it has more than one call site.
