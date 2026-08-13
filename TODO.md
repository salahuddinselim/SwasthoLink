# TODO

Task list for SwasthoLink. Check off tasks as they're completed; uncheck if something turns out incomplete or gets reverted. Bugs get added here when found and checked off when fixed — keep the entry, don't delete it, so there's a record.

## Build Order

- [x] Set up local dev environment (XAMPP PHP 8.2, Composer, MySQL)
- [x] Scaffold Laravel 12 + Breeze (Blade + Tailwind), push to GitHub
- [x] Roles/status schema (`users.role`/`status`, `hospitals`, `doctor_profiles`, `pharmacist_profiles`, `audit_logs`)
- [x] Role-based middleware (`role:admin`, `role:doctor`, etc.)
- [x] Seed a default Admin account
- [x] Hospital/Doctor/Pharmacist registration forms with document upload
- [x] Admin approval queue (approve/reject + secure document viewer + audit log)
- [x] Pending-approval page distinguishing pending vs rejected (with reason)
- [x] Role-specific dashboards for all five roles (Admin, Hospital, Doctor, Patient, Pharmacist)
- [x] Prescription model: doctor create, auto lookup-code generation, patient auto-link by email
- [x] Patient prescription list (own prescriptions only)
- [x] Pharmacist lookup by code + mark-as-dispensed (single-use guard)
- [x] Hospital dashboard (affiliated doctors + their prescriptions)
- [x] Visual identity: brand color system (`tailwind.config.js`), typography, `PRODUCT.md` + `DESIGN.md`
- [x] `PROJECT_OVERVIEW.md` and `TODO.md` for ongoing tracking
- [x] RSA/Diffie-Hellman math explainer + Mermaid diagrams in README
- [x] `LICENSE` (All Rights Reserved) + README license notice
- [x] `database/sql/swastholink.sql` — importable schema + seeded Admin (verified via import + login test)
- [x] README: curated Project Structure tree, local setup instructions (migration or SQL-import path), manual verification walkthrough
- [x] RSA keypair generation for Doctors and Hospitals (on approval)
- [x] Prescription signing with the doctor's RSA private key
- [x] Signature verification at pharmacist lookup (auto, shown as pass/fail)
- [x] Diffie-Hellman key exchange between two Hospital accounts
- [x] AES-256-GCM envelope encryption for hospital-to-hospital record sharing, using the DH-derived key
- [x] Lookup-code expiry (30 days)
- [x] Second-factor check at pharmacy lookup (patient phone last 4 digits)
- [x] Rate limiting on login, lookup, and verification endpoints
- [x] Required-field indicators (`*`) on all registration forms
- [x] Loading/disabled state on submit buttons during file uploads
- [x] Inline (on-blur) client-side form validation
- [x] Written security report (threat model, what each crypto primitive defends against) — `SECURITY.md`
- [x] Remove the vestigial `verified` middleware from `/dashboard` — `User` doesn't implement `MustVerifyEmail`, so it silently no-ops; either wire up real email verification or drop the dead middleware (found during `/qa`, not a bug, just cleanup)

## Bugs Found & Fixed

- [x] Rejected accounts saw the same "pending, waiting for admin" message as truly-pending accounts, with no rejection reason shown — fixed by branching the pending-approval page on account status.
- [x] Admin approve/reject flash messages read "Dr. Dr. Fatema Begum" — doctor's own name already contained "Dr.", controller was prepending another one — fixed by dropping the hardcoded prefix.
- [x] Role-gated nav links (e.g. "New Prescription") showed for accounts still pending approval, leading to a dead-end 403 on click — fixed by hiding those links until the account is active.
- [x] GitHub listed Claude as a repo contributor from an initial commit that included a `Co-Authored-By` trailer — repo history fixed (commit amended, trailer removed, force-pushed; verified via `git log`, no other ref reaches the old commit). GitHub's own contributor sidebar cache has not caught up as of the last check — see matching entry in Open Bugs, since that part is outside this repo's control.
- [x] **(High severity)** Any authenticated user — including Doctor, Hospital, Pharmacist, and even Admin — could permanently delete their own account via the stock Breeze "Delete Account" profile feature. Because `prescriptions.doctor_id` cascade-deletes, a Doctor deleting their account would have silently destroyed every prescription they ever wrote — permanently erasing patient medical records — and there was no protection against the only Admin account deleting itself, which would have locked the whole approval chain with no recovery path. Fixed by restricting self-service account deletion to Patient accounts only (`ProfileController::destroy`, enforced server-side regardless of how the request arrives) and hiding the delete-account UI for other roles with an explanatory message.
- [x] **(High severity)** `/qa` pass found: sending a crafted `?path=` with `../` traversal sequences to the admin document viewer (`DocumentController::show`) crashed to an **unhandled 500 error with a full debug stack trace** instead of a clean 404 — the controller relied on Flysystem's internal `PathTraversalDetected` exception to reject the input rather than validating it itself, and that exception was never caught. No file was actually read out of bounds (Flysystem's own guard fires first), but with `APP_DEBUG=true` the crash page leaks framework internals, file paths, and the attempted payload. Fixed by rejecting any `path` containing `..` before it reaches storage. Verified: traversal payload now 404s cleanly; legitimate document viewing re-tested and still works. Full writeup: `.gstack/qa-reports/qa-report-swastholink-2026-08-11.md`.
- [x] `RsaKeyService::generateKeyPair()` threw `RuntimeException: Invalid RSA private key while signing prescription` on every prescription creation in this local environment — `openssl_pkey_new()` was correctly given the `config` option pointing at XAMPP's bundled `openssl.cnf` (needed because PHP's compiled-in default config path doesn't exist on Windows), but `openssl_pkey_export()` resolves its own config independently and needs that same option passed to *it* too, which it wasn't. Fixed by passing `$options` (containing `config`) as the 4th argument to `openssl_pkey_export()` and checking its return value instead of assuming success. Caught by the new `PrescriptionSigningTest` feature test, not manual QA.

- [x] Two pre-existing `ProfileTest` failures (`user can delete their account`, `correct password must be provided to delete account`), unrelated to today's crypto work but found while running the full suite before it (confirmed via `git stash` + re-run against the prior commit). Root cause: `UserFactory` never set `role`/`status` explicitly, relying on the `users` table's DB-level column defaults (`patient`/`active`) instead — but those defaults only apply on `INSERT`, they don't populate the in-memory model `create()` returns, and `actingAs()` in tests uses that exact in-memory instance rather than re-fetching from the DB. So `$request->user()->role` was `null`, not `'patient'`, inside `ProfileController::destroy()`, which incorrectly took the "deletion restricted to Patients" branch (added in the earlier high-severity account-deletion fix) for what should have been an allowed default-role test user. Fixed by having `UserFactory::definition()` set `role`/`status` explicitly rather than relying on DB defaults.

## Open Bugs

<!-- Add entries here as [ ] when found; move to "Bugs Found & Fixed" as [x] once resolved. -->

- [ ] GitHub's contributor sidebar (github.com/salahuddinselim/SwasthoLink) still lists "claude" alongside "salahuddinselim" as of the last check, even though the actual git history has no Claude attribution anywhere (verified via `git log`, `git ls-remote`, and `git reflog`). This is GitHub's own cached contributor index not having recomputed after the force-push — not something fixable from this repo's side. Options if it doesn't self-resolve: wait longer, or delete and recreate the repo (currently 0 stars/forks/issues, so nothing would be lost).
