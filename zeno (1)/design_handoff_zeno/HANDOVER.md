# zeno — Engineering Handover Document

> **Primary engineering handover for the zeno platform, prepared before backend & mobile development begins.**
>
> Author role: Lead Product Designer / System Analyst / UX Architect
> Source of truth: the interactive design prototype (9 `*.dc.html` files). No Figma required.
> Status of the product: **high-fidelity interactive prototype**. All data is mocked in the client. No real backend, auth, or persistence exists yet.

---

## ⚠️ How to read this document (confirmed vs. inferred)

Because the deliverable is a **design prototype**, this document has two kinds of statements. They are always tagged:

- **[CONFIRMED]** — behavior that is actually implemented and visible in the prototype. This is authoritative for UI/UX.
- **[INFERRED]** — a backend/API/business requirement that the UI *implies* but that is **not** decided. These are engineering recommendations, **not** product decisions. Every `[INFERRED]` item that touches real business rules is also listed again in the final **Missing Functional Requirements** section with the question that must be answered first.

Do **not** treat `[INFERRED]` API shapes, field limits, or rules as final. They are starting points for the backend/PM discussion.

---

## Table of contents

1. [Project overview](#1-project-overview)
2. [Platform architecture & modules](#2-platform-architecture--modules)
3. [File / surface map](#3-file--surface-map)
4. [Design tokens](#4-design-tokens)
5. [Reusable component library](#5-reusable-component-library)
6. [Navigation architecture](#6-navigation-architecture)
7. [Mobile app — screen-by-screen](#7-mobile-app--screen-by-screen)
8. [Employer web dashboard — screen-by-screen](#8-employer-web-dashboard--screen-by-screen)
9. [Public website — screen-by-screen](#9-public-website--screen-by-screen)
10. [Admin panel](#10-admin-panel)
11. [Complete user journeys](#11-complete-user-journeys)
12. [Screen states matrix](#12-screen-states-matrix)
13. [Forms reference](#13-forms-reference)
14. [Business logic & rules](#14-business-logic--rules)
15. [Interaction → API map](#15-interaction--api-map)
16. [Backend requirements (inferred)](#16-backend-requirements-inferred)
17. [Mobile requirements](#17-mobile-requirements)
18. [Web requirements](#18-web-requirements)
19. [Responsive behavior](#19-responsive-behavior)
20. [Missing Functional Requirements](#20-missing-functional-requirements)

---

## 1. Project overview

### 1.1 What is zeno?
**zeno (زينو)** is a **local (proximity-based) job marketplace** for Saudi Arabia, focused on **blue-collar, hourly, part-time, seasonal, and full-time service jobs** — hospitality, retail/cashier, delivery & logistics, cleaning, events, maintenance, security, crafts. It connects **job seekers** with **nearby employers** and lets them **communicate directly (in-app or via WhatsApp)**.

The product spans three surfaces:
- A **mobile app** (Arabic, RTL) for job seekers and employers.
- A **public marketing website** (Arabic, RTL) for discovery, employer conversion, and content.
- An **employer web dashboard** for posting and managing jobs and applicants.

### 1.2 What problem does it solve?
- **For job seekers:** finding legitimate nearby work is slow and fragmented. zeno surfaces the closest opportunities first (map + distance), lets them apply in one tap, and connects them to the employer immediately.
- **For employers:** reaching nearby, ready-to-work candidates is hard. zeno lets a business post a job in minutes and receive/manage applicants from a single place.
- **Trust:** verified businesses, clear job terms, no fees charged to seekers.

### 1.3 Who are the users? (three roles — [CONFIRMED] as selectable roles in the prototype)
| Role | Arabic | Primary surface | Purpose |
|---|---|---|---|
| **Candidate / job seeker** | المرشّح / الباحث عن عمل | Mobile app | Browse, search, apply, chat |
| **Employer** | صاحب العمل | Mobile app **and** web dashboard | Post jobs, review applicants, chat |
| **Admin** | الإدارة | Mobile-app admin view (prototype) | Oversight: stats, jobs, reports |

Registration also supports an **account type** distinction at signup: **individual (فرد)** vs **organization (منشأة)** — [CONFIRMED] as a toggle; its downstream effect is [INFERRED].

### 1.4 How the platform works (happy path)
1. User installs app → sees onboarding → picks role.
2. Signs up with **Saudi mobile number** → verifies via **4-digit OTP**.
3. Completes a **registration/profile** step.
4. **Candidate:** browses/searches jobs, views on map by distance, opens a job, applies in one tap, tracks application status, chats with employer (in-app or WhatsApp).
5. **Employer:** posts a job, receives applicants, accepts/rejects, chats, can pause/activate the listing.
6. **Admin:** monitors platform stats, jobs, and reports.

### 1.5 Main modules
- **Auth & Onboarding** (splash, onboarding carousel, phone, OTP, registration, account-type)
- **Job discovery** (browse, category filter, text search, advanced filters, nearby/map, job detail)
- **Applications** (apply, my-applications with status tracking)
- **Messaging** (in-app chat + WhatsApp deep-link handoff)
- **Profile** (candidate profile, edit profile)
- **Employer tools** (dashboard/overview, post job, manage listings + pause/activate, applicants + accept/reject, employer chat, employer account)
- **Notifications**
- **Admin** (overview, jobs oversight, reports)
- **Public web** (landing, jobs browse+detail, pricing, about, company profile, contact, terms)

---

## 2. Platform architecture & modules

```
zeno
├── Mobile App (RTL, Arabic)              [Zeno.dc.html]
│   ├── Auth/Onboarding module
│   ├── Candidate module (5-tab bottom nav)
│   ├── Employer module (4-tab bottom nav)
│   └── Admin module (3-tab segmented)
│
├── Public Website (RTL, Arabic)
│   ├── Landing            [Zeno-Landing.dc.html]
│   ├── Jobs browse+detail [Zeno-Jobs.dc.html]
│   ├── Pricing            [Zeno-Pricing.dc.html]
│   ├── About              [Zeno-About.dc.html]
│   ├── Company profile    [Zeno-Company.dc.html]
│   ├── Contact            [Zeno-Contact.dc.html]
│   └── Terms & Privacy    [Zeno-Terms.dc.html]
│
└── Employer Web Dashboard (RTL, Arabic)  [Zeno-Web.dc.html]
    ├── Overview
    ├── Jobs management
    ├── Applicants
    ├── Messages
    └── Post-job modal
```

> **Architecture note [CONFIRMED in prototype / INFERRED for production]:** In the prototype each surface is a self-contained single-page component holding its own mock state. For production these should share **one backend, one identity system, and one data model**. The candidate experience is app-first (web `Jobs` page routes application actions back to "apply via the app"); the employer experience exists on **both** app and web.

---

## 3. File / surface map

| File | Surface | Role(s) | Notes |
|---|---|---|---|
| `Zeno.dc.html` | Mobile app (all roles) | Candidate, Employer, Admin | The full app: ~20 screens behind a single `screen` state machine |
| `Zeno-Landing.dc.html` | Website — home | Public | Marketing landing |
| `Zeno-Jobs.dc.html` | Website — jobs | Public / Candidate | Desktop browse, filters, detail drawer |
| `Zeno-Web.dc.html` | Web dashboard | Employer | Overview, jobs, applicants, messages, post modal |
| `Zeno-Pricing.dc.html` | Website — pricing | Public / Employer | Employer packages |
| `Zeno-About.dc.html` | Website — about | Public | Company story |
| `Zeno-Company.dc.html` | Website — company profile | Public / Candidate | A single employer's public profile + its jobs |
| `Zeno-Contact.dc.html` | Website — contact | Public | Contact form + channels |
| `Zeno-Terms.dc.html` | Website — legal | Public | Terms & privacy, TOC |
| `assets/zeno-logo.png` | Brand | — | Full logo (mark + "zeno" + "زينو") |
| `assets/zeno-mark.png` | Brand | — | Icon-only mark (used in app loading) |

> **Routing [INFERRED]:** the prototype is not URL-routed. Recommended production routes are given per screen below under **Route**. They are proposals, not confirmed.

---

## 4. Design tokens

### 4.1 Color palette [CONFIRMED — derived from the uploaded logo]
| Token | Hex | Usage |
|---|---|---|
| `--amber` (primary/brand) | `#F7BE17` | Primary CTAs, active states, highlights, accents |
| `--amber-deep` | `#F2A50E` | Gradient partner for amber (e.g. `linear-gradient(120deg,#F7BE17,#F2A50E)`) |
| `--charcoal` (ink/chrome) | `#211F20` / `#2B2724` | Headers, bottom nav, dark surfaces, primary text |
| `--charcoal-grad` | `#34302B → #26221F` | Dark header/hero gradients |
| `--paper` (app bg) | `#F6F5F1` | Mobile screen background |
| `--paper-web` | `#F1EFE9` | Web app background |
| `--paper-warm` | `#FAF8F3` | Website background |
| `--surface` | `#FFFFFF` | Cards, inputs |
| `--border` | `#EFEDE6` / `#EDEAE2` / `#ECEAE3` | Hairline borders |
| `--text-strong` | `#211F20` | Titles |
| `--text-body` | `#5A554C` / `#56524A` | Body |
| `--text-muted` | `#8A857A` / `#9A958A` | Secondary/meta |

**Semantic / status colors [CONFIRMED]:**
| Meaning | FG | BG |
|---|---|---|
| Success / accepted / active | `#1F8A4D` / `#1F7A3D` | `#E7F4EC` / `#E3F3E8` |
| Warning / new / review / pending | `#8A6D12` | `#FDF3D6` |
| Error / rejected / stopped | `#B2453A` / `#B23232` | `#FBEDEA` / `#FBE6E6` |
| Info / submitted / distance | `#2E6E8A` / `#5B6470` | `#E2EEF4` / `#EEF1F4` |
| WhatsApp | `#25D366` (on-white) / `#fff` | `#25D366` |
| Verified badge | `#2E86C1` / `#5FB0E8` | — |

**Category tint pairs [CONFIRMED]** (icon color / background): amber `#8A6D12`/`#FDF1CC`, green `#4F7A2E`/`#E6F0E1`, red `#B2453A`/`#F8E3E1`, blue `#2E6E8A`/`#E2EEF4`, purple `#6A4E8A`/`#ECE6F4`, teal `#2E7A6B`/`#E0EFEC`.

### 4.2 Typography [CONFIRMED]
- **Font family:** `Tajawal` (Google Fonts), weights **400, 500, 700, 800, 900**. Fallback `sans-serif`.
- **Direction:** RTL globally (`direction: rtl`).
- **Scale (observed):**
  - Display / H1 (web hero): 40–56px / weight 900
  - Section H2: 32–38px / 900
  - Card title: 17–20px / 800
  - Body: 14.5–17px / 500–600
  - Meta / caption: 11.5–13.5px / 600–700
  - Mobile: titles 18–22px, body 13–15px, never below ~11px meta.
- `letter-spacing: -.01em` on large headings; `text-wrap: balance` on hero H1.

### 4.3 Spacing & layout [CONFIRMED]
- Base rhythm ~4px; common gaps 8/10/12/16/20/22/26px.
- Page gutters: mobile 18–22px; web content max-width **1080–1240px**, gutter 26px.
- Card padding: 13–26px depending on density.

### 4.4 Radius [CONFIRMED]
| Element | Radius |
|---|---|
| Small chips/badges | 7–10px |
| Buttons / inputs | 11–16px |
| Cards | 17–22px |
| Large panels / modals | 20–30px |
| Icon tiles | 11–18px |
| Phone bezel | 44px; screen 36px |
| Pills / fully-round | 100px |
| Avatars | 50% |

### 4.5 Shadows / elevation [CONFIRMED]
- Card rest: none or `0 10px 22px -16px rgba(40,35,25,.3)`.
- Card hover: `0 22–26px 40–46px -26/-28px rgba(40,35,25,.3)`.
- Floating chrome / CTAs: `0 12–18px 24–32px -14/-16px rgba(40,35,25,.5)`.
- Modals/drawers: `0 40px 80px -30px rgba(40,35,25,.6)` (drawer uses `-30px 0 70px -30px`).
- Elevation order: page < card < sticky header (blur) < bottom nav < modal/drawer < toast.

### 4.6 Icons [CONFIRMED]
- **Iconsax** icon font (`iconsax.gitlab.io/i/icons.css`), rendered via `<i class="iconsax" icon-name="...">`.
- **Validated names in use:** `home-2, gps, task-list, document-text-1, messages-2, user-1, coffee, truck, shop, broom, award, briefcase, magic-star, shield, setting-2, ruler-and-pen, snow, more-square, search-normal-1, filter-search, bell-2, location, tick-circle, close-circle→x, chevron-left/right/down, arrow-left, add, edit-2, eye, pause-circle, play/play-circle, message-text, send-2, mobile, star, verify, calendar-1, bookmark-add, clock, chart-square, logout-1, globe, info-circle, grid-apps, search-status-1`.
- **Rule for engineers:** before shipping any new icon, confirm the glyph exists in the Iconsax set (several intuitive names — `grid-2`, `scan-barcode`, `global`, `call`, `close-circle`, `category`, `bookmark`, `message-question` — do **not** exist and were replaced). Keep a validated icon whitelist.

### 4.7 Motion / animation / transitions [CONFIRMED]
| Name | Definition | Use |
|---|---|---|
| Entrance `fadeUp` / `.view` | `translateY(8–10px)→0`, .28–.3s ease | Screen/section entrance (transform-only, never opacity-0 at rest) |
| Card stagger | `zrise` transform-based, per-item delay | List cards |
| `breathe` | scale pulse 1.5s | Loading logo |
| Spinner ring | rotate loop | Loading |
| `pulseDot` | scale+opacity 1.8s | Notification dot, "live" badge |
| `ring` | scale .6→2.2 + fade 2.4s | Map location pulse |
| `sheetIn` | translateX(-40px)+fade .28s cubic | Modal/bottom-sheet entrance |
| `dim` | opacity fade .2s | Modal backdrop |
| `toastIn` | translateY(-16px)+fade .3s cubic | Toast |
| Button press | `:active { transform: scale(.97) }`; hover `translateY(-1–2px)` | All buttons |

> **Important implementation rule [CONFIRMED]:** entrance animations are **transform-only** (element is fully opaque at rest). Do not animate from `opacity:0` for content that must be screenshot/print-visible.

---

## 5. Reusable component library

Every entry: **purpose · variants · states · reusability**. These are the atomic pieces to build as a shared component kit.

### 5.1 Buttons
- **Primary (amber):** filled `#F7BE17`, charcoal text. Main CTAs. States: rest / hover (lift) / active (scale) / **disabled** (used when a form isn't ready — e.g. Post-job "نشر" when `postReady=false`). Highly reusable.
- **Primary (charcoal):** filled `#2B2724`, white text, amber icon. Secondary emphasis / dark CTAs.
- **Secondary/ghost:** white with border `#E7E3DA`. Cancel, secondary actions.
- **Icon button:** 38–46px square, rounded, tonal bg. Back, close (`x`), edit, bookmark, notification bell.
- **WhatsApp button:** green `#25D366`, white text + `message-text` icon.
- **Segmented option button:** used across filters & forms — selected = charcoal fill/white text; unselected = white/border. Variant: category chip (amber-tint selected).

### 5.2 Cards
- **Job card (mobile):** avatar/category icon tile, title, company·district, type/distance/salary chips. Tap → job detail. Variant: web job card (larger, verified badge, "التفاصيل" affordance).
- **Application card:** job title, company, **status badge**, **3-step status tracker** (submitted → review → accepted/rejected).
- **Applicant row (employer):** initial avatar, name, status badge, role·city·years·request#, action buttons (accept/reject or message).
- **Stat card:** icon tile, big value, label, delta badge. Web overview + admin.
- **Employer job card:** listing with applicants count, views, edit, pause/activate.
- **Notification card:** tinted icon, title, body, time, unread dot.
- **Pricing card:** tier name, price, feature list, CTA (one "featured" variant).

### 5.3 Inputs & form controls
- **Text input:** label + field, 44–52px height, radius 14, border `#E7E3DA`. Variants: phone (numeric, with +966/flag context), search (leading icon), textarea.
- **OTP input:** 4 separate single-digit boxes with auto-advance focus.
- **Select/dropdown:** native `<select>` styled (sort order, post-job category/type).
- **Segmented control:** work type, unit, gender, nationality, contact channel, radius.
- **Checkbox (web filter):** custom square with tick.
- **Range slider:** distance filter (`accent-color:#F7BE17`).
- **Toggle chips:** category filter.

### 5.4 Navigation components
- **Bottom nav (mobile):** floating dark bar, 4–5 items, active = amber icon + amber label (no filled pill background — color-only). Two variants: candidate (5 tabs), employer (4 tabs).
- **Sidebar (web dashboard):** dark, logo, nav items with active amber fill + badge counts, upgrade card, account footer.
- **Sticky top header:** blurred translucent bar; web site header (nav links + CTA), web dashboard topbar (title + search + bell + post CTA), mobile floating header (location pill + search/notification pills).
- **Segmented tabs:** admin (overview/jobs/reports), applicant filters.
- **Breadcrumb:** website (Terms/About) — home ▸ current.

### 5.5 Overlays
- **Modal (centered):** post-job (web), with sticky header + sticky footer actions, backdrop dim + `sheetIn`.
- **Bottom sheet (mobile):** WhatsApp pre-filled message sheet; advanced filters sheet; confirm-stop dialog.
- **Drawer (side):** web job detail drawer (`Zeno-Jobs`), slides from side, dark hero + scroll body + sticky apply bar.
- **Toast:** top-center charcoal pill, success tick, auto-dismiss ~2.2s.

### 5.6 Indicators / misc
- **Status badge:** pill, semantic color pairs.
- **Verified badge:** `verify` icon, blue.
- **Distance badge:** amber-tint pill with location icon.
- **Unread dot:** amber pulsing dot.
- **Step tracker:** 3-node horizontal progress (applications).
- **Bar chart:** overview "applications this week" (CSS-height bars, amber highlight on peak days). Admin also uses simple stat visualizations.
- **Avatar:** initial-letter tinted circle/rounded tile; stacked avatar group (landing social proof).
- **Empty state:** icon tile + title + subtitle + reset CTA.

---

## 6. Navigation architecture

### 6.1 Mobile app (state machine, not URLs) [CONFIRMED]
- Single `screen` state string drives which screen renders. A `history[]` stack powers **back**.
- **Role switch** resets to the role's home: candidate→`browse`, employer→`emp_home`, admin→`admin`.
- **Candidate bottom nav (5):** `home`(→browse) · `nearby` · `apps`(طلباتي) · `messages` · `profile`. Icons: `home-2, gps, document-text-1, messages-2, user-1`.
- **Employer bottom nav (4):** `jobs`(→emp_home) · `applicants` · `messages` · `account`.
- **Detail/stacked screens** (pushed onto history, no bottom-nav tab): `job`, `submitted`, `chat`, `emp_post`, `applicant`, `notifications`, `search`, `register_candidate`, `register_employer`, `otp`, `phone`.
- **Auth flow order:** `loading` → `onboarding` → `splash`(role pick) → `phone` → `otp` → `register_*` → role home.
- **Global overlays** (render above any screen): WhatsApp sheet, filters sheet, confirm-stop dialog, toast.

### 6.2 Employer web dashboard [CONFIRMED]
- Persistent **left sidebar** (RTL: right side) with 4 views: overview, jobs, applicants, messages; badges on applicants(3)/messages(2).
- **Topbar:** page title+subtitle, global search input, notification bell (with dot), "نشر وظيفة" primary CTA (opens post modal).
- View switching is in-page (`view` state), animated with `fadeUp`.

### 6.3 Public website [CONFIRMED]
- **Header (sticky, blurred):** logo (→landing), nav links (المميزات/كيف يعمل/الوظائف/لأصحاب العمل/الأسئلة on landing; الرئيسية/الوظائف/من نحن/تواصل on subpages), "لوحة التحكم"/"دخول أصحاب العمل" link (→web dashboard), "حمّل التطبيق" CTA.
- **In-page anchors** on landing (`#features #how #jobs #employers #faq #download #top`) with smooth scroll.
- **Cross-links:** landing ⇄ jobs ⇄ company; landing → pricing/about/contact/terms; footer link groups (المنتج / أصحاب العمل / الشركة).
- **Breadcrumbs** on Terms (and About where relevant).
- **Footer:** logo, link columns, socials (globe/message-text/send-2), copyright "© 2026 زينو".

### 6.4 Context menus / tooltips [CONFIRMED]
- No right-click context menus. Tooltips are `title` attributes on icon-only buttons (edit, close, home). No custom tooltip component exists.

---

## 7. Mobile app — screen-by-screen

> User type unless noted: authenticated. **Permissions:** the prototype does not gate screens by permission beyond **role** (candidate/employer/admin) and **auth state**. All `[permission]` notes are role/auth only unless flagged `[INFERRED]`.

### 7.1 Loading (`loading`)
- **Purpose:** brand splash while app boots.
- **Route [INFERRED]:** app launch, no route.
- **Nav path:** entry point → auto-advances to `onboarding` after ~2.1s.
- **User type:** any (pre-auth).
- **Components:** centered **icon mark** (`zeno-mark.png`) with `breathe` animation + rotating spinner ring.
- **States:** loading only. **Edge case:** if boot/auth check is slow, spinner persists ([INFERRED] add timeout/error fallback).

### 7.2 Onboarding (`onboarding`)
- **Purpose:** 3-slide intro to the app's value (large animated vectors + service types).
- **Nav path:** after loading → onboarding → `splash`.
- **Components:** slide illustration, title, description, **3 pagination dots** (active dot widens to 22px, amber), **Next** (`onbNext`) and **Skip** (`onbSkip`); tapping a dot jumps (`onbGo`).
- **Interactions:** Next advances; on last slide Next → splash. Skip → splash anytime.
- **States:** 3 content states (indices 0–2). No error/empty.

### 7.3 Splash / role picker (`splash`)
- **Purpose:** entry choice — sign in as candidate or employer (admin has a hidden/again-inferred entry).
- **Components:** full **logo**, tagline, glow + dotted background, **role CTAs**: candidate (`goCandidate`) and employer (`goEmployer`), each → `phone` after setting role. Admin path (`goAdmin`) exists in logic.
- **Interactions:** picking a role sets `role` and pushes `phone`.
- **States:** static.

### 7.4 Phone (`phone`)
- **Purpose:** capture Saudi mobile number.
- **Components:** logo (top), amber **vector medallion**, phone input (numeric, max 10 digits, `onPhone` strips non-digits), continue button (`goOtp`).
- **Validation [CONFIRMED]:** input restricted to digits, max length 10. **[INFERRED]:** must be a valid Saudi mobile (05XXXXXXXX / +9665XXXXXXXX). No server check in prototype.
- **States:** default; **[INFERRED]** invalid-number error, rate-limit error, loading while requesting OTP.

### 7.5 OTP (`otp`)
- **Purpose:** verify the number with a **4-digit** code.
- **Components:** 4 single-digit boxes (auto-advance on entry, `otpDigit`), a "fill demo OTP" helper (`fillOtp` → `4829`), verify button (`verifyOtp`), (implied) resend.
- **Interactions:** `verifyOtp` → routes to `register_employer` or `register_candidate` by role.
- **Validation [CONFIRMED]:** digits only, one per box. **[INFERRED]:** 4 digits required, correct code, expiry, resend cooldown, max attempts.
- **States:** default; **[INFERRED]** wrong-code error, expired, resend-cooldown, verifying (loading), locked-out.

### 7.6 Registration — candidate (`register_candidate`) & employer (`register_employer`)
- **Purpose:** complete profile after verification (also reused as **Edit profile** via `editProfile`).
- **Account type:** individual (فرد) vs organization (منشأة) toggle (`setAcct`, `acct`).
- **Candidate fields [CONFIRMED presence]:** name, age, nationality, city, job/title, years of experience, skills, brief. **Employer fields:** company name (+ org details).
- **Interaction:** `finishReg` → sets role home + success toast "تم إنشاء حسابك بنجاح".
- **States:** default / editing / **[INFERRED]** validation errors, saving, save-failure.

### 7.7 Browse / home (`browse`) — Candidate
- **Purpose:** primary discovery feed.
- **Route [INFERRED]:** `/app/browse`.
- **Components:**
  - **Floating header (dark):** location pill (الرياض), search entry, notification bell (opens notifications), search shortcut (`goSearch`).
  - **Category chips row** (`activeCat`, `setCat`): "الكل" + 10 categories, selected = charcoal/amber.
  - **Job cards list** (`jobs`, filtered by category+query): tap → `selectJob` → `job`.
- **Empty state [CONFIRMED]:** when filters yield nothing → empty component.
- **States:** list / filtered / empty / **[INFERRED]** loading skeleton, fetch error.

### 7.8 Search (`search`) — Candidate
- **Purpose:** live text search + advanced filters.
- **Components:** dark sticky search header (`setQuery`), result count, **filters button** (`openFilters`, shows active-filter count badge), results list, empty state.
- **Filters sheet [CONFIRMED]:** work type (7 options), gender (men/women), nationality (saudi/non-saudi/all); `setF` toggles, `clearF` resets, apply/close.
- **Interactions:** typing filters instantly (title/company/district contains query); filters AND-combine with category and query.
- **States:** initial / typing / results / empty / filters-open.

### 7.9 Nearby / map (`nearby`) — Candidate
- **Purpose:** proximity-first discovery.
- **Components:** dark header, **radius selector** (1/3/5/10/20/50 km + "الكل", `setRadius`), map area with **pulsing location marker** + category pins, nearby list sorted by distance ascending.
- **Business rule [CONFIRMED]:** shows jobs with `distance ≤ radius`, sorted nearest-first; label "ضمن N كم" / "كل الرياض".
- **States:** list / empty (no jobs in radius) / **[INFERRED]** location-permission-denied, locating, map-load-error.

### 7.10 Job detail (`job`) — Candidate
- **Purpose:** full job info + apply.
- **Components:** header (back), category icon, title, company·district, salary/type/distance/gender/nationality/count meta, description, contact-method label, **Apply** button (`applyJob`), (bookmark implied).
- **Interaction — apply:** creates an application (`status:'submitted'`) unless one already exists for that job (dedupe by `who==='me' && jobId`), sets `lastReq`, pushes `submitted`.
- **States:** default / already-applied (no duplicate created) / **[INFERRED]** job-closed, loading, error.

### 7.11 Application submitted (`submitted`) — Candidate
- **Purpose:** confirmation.
- **Components:** success check (scale-in), request number (`lastReq`), CTA to track/continue.
- **States:** success only.

### 7.12 My applications (`apps` / طلباتي) — Candidate
- **Purpose:** track applications and status.
- **Components:** list of the user's applications with **status badge** and **3-step tracker** (submitted→review→accepted/rejected). Rejected shows red terminal node.
- **Status set [CONFIRMED]:** `submitted, review, new, accepted, rejected` (labels in `statusMeta`).
- **States:** list / empty (no applications) / **[INFERRED]** loading, error.

### 7.13 Messages list (`messages`) & Chat (`chat`) — Candidate
- **Messages:** list of conversations (`chats`) with last message + time + initial avatar; empty state when none. Tap → `openChat`.
- **Chat:** dark header (name + "role · طلب #"), message bubbles (mine=charcoal right, theirs=white left), input + send (`sendMsg`, appends locally, empty input ignored), **WhatsApp button** (`openWa`).
- **WhatsApp sheet [CONFIRMED]:** pre-filled message template — greeting, job title, request number, profile link (`app.zeno.sa/profile/<req>`), thanks — then deep-links to WhatsApp.
- **States:** list/empty; chat: conversation / sending / **[INFERRED]** delivery status, load history, error.

### 7.14 Profile (`profile`) — Candidate
- **Purpose:** view own profile + entry to edit, settings, logout.
- **Components:** dark header with name/role, profile fields (age, nationality, city, experience, skills, brief), **Edit profile** (`editProfile` → register screen), logout (`logout` → splash).
- **States:** view / **[INFERRED]** incomplete-profile prompt.

### 7.15 Notifications (`notifications`)
- **Purpose:** activity feed.
- **Components:** notification cards (icon tint, title, body, time, unread dot). 4 seed types: accepted, new message, matching job, application update. Unread count surfaces on the bell.
- **States:** list / (empty [INFERRED]) / read vs unread.

### 7.16 Employer — home / my jobs (`emp_home`)
- **Purpose:** employer's listing management.
- **Components:** dark header (company), **post-job CTA** (`goEmpPost`), employer job cards with **applicants count, new count, views [INFERRED number source], status (active/stopped)**, edit, **pause/activate** (`askToggle`).
- **Pause flow [CONFIRMED]:** pausing an active listing opens a **confirm dialog** (`confirmStopId` → `doStop`); re-activating is immediate. Toasts: "تم إيقاف الإعلان" / "تم تفعيل الإعلان".
- **States:** list / empty / confirm-open.

### 7.17 Employer — post job (`emp_post`)
- **Purpose:** create a listing (also edit).
- **Form fields [CONFIRMED]:** title (with "أخرى"/custom allowed), category (10), city, work type (7), salary + unit (monthly/weekly/daily/hourly), gender (all/men/women), nationality (all/saudi/non-saudi), vacancies count, description, contact method (app/whatsapp/both).
- **Readiness rule [CONFIRMED]:** submit enabled only when `title && cat && type && salary` (`postReady`). Otherwise **disabled**.
- **Submit:** `postJob` prepends the new job (company = employer, distance seeded 0.5, posted "الآن"), resets the form, returns to `emp_home`, toast "تم نشر الإعلان بنجاح".
- **States:** empty form / partial (submit disabled) / ready / submitting [INFERRED] / success.

### 7.18 Employer — applicants (`emp_applicants`) & applicant detail (`applicant`)
- **Applicants:** dark header, applicant rows for the employer's jobs; **new/review = pending** (show accept/reject), **accepted/rejected = decided** (show message). `empDecide` sets status + toast. New-count badge.
- **Applicant detail:** name, role, city, years, request #, **skills** (role-based), brief, profile URL (`app.zeno.sa/profile/<req>`), accept/reject/message actions.
- **States:** list / empty / pending vs decided per row.

### 7.19 Employer — messages/chat & account
- Same chat component as candidate (employer side). **Account** = employer profile/edit + logout.

### 7.20 Admin (`admin`) — 3 segmented tabs
- **Overview (`overview`):** highlight banner + platform stat cards (`adminStats`, `adminSections`).
- **Jobs (`jobs`):** oversight list of all jobs (`adminJobs` = all jobs decorated).
- **Reports (`reports`):** report items (`adminReports`) — moderation/flagged content queue.
- **Permissions [INFERRED]:** admin-only; the prototype does not implement real access control.

---

## 8. Employer web dashboard — screen-by-screen (`Zeno-Web.dc.html`)

- **User type:** Employer. **Route [INFERRED]:** `/dashboard/*`.
- **Global chrome:** dark **sidebar** (logo, nav, upgrade card, account footer, home link), **topbar** (title/subtitle, search, bell+dot, "نشر وظيفة" CTA), **toast**, **post-job modal**.

### 8.1 Overview
- **Purpose:** at-a-glance activity.
- **Components:** 4 **stat cards** (active jobs, new applications, total views, acceptance rate — each with icon, value, delta badge); **bar chart** "الطلبات هذا الأسبوع" (7 days, amber highlights, "+18% vs last week"); **quick actions** (post job → modal; review applicants → applicants; messages → messages); **recent applicants** list → applicants.
- **States:** populated / **[INFERRED]** empty (no data), loading.

### 8.2 Jobs
- **Purpose:** manage listings.
- **Components:** 2-col job cards: category tile, title + **status badge** (نشط/متوقف), type·area·posted, salary; footer stats (applicants, views), **edit** icon, **pause/activate** toggle (`toggleJob`, immediate, badge + button flip).
- **States:** grid / **[INFERRED]** empty, loading.

### 8.3 Applicants
- **Purpose:** review + decide.
- **Components:** **filter chips** (all/new/accepted/rejected, `setFilter`), applicant rows (initial avatar, name, status badge, role·city·years·request#), **accept**(`acceptApp`)/**reject**(`rejectApp`) for pending, **message** for decided. Toasts on decision.
- **States:** filtered list / empty (filter yields none).

### 8.4 Messages
- **Purpose:** two-pane conversation UI.
- **Components:** conversation list (left/RTL-right; active highlighted), thread pane (header with avatar + "role · طلب #" + **WhatsApp** button, bubble history, input + send).
- **States:** conversation selected / **[INFERRED]** no-conversation, loading, sending.

### 8.5 Post-job modal
- **Purpose:** create a listing from the web.
- **Fields [CONFIRMED]:** title, category (select), work type (select), salary, vacancies count, city, description (textarea), **contact method** (in-app selected vs WhatsApp).
- **Actions:** cancel (`closePost`), **submit** (`submitPost` → close + toast "تم نشر الوظيفة بنجاح"). Backdrop click closes; inner click `stop`s propagation.
- **States:** open/closed; **[INFERRED]** field validation, submitting, error.

---

## 9. Public website — screen-by-screen

### 9.1 Landing (`Zeno-Landing.dc.html`)
- **Purpose:** convert seekers (app download) and employers (dashboard).
- **Sections [CONFIRMED]:** sticky header; **hero** (badge, H1, subtext, App Store/Google Play buttons, social-proof avatars + 4.9 rating, **phone mockup** with two floating notification cards); **stats bar** (12k+ jobs, 8k+ seekers, 3k+ businesses, 48h avg hire); **categories** (6 cards → jobs); **how it works** (3 steps); **features** (big map feature + 3 cards: WhatsApp, one-tap apply, verified businesses); **employers** (dark panel, CTA → dashboard, 3 points); **live jobs** (4 recent → detail); **download** (amber panel, store buttons, QR-style tile); **FAQ** (accordion, `toggleFaq`, one open at a time, +/− sign); **footer** (logo, 3 link columns, socials, "صُنع في السعودية").
- **Interactions:** anchor scroll; FAQ accordion; store/CTA links.
- **States:** static; FAQ open/closed per item.

### 9.2 Jobs browse + detail (`Zeno-Jobs.dc.html`)
- **Purpose:** desktop job search.
- **Components:** header; **dark search bar** (query `onQuery` + location + search button); **filters sidebar** (sticky): work-type **checkboxes** (with counts) `toggleType`, category **chips** `toggleCat`, **distance range slider** `onDist` (1–20km), "مسح الكل" `clearFilters`; **results header** (count + sort select); **job cards** (verified badge, type/distance/posted chips, salary, "التفاصيل") → `openJob`; **empty state** with reset.
- **Detail = side drawer** (`detailOpen`): dark hero (company link → company page, verified), 3 stat tiles (salary/type/distance), description, **requirements** (checklist), **perks** (chips), location card, sticky footer **bookmark + "قدّم عبر التطبيق"** (→ app download).
- **Filter logic [CONFIRMED]:** AND of query (title/company contains) + selected types + selected categories + `distance ≤ dist`. Empty → empty state.
- **States:** results / empty / drawer-open.

### 9.3 Pricing (`Zeno-Pricing.dc.html`)
- **Purpose:** employer packages/tiers + comparison + FAQ. **User:** public/employer. Cards with tiers, feature lists, one featured tier, CTA to dashboard/signup. (Tier names, prices, limits are prototype content — **[INFERRED]** actual commercial terms.)

### 9.4 About (`Zeno-About.dc.html`)
- Company story, mission, values, stats/team. Static marketing.

### 9.5 Company profile (`Zeno-Company.dc.html`)
- **Purpose:** a single employer's public page — header/cover, verified badge, about, and **its open jobs**. Linked from the Jobs detail drawer's company name. Static/prototype data.

### 9.6 Contact (`Zeno-Contact.dc.html`)
- **Purpose:** contact form + channels. Fields [CONFIRMED presence]: name, email, subject/message; channel cards (email/phone/social). **[INFERRED]:** submission endpoint, validation, success/error.

### 9.7 Terms & Privacy (`Zeno-Terms.dc.html`)
- Sticky **TOC** sidebar (6 sections: intro, accounts & usage, employer obligations, privacy & data, WhatsApp comms, liability), anchored content, "last updated" date, contact CTA. Static legal content — **must be reviewed by legal counsel [INFERRED]**.

---

## 10. Admin panel

> The only admin surface in the prototype is inside the **mobile app** (`admin` screen, 3 tabs). There is **no dedicated admin web console** yet.

| Tab | Purpose | Components | Capabilities (visible) |
|---|---|---|---|
| Overview | Platform health | Highlight banner, stat cards | Read-only metrics |
| Jobs | Listing oversight | All-jobs list (decorated) | View all jobs across employers |
| Reports | Moderation | Report items list | Review flagged items |

**Management capabilities that are implied but NOT built [INFERRED / see §20]:** user management, employer verification approval, job takedown, report resolution actions, role/permission management, content moderation workflow, audit log. None of these have interactions in the prototype.

---

## 11. Complete user journeys

### 11.1 Candidate — first run → hired
1. Launch → **loading** → **onboarding** (3 slides, may Skip) → **splash**.
2. Tap "candidate" → **phone** (enter 05…) → continue → **OTP** (enter 4 digits / demo 4829) → verify.
3. **register_candidate** (name, age, nationality, city, job, years, skills, brief; account type فرد) → finish → toast → **browse**.
4. **browse**: filter by category or open **search** (type query, open **filters**: type/gender/nationality) or **nearby** (set radius, view map).
5. Open a **job** → read details → **Apply** → **submitted** (request #).
6. **apps/طلباتي**: watch status move submitted→review→accepted.
7. On accept → **notification** + **messages** → **chat** with employer, or **WhatsApp** handoff (pre-filled).
8. **profile** → edit anytime; **logout** → splash.

### 11.2 Employer — onboard → post → hire
1. splash → "employer" → phone → OTP → **register_employer** (company; account type منشأة) → **emp_home**.
2. **Post job** (`emp_post`): fill title/category/type/salary (+ optional gender/nationality/count/desc/contact) → submit (enabled only when required 4 present) → toast → listing appears.
3. **emp_applicants**: review new applicants → **accept**/**reject** (toast) → **message** decided applicants (in-app or WhatsApp).
4. **emp_home**: **pause** a listing (confirm dialog) or **activate**; **edit** a listing.
5. **account**: edit profile / logout.

### 11.3 Employer — via web dashboard
1. Website "لوحة التحكم"/"دخول أصحاب العمل" → **dashboard/overview**.
2. Review stats + chart + recent applicants → quick action **post job** (modal) → submit → toast.
3. **Jobs** → pause/activate/edit. **Applicants** → filter + accept/reject/message. **Messages** → chat + WhatsApp.

### 11.4 Public visitor
Landing → explore sections → either **حمّل التطبيق** (candidate) or **لأصحاب العمل → dashboard/pricing** (employer). Or **Jobs** page → filter → open detail drawer → **قدّم عبر التطبيق** / view **company** page. Footer → About/Contact/Terms.

### 11.5 Journeys NOT designed [INFERRED — see §20]
Password/account recovery, change phone number, delete account, share a job (no share UI exists), granular notification settings, in-app payments/subscription checkout, employer verification submission.

---

## 12. Screen states matrix

Legend: ✅ designed · ➖ n/a · ⚠️ **inferred/needs design**

| Screen | Default | Empty | Loading | Success | Error | Offline | No-permission | Partial data |
|---|---|---|---|---|---|---|---|---|
| Loading | ✅ | ➖ | ✅ | ➖ | ⚠️ | ⚠️ | ➖ | ➖ |
| Onboarding | ✅ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ |
| Phone | ✅ | ➖ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ➖ |
| OTP | ✅ | ➖ | ⚠️ | ✅(→reg) | ⚠️ | ⚠️ | ➖ | ➖ |
| Registration | ✅ | ➖ | ⚠️ | ✅(toast) | ⚠️ | ⚠️ | ➖ | ⚠️ |
| Browse | ✅ | ✅ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ⚠️ |
| Search | ✅ | ✅ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ➖ |
| Nearby/Map | ✅ | ✅ | ⚠️ | ➖ | ⚠️ | ⚠️ | ⚠️(location) | ⚠️ |
| Job detail | ✅ | ➖ | ⚠️ | ✅(apply) | ⚠️ | ⚠️ | ⚠️(closed) | ➖ |
| Submitted | ✅ | ➖ | ➖ | ✅ | ➖ | ➖ | ➖ | ➖ |
| My applications | ✅ | ✅ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ➖ |
| Messages/Chat | ✅ | ✅ | ⚠️ | ✅(send) | ⚠️ | ⚠️ | ➖ | ⚠️ |
| Profile | ✅ | ➖ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ⚠️ |
| Notifications | ✅ | ⚠️ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ➖ |
| Emp home/jobs | ✅ | ✅ | ⚠️ | ✅(toggle) | ⚠️ | ⚠️ | ➖ | ➖ |
| Post job | ✅ | ✅ | ⚠️ | ✅(toast) | ⚠️ | ⚠️ | ➖ | ✅(disabled) |
| Emp applicants | ✅ | ✅ | ⚠️ | ✅(decide) | ⚠️ | ⚠️ | ➖ | ➖ |
| Admin (3 tabs) | ✅ | ⚠️ | ⚠️ | ➖ | ⚠️ | ⚠️(access) | ⚠️ | ⚠️ |
| Web overview | ✅ | ⚠️ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ⚠️ |
| Web jobs page | ✅ | ✅ | ⚠️ | ➖ | ⚠️ | ⚠️ | ➖ | ➖ |
| Contact form | ✅ | ➖ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ➖ | ➖ |

**Takeaway:** loading/error/offline states are **largely undesigned** across the product and must be added before build (see §20).

---

## 13. Forms reference

### 13.1 Phone (auth) [CONFIRMED fields / INFERRED rules]
| Field | Type | Required | Validation (confirmed) | Validation (inferred) | Error msg |
|---|---|---|---|---|---|
| Mobile number | tel/numeric | Yes | digits only, ≤10 | valid Saudi mobile; not blocked | ⚠️ TBD |

### 13.2 OTP
| Field | Type | Required | Confirmed | Inferred |
|---|---|---|---|---|
| 4× digit | numeric | Yes | 1 digit/box, auto-advance | code correct, ≤N attempts, expiry, resend cooldown |

### 13.3 Candidate registration
Fields: name (text), age (number), nationality (select/text), city (select), job/title (text), years of experience (number), skills (multi/tags), brief (textarea), account type (فرد/منشأة toggle). **Required set: [INFERRED]** (prototype does not enforce). Success: toast + route home.

### 13.4 Employer registration
Fields: company name (text) + org details, account type (منشأة). **Required/verification: [INFERRED]**.

### 13.5 Post job (app + web) [CONFIRMED]
| Field | Type | Required | Notes |
|---|---|---|---|
| Title | text | ✅ | "أخرى"/custom allowed |
| Category | select (10) | ✅ | |
| Work type | segmented (7) | ✅ | full/part/daily/weekly/seasonal/temp/hourly |
| Salary | text/number | ✅ | |
| Unit | segmented | — | monthly/weekly/daily/hourly (default monthly) |
| Gender | segmented | — | all/men/women (default all) |
| Nationality | segmented | — | all/saudi/non-saudi (default all) |
| Vacancies | number | — | default 1 |
| Description | textarea | — | |
| Contact method | segmented | — | app/whatsapp/both (default app) |

**Rule:** submit enabled only when `title && category && type && salary`. **Error/success messages [INFERRED]** beyond the success toast.

### 13.6 Contact form [CONFIRMED fields]
Name, email, subject, message. Validation/success/error: **[INFERRED]**.

### 13.7 Filters (candidate search) & (web jobs)
Not a submit-form; live-applied. Candidate: type/gender/nationality. Web: type (checkbox+counts), category (chips), distance (slider). "مسح الكل" resets.

---

## 14. Business logic & rules

> Rules marked [CONFIRMED] are enforced in the prototype. Others are [INFERRED] and need PM sign-off.

1. **Proximity ranking [CONFIRMED]:** Nearby shows `distance ≤ radius`, sorted ascending. Radius presets 1/3/5/10/20/50/all km. **[INFERRED]:** distance is computed from user geolocation vs job location (needs geocoding + distance service).
2. **Search/filter composition [CONFIRMED]:** browse = category filter; search = text (title/company/district contains) AND type/gender/nationality; web jobs = query AND types AND categories AND distance. All AND-combined, client-side in prototype.
3. **Apply dedupe [CONFIRMED]:** a candidate can hold only one application per job (`who==='me' && jobId`); re-applying does not duplicate, reuses existing request number.
4. **Application status lifecycle [CONFIRMED labels]:** `submitted → review → accepted | rejected` (candidate view uses a 3-step tracker; `new` is the employer-side inbound label mapping to step 0). **[INFERRED]:** who/what triggers `review`, SLA, reopen.
5. **Employer decision [CONFIRMED]:** accept/reject sets status + toast; decided applicants become messageable.
6. **Listing pause/activate [CONFIRMED]:** pausing needs confirmation; activating is immediate. **[INFERRED]:** paused jobs should be hidden from candidate discovery (not enforced in prototype).
7. **Contact channel [CONFIRMED]:** per-job contact = app / whatsapp / both; WhatsApp opens a **pre-filled** message (greeting + job + request# + profile link). **[INFERRED]:** the profile link, phone masking, opt-in.
8. **No fees to seekers [CONFIRMED as stated in Terms/marketing]:** employers must not request fees from applicants.
9. **Verification [CONFIRMED visual]:** businesses can show a verified badge. **[INFERRED]:** the verification process, criteria, who approves (admin), and gating.
10. **Account type [CONFIRMED toggle]:** individual vs organization at signup. **[INFERRED]:** what changes downstream (can individuals post jobs? probably employer-only).
11. **OTP demo [CONFIRMED]:** prototype accepts `4829`/any 4 digits. Production must validate a real code.
12. **Notifications [CONFIRMED types]:** acceptance, new message, job match, application update. **[INFERRED]:** delivery (push/SMS/in-app), triggers, preferences.
13. **Failure scenarios [INFERRED, undesigned]:** network loss, OTP failures, geolocation denial, job closed mid-apply, concurrent accept when vacancies exhausted, duplicate phone numbers, employer editing a job with active applicants.

---

## 15. Interaction → API map

> **All endpoints below are [INFERRED] proposals** to guide backend design. Shapes/paths are recommendations, not commitments. Every one currently runs against **client mock state**.

| Interaction (confirmed UI) | Proposed API | Validation | Success UI | Error UI (to design) |
|---|---|---|---|---|
| Continue after phone | `POST /auth/otp/request {phone}` | valid Saudi mobile | → OTP screen | invalid/rate-limit |
| Verify OTP | `POST /auth/otp/verify {phone,code}` | 4-digit, valid, unexpired | → registration/home + session | wrong/expired/locked |
| Finish registration | `POST /users/me` or `PATCH` | required fields | toast + home | validation/save error |
| Load browse | `GET /jobs?category=&q=` | — | list | empty / error |
| Search | `GET /jobs?q=&type=&gender=&nationality=` | — | filtered list | empty / error |
| Nearby | `GET /jobs?lat=&lng=&radiusKm=` (sorted) | geolocation granted | sorted list | permission/empty/error |
| Open job | `GET /jobs/{id}` | — | detail | not-found/closed |
| Apply | `POST /jobs/{id}/applications` | not already applied; job open | submitted screen + request# | duplicate/closed/error |
| My applications | `GET /users/me/applications` | — | list+status | empty/error |
| Employer applicants | `GET /jobs/{id}/applications` or `/employers/me/applications` | employer owns job | list | empty/error |
| Accept/Reject | `POST /applications/{id}/decision {accepted}` | employer owns; still pending | toast + status | conflict/error |
| Post job | `POST /jobs` | title,category,type,salary required | toast + listing | validation/error |
| Edit job | `PATCH /jobs/{id}` | employer owns | updated | error |
| Pause/Activate | `PATCH /jobs/{id}/status {active}` | employer owns | badge flip + toast | error |
| Send message | `POST /conversations/{id}/messages {text}` | non-empty | bubble appended | send-failure/retry |
| Load conversation | `GET /conversations/{id}/messages` | participant | thread | error |
| WhatsApp handoff | client deep-link `https://wa.me/?text=...` | consent | opens WhatsApp | app-not-installed |
| Notifications | `GET /users/me/notifications` | — | list + unread count | empty/error |
| Contact form | `POST /contact` | name,email,message | success msg (TBD) | validation/error |
| Admin overview/jobs/reports | `GET /admin/*` | admin role | data | no-permission/error |

---

## 16. Backend requirements (inferred)

> Entirely **[INFERRED]** from the UI. Confirm with PM before schema freeze.

### 16.1 Core entities
- **User** (id, phone, role[candidate|employer|admin], accountType[individual|organization], createdAt, status).
- **CandidateProfile** (userId, name, age, nationality, city, jobTitle, yearsExperience, skills[], brief, profileUrl).
- **EmployerProfile / Organization** (userId, companyName, verified[bool], about, cover, location).
- **Job** (id, employerId, title, category, companyName, district/location{lat,lng}, type, salary, unit, gender, nationality, vacanciesCount, distance(derived), postedAt, contactMethod, description, status[active|stopped|closed], views).
- **Application** (id, jobId, candidateId, requestNumber, status[submitted|review|accepted|rejected], channel, createdAt).
- **Conversation** (id, jobId?, participants[], lastMessage, lastTime).
- **Message** (id, conversationId, from, text, time, deliveryStatus).
- **Notification** (id, userId, type, title, body, time, unread).
- **Report** (id, targetType, targetId, reason, status) — admin.
- **Category** (id, label, icon) — 10 fixed seed values (see §4.6 / cats list).

### 16.2 Relationships
- User 1–1 Candidate/Employer profile (by role). Employer 1–N Jobs. Job 1–N Applications. Candidate 1–N Applications (≤1 per Job). Job/Application ↔ Conversation ↔ Messages. User 1–N Notifications.

### 16.3 Auth & authz
- Phone + OTP authentication; session/JWT. Role-based access (candidate/employer/admin). Ownership checks (employer only manages own jobs/applications). **Admin gating undefined [§20].**

### 16.4 Cross-cutting
- **Search & filters:** server-side query by text, category, type, gender, nationality; pagination (lists are unbounded in prototype — add pagination/infinite scroll).
- **Geo:** geolocation capture, job geocoding, distance calc + radius query, map tiles/provider.
- **Uploads [INFERRED]:** avatars/logos/attachments are not in the prototype but likely (profile images, CVs, company logos) — confirm.
- **Notifications:** push (mobile), possibly SMS; triggers on status change / new message / job match.
- **WhatsApp:** deep-link composition; phone privacy/masking policy.
- **Pagination/sorting:** sort options shown on web jobs (nearest/newest/highest salary) require server support.

---

## 17. Mobile requirements

- **Platform:** iOS + Android (App Store & Google Play badges on landing).
- **Language/direction:** Arabic, **RTL only** (per confirmed scope). All layouts, gestures, and icons mirror RTL.
- **Navigation:** role-based bottom nav (5 candidate / 4 employer); back stack; floating translucent header; global overlays (sheets, dialogs, toasts).
- **Device chrome:** designed within a phone frame; respect safe areas (status bar white over dark headers — `statusColor` flips per screen), home indicator.
- **Key native capabilities [INFERRED]:** geolocation (nearby/map), push notifications, WhatsApp deep-linking, OTP autofill/SMS retriever, camera/gallery if uploads are added.
- **Offline/resilience [INFERRED, undesigned]:** loading skeletons, retry, cached last state, empty/error screens per §12.
- **Performance:** transform-only entrance animations; lists need virtualization + pagination at scale.
- **Persistence:** timed/media content should persist position (general app rule); auth session persists login.

---

## 18. Web requirements

- **Two web surfaces:** public marketing site + employer dashboard.
- **Language/direction:** Arabic, RTL. Max content width 1080–1240px, centered.
- **Public site:** SEO-friendly marketing pages, anchor navigation + smooth scroll, FAQ accordion, cross-page links, footer. Jobs page = filterable list + detail **drawer**; applying routes candidates to the **app** ("قدّم عبر التطبيق").
- **Dashboard:** authenticated employer app — sidebar + topbar shell, overview analytics (stat cards + bar chart), jobs management (pause/activate/edit), applicants (filter + accept/reject/message), two-pane messaging, post-job modal, toasts.
- **Auth:** employer login via the same phone/OTP identity [INFERRED for web].
- **States [INFERRED, undesigned]:** dashboard empty/loading/error; form validation in the post-job modal and contact form.

---

## 19. Responsive behavior

> **Important [CONFIRMED constraint]:** the prototype defines **fixed target widths per surface**, not fluid breakpoints. Responsive rules below are the **design intent**; most are **[INFERRED]** and must be implemented/verified.

| Surface | Mobile | Tablet | Desktop |
|---|---|---|---|
| **Mobile app** | ✅ native target (single column, ~390–480px) | ⚠️ scale/letterbox or adapt (undesigned) | ⚠️ n/a (app is phone-first) |
| **Landing** | ⚠️ hero grid (1.05fr/0.95fr), 3-col cards, 4-col stats must **collapse to 1–2 col**; phone mockup stacks below copy | ⚠️ 2-col | ✅ designed at ~1180px |
| **Jobs page** | ⚠️ filters sidebar (262px) must become a **top sheet/collapsible**; detail **drawer** should become full-screen | ⚠️ | ✅ designed (262px sidebar + list) |
| **Dashboard** | ⚠️ sidebar (262px) must become a **drawer/bottom nav**; 4-col stats → 1–2 col; 2-pane messages → list↔thread | ⚠️ | ✅ designed |
| **Pricing/About/Contact/Terms** | ⚠️ multi-col → single col; Terms TOC → top accordion | ⚠️ | ✅ designed |

**Confirmed responsive-friendly patterns already used:** flex/grid with `gap`, `flex-wrap` on button rows and footer, `max-width` containers, `text-wrap: balance/pretty`. **Not yet defined:** explicit breakpoints, mobile web layouts for dashboard/jobs, tablet behavior. **→ needs a responsive design pass (see §20).**

---

## 20. Missing Functional Requirements

> Requirements that **cannot be determined from the UI alone**. Nothing here is invented functionality — each item is either implied-but-unspecified or entirely absent. For each: **why it can't be inferred** + **questions to answer before development**.

### A. Authentication & account lifecycle
1. **OTP rules** — *Why:* prototype accepts any/`4829`; length is 4 but expiry, attempt limits, resend cooldown, lockout are absent. *Ask:* code length/expiry? max attempts? resend cooldown? SMS provider? OTP autofill?
2. **Phone validation & uniqueness** — *Why:* only digit-stripping/≤10 is enforced. *Ask:* exact Saudi formats accepted? one account per number? number change flow?
3. **Account recovery / change number / delete account / logout-everywhere** — *Why:* no such screens exist (only simple logout). *Ask:* is recovery needed (OTP-only may suffice)? account deletion (Terms mentions "request deletion" but no UI)? data retention?
4. **Session & token policy** — *Why:* no session UI. *Ask:* JWT lifetime, refresh, multi-device, forced logout.

### B. Roles, permissions & account type
5. **Account type effect** — *Why:* فرد/منشأة is a toggle with no visible consequence. *Ask:* can individuals post jobs, or is posting employer-only? Different verification/limits?
6. **Admin access & permissions** — *Why:* admin screens are read-only mock tabs with no auth gate or actions. *Ask:* who is admin? how do they log in (same OTP? separate console?)? what actions (approve verification, take down jobs, resolve reports, manage users)? permission granularity? audit logging?

### C. Employer verification & trust
7. **Verification workflow** — *Why:* verified badge is shown but no submission/approval UI exists. *Ask:* what documents/criteria? who approves (admin)? does unverified block posting? SLA?

### D. Jobs & applications
8. **Application status transitions** — *Why:* labels exist; triggers for `review` and timing are unspecified. *Ask:* what moves submitted→review? auto or manual? SLAs? can employer reopen a rejected app? expiry?
9. **Vacancies & closing logic** — *Why:* `count` exists but nothing decrements or closes a job. *Ask:* does filling all vacancies auto-close? can candidates apply to a full/paused job? closed-job state design?
10. **Paused-job visibility** — *Why:* pause flips a badge but prototype still lists the job. *Ask:* should paused jobs be hidden from discovery/search/map immediately?
11. **Job editing constraints** — *Why:* edit reuses the create form. *Ask:* can core fields change after applicants exist? notify applicants of changes?
12. **Duplicate/spam & content moderation** — *Why:* no validation on job content. *Ask:* moderation rules, banned content, reporting-to-action flow (Reports tab has no actions).

### E. Discovery, geo & search
13. **Distance/geolocation source** — *Why:* `distance` is hardcoded mock data. *Ask:* map provider? geocoding? live user location vs chosen city? offline/denied-permission behavior? distance units/rounding?
14. **Search relevance & pagination** — *Why:* client-side "contains" match, unbounded lists. *Ask:* server search semantics (fuzzy/synonyms/Arabic normalization)? pagination/infinite scroll? sort definitions (nearest/newest/highest salary)?

### F. Messaging & WhatsApp
15. **Messaging backend** — *Why:* messages are local, no delivery/read states or history load. *Ask:* real-time transport (websocket/push)? read receipts? attachments? moderation? blocking?
16. **WhatsApp handoff details** — *Why:* pre-filled template + `app.zeno.sa/profile/<req>` link are mock. *Ask:* is the profile URL public? phone privacy/masking? consent? which number is used?

### G. Notifications
17. **Notification delivery & preferences** — *Why:* only an in-app list with 4 seed items; no settings screen. *Ask:* channels (push/SMS/in-app)? exact triggers? per-type opt-in? read/clear actions?

### H. Payments / monetization
18. **Pricing & billing** — *Why:* Pricing page tiers/prices are placeholder marketing; no checkout, no subscription state, no "upgrade" flow (sidebar "ترقية الحساب" is a dead CTA). *Ask:* real tiers/prices? payment provider? what's gated by tier (posting limits, priority, verification)? invoicing/VAT?

### I. Uploads & media
19. **File uploads** — *Why:* no upload UI exists, but profiles/companies/CVs likely need images/docs. *Ask:* are avatars/logos/CV/attachments required? formats/size limits? storage/CDN?

### J. Sharing & content
20. **Share a job** — *Why:* no share affordance anywhere. *Ask:* is job sharing (deep link, social) in scope?
21. **Company profiles & content data** — *Why:* company page and marketing stats (12k+, 4.9, etc.) are placeholder. *Ask:* real data sources? who edits company profiles? CMS for marketing/blog?

### K. Localization, legal, compliance
22. **RTL/Arabic scope** — *Why:* scope says Arabic-only; no i18n framework. *Ask:* will English ever be needed? number/date/currency (SAR) formatting, Hijri dates?
23. **Legal content** — *Why:* Terms/Privacy text is drafted design copy. *Ask:* must be replaced/approved by legal; PDPL (Saudi data protection) compliance, consent, data residency?

### L. States, resilience & responsive
24. **Loading/error/offline states** — *Why:* largely undesigned (see §12). *Ask:* standard skeletons, error/retry patterns, offline behavior, timeouts.
25. **Responsive/mobile-web layouts** — *Why:* web surfaces are designed at fixed desktop widths (see §19). *Ask:* target breakpoints? mobile-web dashboard/jobs layouts? tablet behavior? Is dashboard expected on mobile web or app-only?
26. **Analytics/metrics definitions** — *Why:* overview/admin show views, acceptance rate, "+18%", 48h avg hire as mock numbers. *Ask:* exact metric definitions, time windows, and event tracking needed to compute them.

---

### Confirmed vs. assumption — summary
- **Confirmed (authoritative):** visual design, layout, copy, color/type/spacing tokens, screen inventory, navigation structure, the client-side interactions and rules explicitly listed as [CONFIRMED], and the form field sets.
- **Assumptions (not authoritative):** every API, entity schema, permission model, validation threshold, metric definition, responsive breakpoint, and any rule tagged [INFERRED]. Resolve §20 before backend/mobile build begins.

*End of handover.*
