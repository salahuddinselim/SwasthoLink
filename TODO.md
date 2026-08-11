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
- [ ] RSA keypair generation for Doctors and Hospitals (on approval)
- [ ] Prescription signing with the doctor's RSA private key
- [ ] Signature verification at pharmacist lookup (auto, shown as pass/fail)
- [ ] Diffie-Hellman key exchange between two Hospital accounts
- [ ] AES-256-GCM envelope encryption for hospital-to-hospital record sharing, using the DH-derived key
- [ ] Lookup-code expiry (e.g. 30 days)
- [ ] Second-factor check at pharmacy lookup (e.g. patient phone last 4 digits)
- [ ] Rate limiting on login, lookup, and verification endpoints
- [ ] Required-field indicators (`*`) on all registration forms
- [ ] Loading/disabled state on submit buttons during file uploads
- [ ] Inline (on-blur) client-side form validation
- [ ] Written security report (threat model, what each crypto primitive defends against)

## Bugs Found & Fixed

- [x] Rejected accounts saw the same "pending, waiting for admin" message as truly-pending accounts, with no rejection reason shown — fixed by branching the pending-approval page on account status.
- [x] Admin approve/reject flash messages read "Dr. Dr. Fatema Begum" — doctor's own name already contained "Dr.", controller was prepending another one — fixed by dropping the hardcoded prefix.
- [x] Role-gated nav links (e.g. "New Prescription") showed for accounts still pending approval, leading to a dead-end 403 on click — fixed by hiding those links until the account is active.
- [x] GitHub listed Claude as a repo contributor from an initial commit that included a `Co-Authored-By` trailer — repo history fixed (commit amended, trailer removed, force-pushed; verified via `git log`, no other ref reaches the old commit). GitHub's own contributor sidebar cache has not caught up as of the last check — see matching entry in Open Bugs, since that part is outside this repo's control.
- [x] **(High severity)** Any authenticated user — including Doctor, Hospital, Pharmacist, and even Admin — could permanently delete their own account via the stock Breeze "Delete Account" profile feature. Because `prescriptions.doctor_id` cascade-deletes, a Doctor deleting their account would have silently destroyed every prescription they ever wrote — permanently erasing patient medical records — and there was no protection against the only Admin account deleting itself, which would have locked the whole approval chain with no recovery path. Fixed by restricting self-service account deletion to Patient accounts only (`ProfileController::destroy`, enforced server-side regardless of how the request arrives) and hiding the delete-account UI for other roles with an explanatory message.

## Open Bugs

<!-- Add entries here as [ ] when found; move to "Bugs Found & Fixed" as [x] once resolved. -->

- [ ] GitHub's contributor sidebar (github.com/salahuddinselim/SwasthoLink) still lists "claude" alongside "salahuddinselim" as of the last check, even though the actual git history has no Claude attribution anywhere (verified via `git log`, `git ls-remote`, and `git reflog`). This is GitHub's own cached contributor index not having recomputed after the force-push — not something fixable from this repo's side. Options if it doesn't self-resolve: wait longer, or delete and recreate the repo (currently 0 stars/forks/issues, so nothing would be lost).
