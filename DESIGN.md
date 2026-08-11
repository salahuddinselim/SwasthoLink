# Design System

<!-- impeccable:design-schema 1 -->

## Visual Identity

**Style:** Accessible & Ethical — high contrast, clear focus states, semantic color usage. Chosen because SwasthoLink serves both healthcare professionals and the general public in Bangladesh, where trust and legibility matter more than visual flair.

**Color strategy:** Restrained. One brand blue carries navigation, links, primary actions, and the "active" status state; one green is reserved for "approved/verified" semantics; red is reserved for destructive/rejected states. No decorative color beyond that.

## Tokens

Defined in `tailwind.config.js` under `theme.extend.colors`:

| Token | Hex | Used for |
|---|---|---|
| `brand-50` | `#F0F9FF` | Page background |
| `brand-100` | `#E0F2FE` | Subtle fills (active nav state, active-status badges, role pill) |
| `brand-500` | `#0EA5E9` | Focus rings |
| `brand-600` | `#0284C7` | Links, checkboxes |
| `brand-700` | `#0369A1` | Primary buttons, wordmark, active nav text |
| `brand-800` | `#075985` | Primary button hover |
| `brand-900` | `#0C4A6E` | Headings on light backgrounds |
| `accent` (`#16A34A`) | | Reserved for future "verified"/success semantics beyond the existing green status badges |

Status badges (pre-existing, kept as-is — already semantically correct):
- Green (`bg-green-100 text-green-800`) — active/approved accounts, success flash messages
- Amber (`bg-amber-100 text-amber-800`) — pending accounts
- Red (`bg-red-100 text-red-800` / `text-red-700`) — rejected accounts, destructive actions, errors
- Brand blue (`bg-brand-100 text-brand-700`) — active (not-yet-dispensed) prescriptions

## Typography

**Figtree**, loaded via bunny.net (`fonts.bunny.net/css?family=figtree:400,500,600,700`), applied as the single base font (`font-sans` in Tailwind config). Chosen from the "Medical Clean" pairing (Figtree + Noto Sans) for zero migration cost — Figtree was already Breeze's default font — and because the app is mostly forms/tables/badges rather than long-form prose, so a heading/body font split wasn't worth the added complexity.

## Layout Patterns

- **Guest layout** (`layouts/guest.blade.php`): centered white card (`max-w-md`) on a `brand-50` background, wordmark above the card as a text link to `/`.
- **App layout** (`layouts/app.blade.php`): top nav (white, `border-b border-gray-100`) + `brand-50` page background + white content cards (`bg-white shadow-sm rounded-lg`).
- **Nav bar**: wordmark (brand-700, bold) on the left, role-aware primary links (Dashboard always; Approvals for Admin; New Prescription for Doctor — all hidden until the account is `active`), a role pill (`bg-brand-100 text-brand-700`, uppercase) + name dropdown on the right.
- **Dashboards**: stat cards in a `grid grid-cols-2 md:grid-cols-4 gap-4` of white cards, no colored borders/stripes on the cards themselves — hierarchy comes from card grouping and the stat number size, not decoration.

## Components

Shared Blade components under `resources/views/components/`:
- `primary-button` — solid `brand-700`, the only "loud" color in the UI, reserved for the primary action per screen.
- `secondary-button` — white/bordered, brand-colored focus ring only.
- `danger-button` — red, for destructive actions (reject, delete).
- `text-input` — brand-colored focus ring/border, otherwise neutral gray.
- `nav-link` / `responsive-nav-link` — brand-colored active state (underline on desktop, left-border + tinted background on mobile — a standard functional active-state indicator, not decorative).

## Known Gaps (not yet addressed)

- No visible required-field asterisks on registration forms.
- No loading/disabled state on submit buttons during file uploads.
- No inline (on-blur) client-side validation — all validation is server-side round-trip.
- No dark mode variant defined yet.
