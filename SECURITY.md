# SwasthoLink — Security Report

Threat model and cryptographic design for SwasthoLink, written as the security
report for this computer-security course project. For what's built and why in
product terms, see `PROJECT_OVERVIEW.md` and `PRODUCT.md`; for the open task
list see `TODO.md`.

## 1. Threat Model

**Assets to protect:**
- Prescription contents (medicines, dosage, patient identity) — medical
  privacy, and integrity against forgery.
- Provider identity — a pharmacist must be able to trust that a prescription
  really came from the doctor it claims to.
- Patient identity — a lookup code alone shouldn't be enough to pull up a
  stranger's medical record.
- Hospital-to-hospital shared records in transit and at rest in the database.

**Adversaries considered:**
1. **A forger** who wants to fabricate or alter a prescription (e.g. to
   obtain controlled medication) without a doctor's private key.
2. **A curious/malicious pharmacist or bystander** who has (or guesses) a
   lookup code and wants to see a patient's medical details without the
   patient's presence/consent.
3. **Someone with read access to the database** (a compromised DB backup, a
   misconfigured admin, an insider) trying to read prescription contents or
   hospital-shared records directly from storage.
4. **A network eavesdropper** between two hospitals during record sharing.
5. **A brute-forcer** hammering login or the lookup-code endpoint.

**Explicitly out of scope** (would need infrastructure this project doesn't
own): TLS/transport security (assumed handled by deployment, e.g. HTTPS
termination), OS/host compromise, physical device theft, and social
engineering of hospital staff.

## 2. What Each Primitive Defends Against

### Password hashing (bcrypt, via Laravel's default `Hash::make`)
Defends against: a database leak turning directly into usable credentials.
Passwords are never stored or logged in plaintext; bcrypt's per-password salt
and tunable cost defend against rainbow tables and make brute-forcing a
compromised hash set expensive.

### RSA-2048 digital signatures on prescriptions (`PrescriptionSigningService`)
Defends against: **adversary 1 (forgery)**. Every prescription is signed with
the issuing doctor's RSA private key (SHA-256 + PKCS#1 v1.5, `openssl_sign`)
over a canonical JSON payload covering every field a forger could plausibly
want to change — lookup code, doctor/hospital IDs, patient name/email,
medicines, notes. At pharmacy lookup, the pharmacist's screen shows a
pass/fail badge from re-verifying that signature against the doctor's public
key. A prescription with a tampered `medicines` field, or one written under a
doctor's name without their private key, fails verification visibly. This
does **not** prevent a doctor from misusing their own legitimate key (that's
a trust/policy problem, not a cryptographic one) — it only prevents someone
*without* the key from producing a signature that verifies.

Private keys are generated once, at admin approval time
(`RsaKeyService::generateKeyPair`), and stored encrypted-at-rest via
Laravel's `Crypt` facade (AES-256-CBC under `APP_KEY`) — never in plaintext
in the database. They're decrypted only in memory, for the duration of a
single sign/verify/unwrap call.

### Second factor at pharmacy lookup (patient phone last 4 digits)
Defends against: **adversary 2**. A lookup code by itself (`RX-XXXXXX`, 6
random base-36 characters ≈ 31 bits of entropy) is not treated as sufficient
to reveal medicines/notes. The pharmacist must additionally confirm the last
4 digits of the patient's phone number — something the code alone doesn't
leak — before the controller (`PrescriptionController::verify`) reveals
anything beyond the patient's name. This is a deliberate, cheap
"something the patient told you in person" check, not a strong 2FA scheme;
see §5 for its limits.

### Lookup-code expiry (30 days)
Defends against: **adversary 2's** attack surface shrinking over time. Old
prescriptions that have presumably already been filled (or are stale) stop
being valid lookup targets, bounding how long a leaked/guessed code stays
useful.

### Diffie-Hellman key exchange (`DhKeyExchangeService`, RFC 3526 Group 14 /
2048-bit MODP, `g=2`)
Defends against: **adversary 4** and, combined with the envelope encryption
below, **adversary 3**. Two hospitals derive a shared secret without either
one ever transmitting a value that alone reveals it — only the public values
`g^a mod p` and `g^b mod p` cross the wire/database; the private exponents
`a`, `b` never do. An eavesdropper who sees the whole exchange (both public
values, `p`, `g`) still faces the discrete-log problem to recover the
secret. In this app, the exchange is asynchronous and human-driven (hospital
A initiates, hospital B accepts later, as two separate HTTP requests from two
separate logins) rather than a live synchronous session — see §5 for the
practical concession that requires.

### AES-256-GCM envelope encryption for hospital record sharing
Defends against: **adversary 3**. The DH-derived secret is hashed
(SHA-256) down to a 256-bit key, used exactly once to AES-256-GCM-encrypt
the shared prescription payload. GCM's authentication tag also means a
tampered ciphertext fails to decrypt rather than silently returning garbage
— integrity, not just confidentiality. That raw AES key is then RSA-OAEP
*wrapped* separately for each of the two hospitals with their own RSA public
key (classic envelope-encryption pattern: one data key, N recipient-specific
wrapped copies), and the raw key itself is discarded immediately after
wrapping — never persisted. Anyone with direct database access sees only
ciphertext, an auth tag, and two RSA-wrapped blobs; without one of the two
hospitals' RSA private keys (themselves encrypted-at-rest under `APP_KEY`),
none of it is readable.

### Rate limiting
Defends against: **adversary 5**. Login already inherits Breeze's built-in
`RateLimiter`-based lockout (5 attempts per email+IP combination, with
backoff). The pharmacist lookup and second-factor verification endpoints are
throttled at the route level (`throttle:20,1` — 20 requests/minute) to slow
down code-guessing or phone-digit-guessing attempts without materially
affecting legitimate pharmacy workflow.

## 3. Trust Chain

```
Admin (root, seeded)
  └─ approves Hospital/Doctor/Pharmacist registrations after reviewing
     uploaded verification documents (BMDC certificate, NID, license)
       └─ approval is the moment RSA keys are generated (RsaKeyService),
          binding a cryptographic identity to a manually-vetted human/org
```

Nobody can sign a prescription or receive a hospital-shared record without
having been through this manual admin review — the crypto only starts
mattering *after* that human trust decision, and is meant to make the
*consequences* of that decision auditable and tamper-evident, not to replace
it.

## 4. Audit Trail

Every security-relevant action is recorded in `audit_logs`
(`AuditLog::record`): approvals/rejections, document views, prescription
creation, lookup attempts (successful or not), second-factor failures,
dispensing, and every step of a hospital share (initiated / accepted /
rejected / viewed). This doesn't prevent an attack, but it's what turns "we
think something happened" into "here's exactly what happened and when" —
necessary for any real incident response.

## 5. Known Limitations / Simplifications

Documented honestly rather than hidden, since a security report that only
lists strengths isn't a security report:

- **DH private-exponent custody window.** Because the key exchange spans two
  separate human-driven HTTP requests (hospital A initiates, hospital B
  accepts, possibly hours apart) rather than one live synchronous session,
  hospital A's private exponent `a` has to be held *somewhere* between those
  two requests. It's kept encrypted-at-rest (`Crypt`, AES-256-CBC under
  `APP_KEY`) for that window only, and is permanently deleted the instant the
  shared secret is derived and used — see
  `HospitalShare::initiator_private_exponent_encrypted`, nulled out in
  `ShareController::accept`. A fully live protocol (e.g. two servers actually
  talking to each other over a network) wouldn't need this; it's a concession
  to modeling both "hospitals" as accounts on the same single-server demo app.
  Forward secrecy still holds *after* a share completes — the exponent is
  gone, so a later `APP_KEY` compromise doesn't retroactively expose that
  particular derived secret, only the RSA-wrapped copies (which need the
  hospital's RSA private key, itself independently encrypted).
- **Second factor is low-entropy.** Phone last-4-digits is ~13 bits of
  entropy and is meant as a "did you actually talk to this patient" gate
  against opportunistic code-guessing, not a cryptographic-strength factor.
  A real deployment would want OTP-over-SMS or similar.
- **RSA private keys and DH exponents are ultimately protected by one
  application-wide key (`APP_KEY`).** This is standard for a Laravel app of
  this size, but it does mean `APP_KEY` compromise is a single point of
  failure for every private key at rest. A production system handling real
  patient data would likely want a dedicated KMS/HSM instead of
  application-level envelope encryption alone.
- **No TLS is implemented by this codebase** — encryption in transit is a
  deployment concern (HTTPS termination), not something this Laravel app
  does itself. Everything above protects data *at rest* and the *content*
  of what's exchanged, not the transport.
- **A doctor's own key can't be revoked mid-flight** — if a doctor's account
  is compromised, their signature still verifies until an admin intervenes;
  there's no CRL/revocation list, just account deactivation, which stops
  *new* signing but doesn't invalidate prescriptions already issued with a
  now-untrusted key. Acceptable for a course project; a real system would
  need revocation checking at verification time.
