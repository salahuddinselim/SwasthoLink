# Project Overview

Living log of what SwasthoLink actually is and what's been built, updated every time something new is added. For durable product facts (users, purpose, positioning) see `PRODUCT.md`; for the visual system see `DESIGN.md`; for the open task list see `TODO.md`.

## What This Is

SwasthoLink is a secure, verifiable e-prescription system for Bangladesh, built as a computer security course project. It demonstrates applied cryptography (RSA digital signatures, Diffie-Hellman key exchange, password hashing) while solving a real problem: paper/unsigned-digital prescriptions are easy to forge, and there's no secure way for providers to share patient records. See `README.md` for the full problem statement and trust model.

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
- `hospitals`, `doctor_profiles`, `pharmacist_profiles` — verification data + (future) RSA keys
- `prescriptions` — doctor-issued, auto-generated `lookup_code`, optional `patient_id` link (matched by email at creation), `status` (`active`/`dispensed`)
- `audit_logs` — records approvals, rejections, document views, prescription creation, lookups, dispensing

**Key workflows built:**
1. Provider registration (Hospital/Doctor/Pharmacist) with document upload → Admin approval queue → account activation
2. Doctor writes a prescription → gets a short lookup code (`RX-XXXXXX`) → optionally auto-links to a patient account by email
3. Patient views prescriptions linked to their account
4. Pharmacist looks up a prescription by code → views details → marks it dispensed (single-use guard)
5. Hospital dashboard shows affiliated doctors and their prescriptions
6. Admin dashboard shows platform-wide stats and recent activity

## Visual Identity

Established security-blue "Accessible & Ethical" design system (brand color scale in `tailwind.config.js`, Figtree typography). Full detail in `DESIGN.md`.

## Change Log

<!-- Newest entries at the top. One line per meaningful change: what, and why if not obvious. -->

- **2026-08-11** — Added `database/sql/swastholink.sql` (importable schema + seeded Admin, verified by import-and-login test) plus README sections: curated Project Structure tree, step-by-step local setup (migrations or direct SQL import), and a manual verification walkthrough.
- **2026-08-11** — Added RSA/Diffie-Hellman mathematical explanations and a proprietary license notice to README.md.
- **2026-08-11** — Full bug-hunt pass across the running system. Found and fixed a high-severity issue: any account (including Admin) could self-delete via the stock Breeze profile page, which would have cascade-deleted a Doctor's entire prescription history. Restricted self-deletion to Patient accounts. Full details in `TODO.md`.
- **2026-08-11** — Created `PROJECT_OVERVIEW.md` and `TODO.md` for ongoing tracking.
- **2026-08-11** — Established visual identity: brand-blue color system, Figtree typography, `PRODUCT.md` + `DESIGN.md` written.
- **2026-08-11** — Built role-specific dashboards and a basic prescription workflow (create/list/lookup/dispense) for all five roles.
- **2026-08-11** — Built the Admin approval foundation: roles/status schema, registration flows with document upload, Admin approval queue, audit logging.
- **2026-08-11** — Repo initialized and pushed to GitHub (`github.com/salahuddinselim/SwasthoLink`).

## What's Not Built Yet

The core cryptography this project is meant to demonstrate is not implemented yet:
- RSA keypair generation for Doctors/Hospitals, prescription signing, and signature verification at pharmacy lookup
- Diffie-Hellman key exchange + AES envelope encryption for hospital-to-hospital record sharing

Everything currently built is the functional scaffold these features attach to. See `TODO.md` for the full open list.
