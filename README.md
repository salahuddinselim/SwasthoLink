# SwasthoLink — Secure e-Prescription & Medical Record System

A computer security course project built with **Laravel (PHP)**, **Tailwind CSS**, and **MySQL**, demonstrating proper authentication and applied cryptography (RSA digital signatures, Diffie–Hellman key exchange, and password hashing).

## The Problem (Bangladesh context)

Prescriptions and medical records in Bangladesh are still mostly paper-based or shared as unsigned digital copies (WhatsApp photos, plain PDFs/photocopies). This makes them easy to forge — fake prescriptions for restricted drugs are a known issue at pharmacies — and records shared between hospitals/labs/clinics have no real integrity or confidentiality guarantee in transit. Most medicine purchases also happen **offline, at a physical pharmacy counter** — so any solution has to work for a patient who just walks in and talks to the person behind the counter, not one who opens an app together with the pharmacist. SwasthoLink is designed around that reality.

## Trust Model — Who Is Actually Trusted, and Why

This is the part that makes the crypto meaningful instead of decorative. SwasthoLink uses a three-tier chain of trust, similar to how real-world PKI certificate chains work:

```
Admin (platform / root of trust)
   │  verifies legal registration documents
   ▼
Hospital / Clinic (organizational account)
   │  verifies staff doctor's BMDC registration
   ▼
Doctor (individual account, affiliated with a hospital)
```

- **Admin** is the root: manually verifies a hospital/clinic's legal registration (trade license, DGHS registration) before approving it.
- **Hospital/Clinic** accounts, once approved, get their own RSA keypair and can approve doctors who work there.
- **Doctor** accounts submit their **BMDC (Bangladesh Medical & Dental Council) registration number** plus supporting documents (BMDC certificate, NID) at signup. The account sits in `pending` status — **it cannot create or sign prescriptions yet**. An Admin (or an affiliated Hospital admin) manually checks the BMDC number/documents before approving. Only on approval does the system generate the doctor's RSA keypair and activate the account.
- Independent doctors (not affiliated with a hospital) can still register directly with Admin approval — the hospital layer is optional, not mandatory.

**Why this matters:** no software can currently auto-confirm "this person is a licensed doctor" — that has to be a human verification step against BMDC records. The RSA signature doesn't replace that check; it makes the *result* of that check (this prescription really came from a verified doctor, unaltered) provable after the fact, to anyone, without re-checking BMDC every time.

## What We're Building

Five account types:

- **Admin** — root of trust; approves hospitals and independent doctors.
- **Hospital/Clinic** — organizational account; approves its own affiliated doctors; can securely exchange patient records with other hospitals.
- **Doctor** — writes prescriptions. Every prescription is hashed and **signed with the doctor's RSA private key**, so forgery or tampering becomes detectable.
- **Patient** — owns their prescriptions/records; can share a record with another hospital/doctor, and gets a short lookup code for each prescription (see below).
- **Pharmacist** — verified staff account at a pharmacy; looks up a prescription by code and the system verifies its signature automatically before it's dispensed.

## The Offline Pharmacy Flow (the "unique code" idea)

Since most medicine buying happens face-to-face at a counter, prescriptions don't rely on the patient's phone or an app at the point of sale:

1. When a doctor signs a prescription, the system generates a **short, memorable lookup code** (e.g. `RX-482917`), tied to that specific signed record. It's printed on a slip and/or sent by SMS — a QR code is included too, as an optional scan-instead-of-type shortcut.
2. At the pharmacy, the patient just **tells the pharmacist the code** — no app needed on the patient's side.
3. The pharmacist (logged into their own verified account) types the code into a **Lookup Prescription** screen.
4. The system automatically recomputes the prescription hash and verifies the RSA signature against the doctor's stored public key, then shows a clear result: ✅ *Verified — Dr. X, BMDC #12345, [medicine list]* or ❌ *Invalid / Tampered — do not dispense*. The pharmacist never has to understand the crypto; they just see the verdict.
5. Safeguards on the code itself:
   - **Expiry** (e.g. 30 days) and **single/limited use** — once dispensed, the code is marked used so it can't be replayed to refill restricted drugs.
   - **Second factor at lookup** — pair the code with something like the patient's phone last 4 digits or date of birth, so a code alone isn't enough to pull up someone's medical info if it's overheard or found on a discarded slip.
   - **Every lookup is audit-logged** (which pharmacist, which code, when) for accountability.

Digital-to-digital sharing (a hospital sending a patient's history to another hospital/lab) is a different, separate flow — that's where Diffie–Hellman comes in, described below.

## How the Required Crypto Maps to Real Features

| Requirement | Where it's used | What it protects against |
|---|---|---|
| **Password hashing** | Laravel's built-in Bcrypt/Argon2id hashing for every user account | Plaintext password exposure if the DB leaks |
| **RSA** | Digital signatures on prescriptions — doctor signs the prescription hash with their private key; pharmacists/patients verify it with the doctor's public key. Hospitals get their own keypair too, for signing reports and vouching for affiliated doctors | Forged prescriptions, tampering, denies a doctor the ability to disown a prescription they actually wrote (non-repudiation) |
| **Diffie–Hellman** | Key agreement between two *organizational* accounts (hospital ↔ hospital, or hospital ↔ lab) when sharing a patient's full record digitally; the derived shared secret becomes an AES-256-GCM key that encrypts the record before it's stored/sent | Eavesdropping on shared medical data, even if the transport layer (TLS) were somehow compromised — defense in depth |

Pages that exist specifically to make the crypto visible for demo/grading purposes:
- **Verify Prescription / Lookup by Code** — shows the hash recomputation and RSA signature check step-by-step, with a clear pass/fail explanation.
- **Secure Share** — shows the Diffie–Hellman public-value exchange between two hospital accounts and confirms both sides derive the same session key before a record is encrypted.

## General Security Practices Included

- CSRF protection on all forms (Laravel default)
- Blade auto-escaping to prevent XSS
- Eloquent parameterized queries everywhere (no raw SQL string concatenation) to prevent SQL injection
- Rate limiting on login, lookup-code, and verification endpoints (to prevent code-guessing/brute force)
- RSA private keys encrypted at rest, never exposed via any API response
- Secure session cookies, session regeneration on login
- Authorization checks (Policies) on every prescription/record route to prevent one user from accessing another's data (no IDOR)
- Full audit log of sensitive actions (logins, signing, verification, code lookups, sharing)
- Manual document/ID verification gate before any Doctor or Hospital account can act (BMDC number + documents, legal registration)

## Tech Stack

- **Backend**: Laravel (PHP), Breeze/Fortify for auth scaffolding
- **Frontend**: Blade templates + Tailwind CSS
- **Database**: MySQL/MariaDB via Eloquent ORM
- **Crypto**: PHP `openssl_*` functions for RSA keygen/sign/verify, `phpseclib3` for Diffie–Hellman, `openssl_encrypt`/`openssl_decrypt` (AES-256-GCM) for the DH-derived symmetric key

## Build Order

1. Laravel + Breeze scaffold, user roles (Admin/Hospital/Doctor/Patient/Pharmacist), RBAC middleware
2. Password authentication end-to-end (register/login/reset)
3. Verification gate: BMDC/document submission + Admin/Hospital approval workflow before an account becomes active
4. RSA: doctor & hospital keypair generation, prescription signing
5. Pharmacy lookup-code flow: code generation, expiry/single-use, second-factor check, verification screen
6. Diffie–Hellman: key-exchange endpoint + AES envelope encryption for hospital-to-hospital record sharing
7. Audit logging, rate limiting, authorization policy pass
8. UI polish for the five role dashboards plus the two explainer pages
9. Short written security report (threat model, what each crypto primitive defends against)

---

*Next step: scaffold the Laravel project inside this folder and start on step 1 of the build order.*
