# Handoff: zeno — Local Job Marketplace (Mobile App + Web + Employer Dashboard)

## Overview
**zeno (زينو)** is a proximity-based local job marketplace for Saudi Arabia connecting job seekers with nearby employers (hospitality, retail, delivery, cleaning, events, etc.). It spans three surfaces: an **Arabic RTL mobile app** (candidate / employer / admin), a **public marketing website**, and an **employer web dashboard**. This bundle is the complete design reference for rebuilding the product in a real codebase.

## About the design files
The `*.dc.html` files in this bundle are **design references created in HTML** — interactive prototypes that show the intended look, layout, copy, and behavior. **They are not production code to copy directly.** The `.dc.html` format is a self-contained prototyping format (a small runtime, `support.js`, renders them in a browser).

**Your task:** recreate these designs in the target codebase's existing environment (React / Vue / SwiftUI / Flutter / native, etc.), using its established patterns, component library, and conventions. If no environment exists yet, choose the most appropriate stack for the product (the app is phone-first, Arabic RTL; the web surfaces are desktop-first responsive) and implement the designs there. Do **not** ship the HTML directly.

## Fidelity
**High-fidelity (hifi).** These are pixel-level mockups with final brand colors, Tajawal typography, spacing, radii, shadows, icons (Iconsax), copy, and interactions. Recreate the UI faithfully using your codebase's libraries. Exact tokens and per-screen detail are in **`HANDOVER.md`** (the full 20-section engineering handover included in this bundle).

## The authoritative spec: `HANDOVER.md`
**`HANDOVER.md` in this folder is the primary, self-sufficient specification.** It documents — grounded in the actual prototype files — every screen, component, interaction, form, business rule, user journey, state matrix, design token, and inferred backend requirement. Critically, it tags every statement as:
- **[CONFIRMED]** — implemented and visible in the prototype (authoritative for UI/UX).
- **[INFERRED]** — a backend/API/rule the UI implies but that is **not decided** (a proposal for you + the PM, not a commitment).

It ends with **Missing Functional Requirements** — 26 items across 12 categories, each with why it can't be inferred and the question to answer before build. **Read `HANDOVER.md` first; this README is the orientation layer on top of it.**

## Screens / Views (index — full detail in `HANDOVER.md` §7–§10)
**Mobile app (`Zeno.dc.html`)** — one `screen` state machine:
- Auth/onboarding: loading, onboarding (3 slides), splash/role-picker, phone, OTP (4-digit), registration (candidate/employer, individual/org toggle).
- Candidate (5-tab bottom nav): browse, search (+ filters sheet), nearby/map (radius), job detail, application submitted, my-applications (status tracker), messages + chat (+ WhatsApp sheet), profile, notifications.
- Employer (4-tab bottom nav): home/my-jobs (pause/activate + confirm), post-job form, applicants + applicant detail (accept/reject), messages, account.
- Admin (3 segmented tabs): overview, jobs, reports.

**Employer web dashboard (`Zeno-Web.dc.html`):** sidebar + topbar shell; overview (stat cards + weekly bar chart + quick actions + recent applicants); jobs management; applicants (filter + accept/reject/message); two-pane messages; post-job modal; toasts.

**Public website:** landing (`Zeno-Landing`), jobs browse + detail drawer (`Zeno-Jobs`), pricing (`Zeno-Pricing`), about (`Zeno-About`), company profile (`Zeno-Company`), contact (`Zeno-Contact`), terms & privacy (`Zeno-Terms`).

## Interactions & behavior
See `HANDOVER.md` §14 (business logic), §15 (interaction → proposed API map), §12 (state matrix). Highlights: proximity ranking by radius, AND-combined search/filters, one-application-per-job dedupe, application lifecycle (submitted→review→accepted/rejected), employer accept/reject, listing pause (with confirm)/activate, WhatsApp handoff with a pre-filled message, post-job readiness rule (title+category+type+salary required). Loading/error/offline states are largely **undesigned** — see §12 and Missing Requirements §20.

## State management
Prototype holds all state client-side with mock data. Key state per surface documented in `HANDOVER.md` (role, screen/history stack, filters, application/job/chat collections, modal/sheet/toast flags). For production, back these with the entities in §16.

## Design tokens
Full token set in `HANDOVER.md` §4. Summary:
- **Brand:** amber `#F7BE17` (+ `#F2A50E`), charcoal `#211F20`/`#2B2724`, papers `#F6F5F1`/`#F1EFE9`/`#FAF8F3`, white surfaces, borders `#EFEDE6`/`#EDEAE2`.
- **Status:** success `#1F8A4D`/`#E7F4EC`, warning `#8A6D12`/`#FDF3D6`, error `#B2453A`/`#FBEDEA`, info `#2E6E8A`/`#E2EEF4`, WhatsApp `#25D366`, verified `#2E86C1`.
- **Type:** Tajawal 400–900, RTL. Radii 7–30px. Shadows and motion tokens in §4.5–§4.7 (transform-only entrances).

## Assets
- `assets/zeno-logo.png` — full logo (mark + "zeno" + "زينو"); used on splash, auth, website headers/footers.
- `assets/zeno-mark.png` — icon-only mark; used in the app loading screen.
- **Icons:** Iconsax icon font (`iconsax.gitlab.io/i/icons.css`). Validated icon-name whitelist in `HANDOVER.md` §4.6 — several intuitive names don't exist in the set; verify before adding new ones.
- **Font:** Tajawal via Google Fonts.

## Files in this bundle
- `HANDOVER.md` — **the authoritative spec (read first).**
- `Zeno.dc.html` — mobile app (all roles).
- `Zeno-Web.dc.html` — employer web dashboard.
- `Zeno-Landing.dc.html`, `Zeno-Jobs.dc.html`, `Zeno-Pricing.dc.html`, `Zeno-About.dc.html`, `Zeno-Company.dc.html`, `Zeno-Contact.dc.html`, `Zeno-Terms.dc.html` — website.
- `assets/` — logo + mark.
- `support.js` — the prototype runtime (needed only to open the `.dc.html` files in a browser for reference; not part of the target app).

## How to preview the references
Open any `.dc.html` file in a browser (they load `support.js` from the same folder). They are Arabic RTL; view at phone width for `Zeno.dc.html` and desktop width for the web files.
