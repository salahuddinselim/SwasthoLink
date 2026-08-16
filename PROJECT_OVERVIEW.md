# Project Overview

Living log of what SwasthoLink actually is and what's been built, updated every time something new is added. For durable product facts (users, purpose, positioning) see `PRODUCT.md`; for the visual system see `DESIGN.md`; for the open task list see `TODO.md`.

## What This Is

SwasthoLink is a secure, verifiable e-prescription system for Bangladesh, built as a computer security course project. It demonstrates applied cryptography (RSA digital signatures, Diffie-Hellman key exchange, AES-256-GCM envelope encryption, password hashing) while solving a real problem: paper/unsigned-digital prescriptions are easy to forge, and there's no secure way for providers to share patient records. See `README.md` for the full problem statement and trust model, and `SECURITY.md` for the full threat model and cryptographic design writeup.

## Stack

- **Backend:** Laravel 12 (PHP 8.2, XAMPP locally)
- **Frontend:** Blade templates + Tailwind CSS (Breeze scaffold)
- **Database:** MySQL (`swastholink` schema)
- **Dev tools:** Composer, Node/npm + Vite for asset builds

## Architecture Summary

**Roles:** Admin, Hospital, Doctor, Patient, Pharmacist — each with its own dashboard and route group (`/admin`, `/hospital`, `/doctor`, `/patient`, `/pharmacist`).

**Trust chain:** Admin (root) → Hospital (approves its own doctors) → Doctor. Hospital and Doctor accounts start `pending` and can't act until Admin (or an approved Hospital) reviews their submitted documents (BMDC number, legal registration, NID) and flips them to `active` or `rejected`.

**Core data model:**
- `users` — role + status (`pending`/`active`/`rejected`)
- `hospitals`, `doctor_profiles`, `pharmacist_profiles` — verification data + RSA-2048 keypair (public key plaintext, private key encrypted at rest under `APP_KEY`), generated at admin approval
- `prescriptions` — doctor-issued, auto-generated `lookup_code`, RSA signature over the prescription content, `expires_at` (30 days), `patient_phone` (2FA at lookup), optional `patient_id` link (matched by email at creation), `status` (`active`/`dispensed`)
- `hospital_shares` — DH key-exchange + AES-256-GCM envelope for one hospital sharing a prescription with another (see below)
- `audit_logs` — records approvals, rejections, document views, prescription creation, lookups, second-factor failures, dispensing, and every hospital-share step

**Key workflows built:**
1. Provider registration (Hospital/Doctor/Pharmacist) with document upload → Admin approval queue → account activation, which also generates that provider's RSA keypair
2. Doctor writes a prescription → it's signed with the doctor's RSA private key → gets a short lookup code (`RX-XXXXXX`, 30-day expiry) → optionally auto-links to a patient account by email
3. Patient views prescriptions linked to their account
4. Pharmacist looks up a prescription by code → confirms the patient's phone last 4 digits (second factor) → views details with a live RSA signature pass/fail badge → marks it dispensed (single-use guard)
5. Hospital dashboard shows affiliated doctors, a per-patient roster (name/phone/email/Patient ID/visit count) built from that hospital's own prescriptions, and their prescriptions
6. Hospital-to-hospital record sharing: hospital A initiates a Diffie-Hellman exchange targeting hospital B and one of A's prescriptions; hospital B accepts, completing the exchange and envelope-encrypting the record (AES-256-GCM key derived from the DH secret, wrapped per-hospital with RSA-OAEP); either hospital can later decrypt and view it with their own RSA private key
7. Admin dashboard shows platform-wide stats and recent activity
8. Doctor patient lookup: a doctor searches a patient by their `PT-000004`-style Patient ID or email and automatically sees that patient's prescription history from the doctor's *own* hospital (same trust boundary that already let the doctor sign prescriptions there). History from other hospitals/doctors stays hidden unless the patient supplies a temporary, revocable access code (`PA-XXXXXX`, 24h validity, generated from the patient's "Share Access" page) — covers the "patient switched doctors and wants their new one to see older records for the same condition" case without any hospital-to-hospital coordination

## Visual Identity

Established security-blue "Accessible & Ethical" design system (brand color scale in `tailwind.config.js`, Figtree typography). Full detail in `DESIGN.md`.

## Change Log

<!-- Newest entries at the top. One line per meaningful change: what, and why if not obvious. -->

- **2026-08-17 (evening)** — Added TOTP two-factor authentication (`pragmarx/google2fa`), scoped to Admin and Hospital accounts only — these hold the platform's root of trust and hospital RSA private keys, so a compromised password there has far more reach than for a Doctor/Patient/Pharmacist. Standard flow: QR enrollment (using the existing `QrCodeService`/SVG pipeline), 8 recovery codes shown exactly once, a login-time challenge page that Auth::logout()s the password-verified-but-not-fully-authenticated user until a valid TOTP or recovery code is entered, and a password-confirmed disable action. Found and fixed a real bug via the new feature test suite: the `two_factor_*` columns weren't in `User::$fillable`, so every "successful" enable was silently a no-op — confirmed via `php artisan tinker` that `update()` returned true but nothing persisted. `TwoFactorAuthenticationTest` now locks in the full cycle (enable → correct/incorrect challenge → recovery code + reuse rejection → disable → login without 2FA afterward). Full suite: 42/42 passing.

- **2026-08-17 (later)** — Closed the "polish" list from the previous pass, plus the printable-slip gap: (1) printable prescription PDF via `barryvdh/laravel-dompdf`, restricted to the doctor/patient/hospital tied to that specific prescription — pharmacists and admins still only ever see prescription content through the code-lookup or audit-log paths, never a direct link; (2) dark mode, implemented as a manual (not OS-preference) toggle persisted in localStorage, using global CSS overrides keyed to the app's existing utility-class vocabulary instead of touching every view with `dark:` classes; (3) an in-app notification bell (new `notifications` table) wired into prescription creation, admin approve/reject decisions, the full hospital-share lifecycle, and patient-access-grant usage — each a real signal a user would want to know about without digging through the audit log; (4) CSV export on the doctor prescription list, hospital patient roster, and admin audit log. QR-in-PDF was attempted but dropped — dompdf doesn't reliably render inline SVG from `endroid/qr-code`'s writer, so the PDF instead leads with the lookup code in large text, consistent with the README's framing of the QR as an optional shortcut, not the primary mechanism. Full `php artisan test` suite still 38/38 after this pass.

- **2026-08-17** — Larger pass: responsive sidebar navigation (fixed left sidebar on desktop, hamburger + slide-over on mobile, replacing the old top nav) across all five dashboards; overview stat tiles added to the Doctor, Pharmacist (new dashboard — pharmacist previously had no landing page besides the lookup form), and Hospital dashboards; QR codes (via `endroid/qr-code`'s SVG writer, since this XAMPP setup has no GD extension) on lookup codes at creation and in the doctor/patient prescription lists; an Admin Audit Log viewer (paginated, filterable — the `audit_logs` table existed but had no browsing UI); hospital-to-hospital share revocation (either party can revoke a completed share, which wipes the ciphertext and both wrapped keys so it's unrecoverable even from a DB backup); rate limiting extended to prescription creation and doctor patient-record search; and search/filter (patient name, lookup code, date range) on the doctor's prescription list. Also added feature tests for the patient-record/access-grant system and share revocation — both were previously verified only by hand in a browser. Full `php artisan test` suite: 38/38 passing.

- **2026-08-17** — Added patient-record visibility, which didn't exist before: the Hospital dashboard had no patient list at all (only affiliated doctors + a raw prescription feed), and doctors could only see prescriptions they personally wrote, with no way to check a patient's prior history before prescribing. Built (1) a "Patients" section on the Hospital dashboard summarizing every patient treated there; (2) a Doctor "Patient Records" search by Patient ID (`PT-000004`) or email that auto-shows that patient's history at the doctor's own hospital (same trust boundary as prescribing there) and (3) a `patient_access_grants` table + Patient "Share Access" page so a patient can generate a short-lived, revocable `PA-XXXXXX` code to let a *new* doctor (e.g. after switching hospitals) see their older records too — every grant issue/use/rejection/revocation is audit-logged. Verified end-to-end in a real browser: same-hospital history visible with no code, cross-hospital history invisible without a valid code, visible with one, and correctly rejected once revoked.
- **2026-08-13** — Implemented the full cryptography layer this project exists to demonstrate: RSA-2048 keypair generation for Doctors/Hospitals on admin approval; RSA-SHA256 signing of every prescription plus live pass/fail signature verification at pharmacist lookup; a 30-day lookup-code expiry; a phone-last-4-digits second factor gating prescription detail at lookup; rate limiting on the lookup/verify endpoints; and a full hospital-to-hospital record-sharing feature built on a real Diffie-Hellman key exchange (RFC 3526 Group 14, bcmath-based since GMP isn't available here) whose derived secret seeds an AES-256-GCM envelope, with the raw AES key RSA-OAEP-wrapped per hospital and never persisted. Added `SECURITY.md` (full threat model + per-primitive rationale + honestly-documented limitations). Added `tests/Feature/PrescriptionSigningTest.php` and `tests/Feature/HospitalShareTest.php`, which caught two real bugs before they'd have shipped: (1) `openssl_pkey_export()` needed the same Windows/XAMPP `openssl.cnf` config-path fix already applied to `openssl_pkey_new()`, and (2) `UserFactory` relying on DB column defaults instead of setting `role`/`status` explicitly broke `actingAs()` in tests (pre-existing, unrelated to this session's feature work, fixed alongside it). Also removed the vestigial `verified` middleware from `/dashboard`, and added required-field markers, upload-submit loading states, and on-blur inline validation across the registration/login/prescription forms.
- **2026-08-11** — Ran a `/qa` pass (mobile responsiveness, error handling, security probing). Found and fixed one high-severity bug: the admin document viewer crashed to an unhandled 500 with a full debug stack trace on a path-traversal attempt, instead of validating input itself. Health score 95.5 → 98.5. Full report in `.gstack/qa-reports/`.
- **2026-08-11** — Added `database/sql/swastholink.sql` (importable schema + seeded Admin, verified by import-and-login test) plus README sections: curated Project Structure tree, step-by-step local setup (migrations or direct SQL import), and a manual verification walkthrough.
- **2026-08-11** — Added RSA/Diffie-Hellman mathematical explanations and a proprietary license notice to README.md.
- **2026-08-11** — Full bug-hunt pass across the running system. Found and fixed a high-severity issue: any account (including Admin) could self-delete via the stock Breeze profile page, which would have cascade-deleted a Doctor's entire prescription history. Restricted self-deletion to Patient accounts. Full details in `TODO.md`.
- **2026-08-11** — Created `PROJECT_OVERVIEW.md` and `TODO.md` for ongoing tracking.
- **2026-08-11** — Established visual identity: brand-blue color system, Figtree typography, `PRODUCT.md` + `DESIGN.md` written.
- **2026-08-11** — Built role-specific dashboards and a basic prescription workflow (create/list/lookup/dispense) for all five roles.
- **2026-08-11** — Built the Admin approval foundation: roles/status schema, registration flows with document upload, Admin approval queue, audit logging.
- **2026-08-11** — Repo initialized and pushed to GitHub (`github.com/salahuddinselim/SwasthoLink`).

## What's Not Built Yet

The core cryptography this project set out to demonstrate is now implemented (see the 2026-08-13 change-log entry and `SECURITY.md`). Remaining open items are all in `TODO.md` — currently none in "Build Order" are unchecked; new work shows up there as it's identified.
