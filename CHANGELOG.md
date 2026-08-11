# Changelog

## 2.1.0 — 2026-08-11

### Added
- Starter searchable Bird Species Directory backed by `species.json`.
- Bird Sound ID upload interface and a server-side provider adapter.
- Token-protected admin dashboard with user, scan, payment-count and revenue summaries.
- Favorites and community-correction database migration for the next UI iteration.
- Session-based lightweight rate-limiter helper for public endpoints.
- PWA manifest and offline app-shell service worker.
- New navigation links from the main page to species, sound and history features.

### Security
- Bird Sound ID credentials are configuration-only and are never stored in the repository.
- Admin access uses a server-side token configured outside source control.
- The sound endpoint rejects unsupported file types and oversized uploads.
- No API keys, payment secrets or database passwords were added to the repository.

### Important limitation
- Bird Sound ID is not activated by default because a verified bird-audio classification provider endpoint and server-side API key were not available in the repository. The adapter is ready for a compatible provider and fails closed with a clear configuration error.
- The starter species catalog is intentionally small and should be expanded/verified before being treated as a comprehensive scientific database.

## 2.0.0 — 2026-08-10

### Added
- Scan History page showing the latest 50 successful results for the current guest session.
- Mobile-friendly navigation from the main Bird ID page to scan history.
- SEO meta description on the main application page and history page.
- Clear V2 version marker via `VERSION`.

### Improved
- Bird ID home page now explains the AI identification workflow more clearly.
- Safer HTML escaping on the history output.
- Documentation now describes the V2 release and the distinction between bKash, Stripe and ShurjoPay.

### Security
- No API keys, payment secrets or database credentials were added to the repository.
- Existing server-side payment verification requirements remain unchanged.
