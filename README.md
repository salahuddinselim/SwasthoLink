# SwasthoLink — Secure e-Prescription & Medical Record System

**© 2026 Salah Uddin. All Rights Reserved.**
This is a personal academic project. It is **not open source** — see [License](#license) below before reusing, copying, or redistributing any part of this code.

A computer security course project built with **Laravel (PHP)**, **Tailwind CSS**, and **MySQL**, demonstrating proper authentication and applied cryptography (RSA digital signatures, Diffie–Hellman key exchange, and password hashing).

> Project tracking: see [`PROJECT_OVERVIEW.md`](PROJECT_OVERVIEW.md) for what's built and the running change log, and [`TODO.md`](TODO.md) for the open task list and bug history. Product/design decisions are recorded in [`PRODUCT.md`](PRODUCT.md) and [`DESIGN.md`](DESIGN.md).

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

## Cryptography Deep Dive — The Actual Math

This section exists because a security course project should be able to show *why* the crypto works, not just that a library was called. Both algorithms below are implemented with real, production-sized parameters in the app (via `phpseclib3` / PHP's `openssl_*`) — the numbers used here are deliberately tiny so the arithmetic is checkable by hand; real keys use 2048+ bit RSA moduli and standard DH groups (e.g. RFC 3526), not two-digit primes.

### RSA — Digital Signatures on Prescriptions

RSA relies on one hard problem: given a large number `n` that is the product of two primes, it's computationally infeasible to recover those primes (factor `n`) if they're large enough — but it's easy to multiply them together in the first place. Key generation exploits that asymmetry.

**1. Key generation** (done once, when a Doctor/Hospital is approved):

$$
\begin{aligned}
&\text{Pick two large primes } p, q \\
&n = p \times q \\
&\varphi(n) = (p-1)(q-1) &&\text{(Euler's totient of } n \text{)} \\
&\text{Pick } e \text{ such that } 1 < e < \varphi(n) \text{ and } \gcd(e, \varphi(n)) = 1 \\
&d \equiv e^{-1} \pmod{\varphi(n)} &&\text{(modular inverse of } e\text{)}
\end{aligned}
$$

- **Public key:** $(e, n)$ — this is what gets stored in `doctor_profiles.rsa_public_key` / `hospitals.rsa_public_key` and handed to anyone who needs to verify.
- **Private key:** $(d, n)$ — stored encrypted at rest (`rsa_private_key_encrypted`), never leaves the server, never appears in any API response.

**2. Signing a prescription** (Doctor, at creation time):

$$
h = \text{SHA-256}(\text{prescription content}), \qquad s = h^{d} \bmod n
$$

The prescription's content is hashed first (never sign raw data directly), then the hash is raised to the private exponent `d` mod `n`. The result `s` is the signature, stored alongside the prescription.

**3. Verifying a prescription** (automatic, at pharmacist lookup):

$$
h' = s^{e} \bmod n \overset{?}{=} \text{SHA-256}(\text{prescription content})
$$

Anyone with the doctor's *public* key can raise the signature to `e` mod `n` and check it matches a fresh hash of the content. If the content was altered by even one character, the hash won't match and verification fails — that's the tamper-evidence property. If the signature wasn't produced with the matching private key, it won't match either — that's the authenticity property.

**Why it works — a tiny worked example** (toy primes, not secure sizes — for illustration only):

$$
\begin{aligned}
p &= 61, \quad q = 53 \\
n &= 61 \times 53 = 3233 \\
\varphi(n) &= 60 \times 52 = 3120 \\
e &= 17 \quad (\gcd(17, 3120) = 1) \\
d &= 17^{-1} \bmod 3120 = 2753
\end{aligned}
$$

To sign a message hash of, say, $h = 65$, using the **private** exponent $d = 2753$:

$$
s = 65^{2753} \bmod 3233 = 588
$$

To verify, anyone with the **public** exponent $e = 17$ checks:

$$
588^{17} \bmod 3233 = 65 = h \quad \checkmark
$$

The signer used $d$ (private) to produce `s`; anyone can use $e$ (public) to recover `h` from `s` and compare it to a freshly computed hash — without ever needing to know $d$, $p$, or $q$. (This works because $e$ and $d$ are modular inverses mod $\varphi(n)$: raising to $d$ then to $e$ returns the original value, by Euler's theorem.)

```mermaid
sequenceDiagram
    participant D as Doctor (has private key d)
    participant DB as Database
    participant P as Pharmacist (has doctor's public key e)

    D->>D: hash = SHA-256(prescription)
    D->>D: signature = hash^d mod n
    D->>DB: store {prescription, signature}
    Note over D,DB: Private key d never leaves the server

    P->>DB: lookup by code
    DB-->>P: {prescription, signature, doctor's public key (e, n)}
    P->>P: hash' = SHA-256(prescription)
    P->>P: check = signature^e mod n
    alt check == hash'
        P->>P: ✅ Verified — authentic & unaltered
    else check != hash'
        P->>P: ❌ Invalid — tampered or forged, do not dispense
    end
```

### Diffie–Hellman — Key Exchange for Hospital-to-Hospital Record Sharing

Diffie–Hellman lets two parties agree on a shared secret over a channel an eavesdropper can see *completely* — without ever transmitting the secret itself. It relies on a different hard problem: given $g$, $p$, and $g^a \bmod p$, it's computationally infeasible to recover $a$ (the discrete logarithm problem) when $p$ is large.

**1. Public parameters** (agreed in advance, not secret): a large prime $p$ and a generator $g$.

**2. Each side picks a private value and computes a public value:**

$$
\begin{aligned}
\text{Hospital A: pick private } a \implies A = g^{a} \bmod p \\
\text{Hospital B: pick private } b \implies B = g^{b} \bmod p
\end{aligned}
$$

$A$ and $B$ are exchanged over the network in the open — an eavesdropper can see both.

**3. Both sides independently compute the same shared secret:**

$$
\text{Hospital A computes: } B^{a} \bmod p = (g^{b})^{a} \bmod p = g^{ab} \bmod p
$$

$$
\text{Hospital B computes: } A^{b} \bmod p = (g^{a})^{b} \bmod p = g^{ab} \bmod p
$$

Both arrive at $g^{ab} \bmod p$ — identical — without either side ever sending $a$, $b$, or the secret itself over the wire. That shared value then becomes the key for AES-256-GCM, which actually encrypts the patient record payload before it's transmitted/stored.

**Tiny worked example:**

$$
p = 23, \quad g = 5
$$

$$
\begin{aligned}
\text{Hospital A: } a = 6 &\implies A = 5^{6} \bmod 23 = 8 \\
\text{Hospital B: } b = 15 &\implies B = 5^{15} \bmod 23 = 19
\end{aligned}
$$

Exchange $A = 8$ and $B = 19$ in the open. Now:

$$
\text{A computes: } 19^{6} \bmod 23 = 2, \qquad \text{B computes: } 8^{15} \bmod 23 = 2
$$

Both land on shared secret $2$ — an observer who saw $p=23$, $g=5$, $A=8$, $B=19$ cannot feasibly recover $6$, $15$, or $2$ without solving a discrete log (trivial at this toy size, computationally infeasible at real key sizes of 2048+ bits).

```mermaid
sequenceDiagram
    participant HA as Hospital A
    participant HB as Hospital B

    Note over HA,HB: Public, pre-agreed: prime p, generator g

    HA->>HA: pick private a, compute A = g^a mod p
    HB->>HB: pick private b, compute B = g^b mod p

    HA->>HB: send A (public)
    HB->>HA: send B (public)

    HA->>HA: shared = B^a mod p
    HB->>HB: shared = A^b mod p
    Note over HA,HB: Both derive the same g^(ab) mod p — never transmitted directly

    HA->>HA: AES-256-GCM encrypt(record, key=shared)
    HA->>HB: send encrypted record
    HB->>HB: AES-256-GCM decrypt(record, key=shared)
```

### Why These Two Together

RSA and Diffie–Hellman solve different problems and are used for different things in this app — this is deliberate, not redundant:

| | Solves | Used for |
|---|---|---|
| **RSA** | Authenticity + non-repudiation: proving *who* signed something and that it wasn't altered | Prescription signing (one-to-many verification — anyone can check a doctor's signature) |
| **Diffie–Hellman** | Confidentiality: establishing a shared secret between exactly two parties over an open channel | Hospital-to-hospital record sharing (a session key for one specific conversation) |

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

1. ✅ Laravel + Breeze scaffold, user roles (Admin/Hospital/Doctor/Patient/Pharmacist), RBAC middleware
2. ✅ Password authentication end-to-end (register/login/reset)
3. ✅ Verification gate: BMDC/document submission + Admin/Hospital approval workflow before an account becomes active
4. ✅ Functional prescription workflow (create, lookup by code, dispense) — the scaffold RSA signing attaches to
5. ⬜ RSA: doctor & hospital keypair generation, prescription signing, verification at lookup
6. ⬜ Diffie–Hellman: key-exchange endpoint + AES envelope encryption for hospital-to-hospital record sharing
7. ⬜ Lookup-code expiry/single-use hardening, second-factor check, rate limiting
8. ✅ UI polish for the five role dashboards
9. ⬜ Short written security report (threat model, what each crypto primitive defends against)

Live status and detailed task-by-task tracking (including bugs found/fixed) is kept in [`TODO.md`](TODO.md), updated as work happens — this list is a snapshot, that file is the source of truth.

## License

**© 2026 Salah Uddin. All Rights Reserved.**

This is a personal project, not open source. See [`LICENSE`](LICENSE) for full terms. No permission is granted to copy, reuse, redistribute, or create derivative works from this code without the author's explicit written consent — this applies regardless of whether the hosting repository is public or private.

---

*Status: functional multi-role scaffold complete (auth, approval workflow, prescription CRUD, dashboards). Core cryptography (RSA signing, Diffie–Hellman) is the next phase — see `TODO.md`.*
