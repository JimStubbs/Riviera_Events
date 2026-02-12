# Riviera Events Engine — PRD v2 + Technical Spec (Draft)

**Target domain:** `calendar.puertoaventurasliving.com`

**Positioning:** Standalone, embeddable events platform (not a WordPress plugin). Designed to scale to regional usage and evolve into white-label SaaS.

---

## 0) Decisions Locked In

- **Recurring events:** Yes (MVP)
- **Timezone:** Per-location (location carries an IANA timezone)
- **Premium listing pricing:** Flat fee **200 MXN** (initial)
- **Premium applies to:** Entire **series** (recurring series) or single event if not recurring
- **Ads geo-targeting:** By **event town** (derived from selected location)
- **Organizer edits:** Allowed; changes create a **draft** and require **admin re-approval**; previously approved/published version stays public until re-approved
- **Auth:** Email/password

---

## 1) MVP Scope (What “Done” Means)

### Visitor
- Browse events in **Stacked List** view (default)
- Switch views: **Month/Week/Day** (can ship Month + List first; Week/Day can be flagged if needed)
- Filter/search:
  - Town (multi-select)
  - Location (multi-select)
  - Category (multi-select)
  - Date range
  - Keyword
- Event detail page
- Add to Google Calendar
- Download event `.ics`
- Subscribe to a **calendar feed** endpoint (ICS) (filtered + unfiltered)

### Submitter (Free, no account)
- Submit event form
- Email verification link (must verify before admin sees it, or before publish—see workflow)
- reCAPTCHA
- Status: `pending_review` after verification

### Organizer (Paid)
- Create account (email/password)
- Create event or event series
- Choose **Premium** (200 MXN) → Stripe Checkout
- After payment: listing marked `paid_pending_review`
- Can edit event/series; edits create **draft changes** and require re-approval

### Admin
- Approve/reject
- Bulk actions
- CRUD: towns/locations/categories
- Featured banner management + ordering + expiration
- Ads management (manual placements + town targeting)
- CSV import

---

## 2) Information Architecture / URL Plan

- `/` → default list view
- `/events/{slug}` → event detail
- `/embed` → lightweight embed-friendly list view (optional alias of `/`)
- `/ics` → full calendar feed
- `/ics?town=tulum&category=music` → filtered feed
- `/admin/*` → admin (Filament recommended)
- `/dashboard/*` → organizer dashboard

---

## 3) Core Data Model (Proposed)

### 3.1 Towns / Locations
Because “any town in Riviera Maya” implies an open set, but we still need consistent targeting.

**towns**
- `id`
- `name` (e.g., Tulum)
- `slug`

**locations**
- `id`
- `town_id`
- `name` (e.g., Parque Dos Aguas)
- `address` (optional)
- `lat`, `lng` (optional)
- `timezone` (IANA string, e.g. `America/Cancun`)
- `is_active`

Rationale: Town is used for targeting; location is the venue/area.

### 3.2 Categories
**categories**
- `id`, `name`, `slug`, `is_active`

### 3.3 Event Series + Events
We support recurrence in MVP via a **series** object + generated instances on demand.

**event_series**
- `id`
- `title`
- `description`
- `image_path` / `image_url`
- `organizer_user_id` (nullable for free submissions)
- `contact_email` (for free submissions)
- `town_id`
- `location_id`
- `category_id`
- `timezone` (copied from location at creation)
- `is_all_day` (bool)
- `starts_at_local` (datetime)
- `ends_at_local` (datetime)
- **Recurrence**
  - `rrule` (string, RFC 5545 RRULE)
  - `until_local` (datetime, nullable)
  - `count` (int, nullable)
  - `exdates` (json array of local dates to skip)
- **Monetization**
  - `is_premium` (bool)
  - `premium_price_mxn` (int default 200)
  - `premium_paid_at` (datetime nullable)
  - `stripe_checkout_session_id` (string nullable)
  - `stripe_payment_intent_id` (string nullable)
- **Workflow**
  - `status` enum: `draft`, `pending_email_verification`, `pending_review`, `approved`, `rejected`, `archived`
  - `published_data` (json) — last approved snapshot
  - `draft_data` (json) — proposed edits awaiting review
  - `last_approved_at`
  - `last_submitted_at`
- `created_at`, `updated_at`

We can avoid a separate `events` table for instances in MVP by *expanding* series into instances for the requested range.

If we later need per-instance overrides (e.g. “this Friday starts at 8pm”), we add `event_overrides`.

### 3.4 Featured and Sponsorships
**featured_items**
- `id`
- `event_series_id`
- `starts_at` / `ends_at`
- `sort_order`
- `is_active`

### 3.5 Ads
**ads**
- `id`
- `name`
- `placement` enum: `list_inline`, `sidebar`, `leaderboard`, `carousel_sponsor`
- `town_id` nullable (null = global)
- `image_path` / `html_snippet` (MVP: image + link)
- `target_url`
- `starts_at`, `ends_at`
- `is_active`
- Optional counters: `impressions`, `clicks`

### 3.6 Views / Analytics (lightweight MVP)
**event_views** (optional MVP)
- `id`
- `event_series_id`
- `viewed_at`
- `ip_hash` (privacy-safe)
- `user_agent_hash`

---

## 4) Workflows (Precise)

### 4.1 Free Submission
1. User submits form → create `event_series` in `pending_email_verification`
2. Email with signed verification link
3. On verification:
   - apply submission data → `draft_data`
   - set status `pending_review`
4. Admin approves:
   - copy `draft_data` → `published_data`
   - status `approved`

### 4.2 Paid Organizer Submission (Premium)
1. Organizer creates series/event in dashboard (status `draft`)
2. Chooses “Premium” → Stripe Checkout 200 MXN
3. Stripe webhook confirms payment:
   - mark `is_premium=1`, `premium_paid_at=now()`
   - set status `pending_review`
4. Admin approves → publish snapshot

### 4.3 Edit + Re-approval (Organizer)
1. Organizer edits an **approved** series
2. Save goes to `draft_data`
3. Set status to `pending_review`
4. Public site continues to render from `published_data`
5. Admin approves:
   - overwrite `published_data` with `draft_data`

### 4.4 Rejection
- Status `rejected`, keep reason string
- Organizer can revise → status back to `pending_review`

---

## 5) Recurrence Rules (MVP)

### Supported recurrence patterns (UI)
- Daily
- Weekly (select weekdays)
- Monthly (by day-of-month or “nth weekday”)

### Storage
- Store as RFC5545 `RRULE` string (e.g., `FREQ=WEEKLY;BYDAY=MO,WE,FR;UNTIL=20261231T235959`)
- Store `exdates` for skipped occurrences

### Expansion
- Server expands instances for requested date range using an RRULE library.
- Enforce caps:
  - `max_instances_per_query` (e.g., 500)
  - hard limit on date range requested (e.g., 365 days)

---

## 6) Search/Filtering Requirements

- All filters must be server-side (Laravel query) with AJAX endpoints.
- Indexed fields:
  - `town_id`, `location_id`, `category_id`, `status`, `is_premium`
- For recurrence, filtering works at the **series** level plus instance expansion for date ranges.

---

## 7) Embedding

### MVP: iFrame
- Provide snippet:

```html
<iframe
  src="https://calendar.puertoaventurasliving.com/?town=tulum"
  width="100%"
  height="1000"
  style="border:0;"
  loading="lazy"
></iframe>
```

### Query params
- `town` (slug or comma-separated)
- `location` (slug)
- `category` (slug)
- `view` = `list|month|week|day`

### Phase 2: JS embed
- Dynamic height messaging + theming options

---

## 8) Integrations

### 8.1 Google Calendar
Use simple URL-based “Add to Google Calendar” link on event detail.

### 8.2 ICS
- Per-event instance ICS download
- Feed endpoint:
  - `/ics` (all approved)
  - `/ics?town=tulum` etc.

---

## 9) Admin Panel (Recommendation)

Use **Filament** (Laravel) for admin + organizer dashboard:
- Fast CRUD + permissions
- Works well on shared hosting

Roles:
- `admin`
- `organizer`

---

## 10) Hosting Notes / Risks (Hostinger + WordPress)

Because the main site is WordPress on Hostinger:
- This calendar should run as a **separate Laravel app** on the subdomain.
- Requirement: subdomain document root must point to Laravel `/public` directory.
- If Hostinger plan blocks this, we can:
  - Use a separate Hostinger sub-account/app hosting slot, or
  - Use a small VPS for the calendar (still embedded into WP via iframe).

We also need:
- SMTP credentials for verification emails
- Cron (nice-to-have for queued jobs; can run without initially)

---

## 11) Payments (Stripe)

- Currency: **MXN**
- Product: “Premium Event Series Listing” — 200 MXN
- Checkout session includes metadata: `event_series_id`
- Webhook endpoint: `/stripe/webhook`
- Verify signatures using Stripe signing secret.

---

## 12) UX Reference

Inspiration: `cityspark.com` (browse + filter experience). We’ll adapt to a simpler first-pass UI with fast list scanning.

---

## 13) MVP Backlog (Epics)

1. **Project scaffold**: Laravel app, env config, deployment notes
2. **Auth + roles**: admin + organizer
3. **Core taxonomy**: towns, locations, categories
4. **Event series CRUD**: create/edit, draft/publish snapshots
5. **Public browse**: list + filters + detail
6. **Recurrence engine**: rrule storage + expansion + exdates
7. **Moderation**: pending review queue + approve/reject
8. **Email verification**: free submissions
9. **Stripe checkout**: pay premium + webhook
10. **Featured banner**: ordered list
11. **Ads**: placements + town targeting
12. **CSV import**: validation + preview + bulk publish
13. **ICS**: per-event + feed

---

## 14) Open Questions (Still)

1. Do we need bilingual content (EN/ES) in MVP?
2. Image hosting: upload to app storage vs external URLs only?
3. Do we need a “Business Directory integration” link-out now or later?

