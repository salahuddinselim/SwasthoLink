# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Five account types, each with a distinct job:

- **Admin** — platform root of trust. Manually reviews and approves/rejects Hospital and independent Doctor registrations by checking submitted legal/BMDC documents.
- **Hospital/Clinic** — organizational account. Once approved by Admin, can have Doctors register under its affiliation; sees its doctors and their prescriptions.
- **Doctor** — writes prescriptions for patients. Must be approved (BMDC registration number + certificate + NID reviewed by Admin) before the account can act.
- **Patient** — registers freely (no approval gate). Views prescriptions written for them, matched automatically by email.
- **Pharmacist** — verified staff account at a pharmacy counter. Looks up a prescription by a short code the patient provides verbally, views details, and marks it dispensed (single-use).

## Product Purpose

SwasthoLink is a secure, verifiable e-prescription system built as a computer security course project, and designed around a real Bangladesh problem: prescriptions are still mostly paper-based or shared as unsigned digital copies (WhatsApp photos, plain PDFs), making them easy to forge — a known issue for restricted-drug purchases at pharmacies — and provider-to-provider record sharing has no real integrity/confidentiality guarantee. The project demonstrates applied cryptography (RSA digital signatures, Diffie-Hellman key exchange, password hashing) as the mechanism that makes prescriptions provably authentic and tamper-evident, not as a disconnected classroom demo.

Success = a working, testable system where the crypto's role in preventing forgery/tampering can be clearly demonstrated (for grading/viva) while remaining realistic for how medicine is actually bought in Bangladesh (in person, at a counter).

## Positioning

Most "digital prescription" concepts assume the patient and pharmacist both use an app. SwasthoLink doesn't: prescriptions are signed and stored digitally, but consumed at the counter via a short, memorable lookup code the patient just speaks aloud — no app needed at the point of sale. Underneath that simple interaction, the system automatically verifies an RSA signature against the issuing doctor's key before showing the pharmacist a clear verified/invalid verdict, and doctors/hospitals only get signing authority after a human (Admin, or an Admin-approved Hospital) manually verifies their real-world credentials (BMDC number, legal registration) — a three-tier trust chain modeled on real PKI certificate chains. A competing "just digitize prescriptions" product without both the offline-code UX and the verified trust chain could not truthfully make the same anti-forgery claim.

## Operating Context

- Doctors and Hospitals go through a document-upload + manual Admin review gate before their account can create/sign anything; Patients do not.
- The actual security boundary is that human document review — the RSA signature doesn't replace it, it makes the *result* of that check (this really came from a verified doctor, unaltered) provable afterward without re-checking BMDC every time.
- The primary patient-facing interaction is offline and face-to-face: a patient tells a pharmacist a short code (e.g. `RX-482917`), the pharmacist types it into their own account, and verification happens invisibly in the background.
- Hospital-to-hospital digital record sharing (planned, via Diffie-Hellman) is a separate flow from the pharmacy-counter flow — that one really is system-to-system.
- Every role lands on its own dashboard, not a shared generic screen: Admin sees approval queue + stats, Hospital sees its affiliated doctors, Doctor manages prescriptions they've written, Patient sees prescriptions written for them, Pharmacist gets a lookup tool.

## Capabilities and Constraints

**Built:** Laravel 12 + Breeze (Blade + Tailwind) + MySQL. Role/status-based accounts (`pending` / `active` / `rejected`) with document upload and Admin approval workflow. Audit logging of sensitive actions. Prescription creation with auto-generated lookup codes and automatic patient-account linking by email. Pharmacist lookup with a single-use dispense guard. Per-role dashboards for all five roles.

**Not yet built (explicitly planned):** RSA keypair generation for Doctors/Hospitals, prescription signing, and signature verification at pharmacy lookup. Diffie-Hellman key exchange + AES envelope encryption for hospital-to-hospital record sharing. Lookup-code expiry and a second-factor check at pharmacy lookup. Rate limiting on lookup/verification endpoints.

**Terminology:** "lookup code" = the patient-facing short code (`RX-XXXXXX`) tied to one signed prescription. "BMDC number" = Bangladesh Medical & Dental Council registration number, the real-world doctor identity check. "Dispensed" = a prescription's terminal state once a pharmacist fulfills it; the code cannot be reused after.

**Dev environment constraint:** local development runs on XAMPP (PHP 8.2) + MySQL on Windows.

## Brand Commitments

Name "SwasthoLink" is fixed. No logo, color palette, or typography is committed — the app currently runs on unstyled Laravel Breeze defaults (Figtree font, default Tailwind indigo/gray) with no deliberate visual identity yet. Full creative freedom on the visual system.

## Evidence on Hand

None. This is a fresh academic project — no real testimonials, case studies, usage stats, or press exist. All prior test data used during development was synthetic and has been deleted. Future work must not fabricate patient/doctor testimonials, adoption numbers, or press mentions.

## Product Principles

1. **The crypto must be visible and explainable, not decorative.** Every RSA/DH feature exists to demonstrate a real security property for grading/viva — verification UI should show pass/fail and why, not hide behind a vague "verified" badge.
2. **Design for the pharmacy counter, not the browser.** The core patient-facing interaction happens offline, face-to-face, with a human reading a short code aloud — optimize for that, not app-to-app handoff.
3. **Trust is manual, crypto is provable.** The real security boundary is human document review (Admin/Hospital). The interface must make that approval gate and its outcomes (pending/active/rejected + reason) unambiguous to every role.
4. **Built to grow past the classroom.** It ships as a course deliverable now, but schema/role/trust-chain decisions shouldn't paint the project into a corner if it's extended toward real deployment later.
5. **Every role gets a real home.** Five different users (admin, hospital, doctor, patient, pharmacist) get dashboards suited to their actual job, not one generic screen with conditionals.

## Accessibility & Inclusion

No formal standard specified for this project. Follow standard contrast and semantic-HTML practices as a baseline rather than targeting a specific WCAG level.
