# Changelog

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

### Notes
- The current release keeps guest-session storage; clearing browser cookies can make guest credits/history inaccessible.
- Bird identification remains an AI estimate and should be independently verified when accuracy is important.
