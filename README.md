# Bird ID 🦜 — AI Bird Identification & Bird Species Scanner

**Bird ID** is a lightweight, mobile-friendly web application for identifying birds from photographs. It is built for bird lovers, bird owners, photographers, wildlife learners, students and developers who want a simple **bird identification from photo** experience.

## SEO-friendly overview

Bird ID helps users **identify a bird by image**, discover a possible **bird species and scientific name**, understand visible identification clues, and get guidance for **baby/juvenile bird identification**. The project combines AI image analysis with a simple pay-per-scan model and guest access.

> AI identification is an estimate. Similar species, hybrids, juvenile plumage, poor lighting or incomplete photos can produce uncertain results. For scientific, legal, veterinary or wildlife-rescue decisions, consult an appropriate expert.

## ⭐ Key Features

- 🦜 AI bird identification from photo
- 📷 JPG, PNG and WEBP image upload
- 🐣 Baby bird / juvenile bird identification guidance
- 🔬 Possible bird species and scientific name
- 📊 Confidence estimate
- 🔍 Visible identification clues
- 🪶 Possible appearance changes as the bird grows
- 🔄 Alternative species when the result is uncertain
- 📚 Scan History for the current guest session
- 🗂️ Searchable starter Bird Species Directory
- 🔊 Bird Sound ID upload interface with server-side provider adapter
- 🛠️ Token-protected Admin Dashboard
- 🛡️ Lightweight session rate-limiter helper
- 📱 Mobile-friendly interface + PWA app shell
- 👤 Guest Mode — no registration or login
- 🪙 Pay-per-scan credits
- 💳 Stripe Checkout
- 🇧🇩 bKash Tokenized Checkout
- 🧪 bKash Sandbox mode
- 🔴 bKash Live mode
- 🔐 Server-side payment verification
- 🧾 Invoice and payment amount validation
- 🛡️ Duplicate-credit protection with database transactions
- 🗄️ MySQL payment and scan records
- ⚡ PHP + MySQL deployment suitable for cPanel/shared hosting/VPS
- 🔑 API secrets kept server-side

## 🆕 V2.1 Implementation Notes

Version **2.1.0** adds the foundation for the next platform stage:

- `species.json` — starter species catalog with common/scientific names, habitats and regions.
- `species.php` — searchable species directory.
- `sound.html` + `sound_id.php` — secure audio upload flow and provider adapter.
- `admin.php` — server-token protected dashboard with basic operational/payment analytics.
- `rate_limit.php` — reusable session rate limiter for public endpoints.
- `manifest.webmanifest` + `sw.js` — installable/offline app shell foundation.
- `migrations/2026-08-11-v2-1.sql` — favorites and community-correction tables.

### Bird Sound ID limitation

The sound feature intentionally **fails closed** until a verified bird-audio classification provider is configured. Set these values only in the private server configuration copied from `config.example.php`:

```php
$BIRD_SOUND_API_URL = 'YOUR_PROVIDER_ENDPOINT';
$BIRD_SOUND_API_KEY = 'YOUR_PRIVATE_SERVER_KEY';
```

No bird-audio provider credential was added to GitHub. The adapter expects a multipart `audio` upload and a JSON response containing one of `result`, `prediction`, or `label`. Adapt the request/response mapping if your selected provider uses a different API contract.

### Admin Dashboard

Configure a long random server-side token:

```php
$ADMIN_TOKEN = 'YOUR_LONG_RANDOM_ADMIN_SECRET';
```

Then open `admin.php` and sign in. Do not publish the token or place it in frontend JavaScript.

### V2.1 database migration

After the original `schema.sql` has been imported, apply:

```text
migrations/2026-08-11-v2-1.sql
```

This creates `favorites` and `corrections` without replacing the existing payment/scan tables.

## 🔎 How It Works

```text
Guest
  ↓
Choose scan package
  ↓
Stripe or bKash
  ↓
Server-side payment verification
  ↓
Scan credit added
  ↓
Upload bird photo
  ↓
AI image analysis
  ↓
Bird identification result
  ↓
1 scan credit consumed
```

No registration is required for the basic guest flow.

## 💰 Pay Per Scan

The default price is controlled by the server:

```php
$PRICE_PER_SCAN = 10;
$CURRENCY = 'BDT';
```

Example packages:

- 1 scan — ৳10
- 5 scans — ৳50
- 10 scans — ৳100

Change pricing only in the server configuration. Never trust a price submitted by the browser.

## 💳 Stripe Checkout

Stripe is available as an international payment option. The server creates the Checkout Session and calculates the order amount.

Scan credits are added only after a valid signed Stripe webhook is received and the payment/order data matches the pending order.

### Stripe webhook

Configure:

```text
https://YOUR-DOMAIN/bird-id/stripe_webhook.php
```

Event:

```text
checkout.session.completed
```

Never expose the Stripe secret key in frontend JavaScript.

## 🇧🇩 bKash Checkout API

Bird ID supports the official bKash Tokenized Checkout architecture for merchant accounts.

### Sandbox

For development/testing:

```php
$BKASH_MODE = 'sandbox';
```

### Live

For production:

```php
$BKASH_MODE = 'live';
```

Use your approved production bKash credentials only on the server. Never publish App Secret, password or other private credentials in GitHub.

### bKash payment flow

```text
Create local order
      ↓
Grant bKash access token
      ↓
Create bKash payment
      ↓
Redirect customer to bKash Checkout
      ↓
bKash callback
      ↓
Execute payment
      ↓
Query status if required
      ↓
Validate status + amount + invoice
      ↓
Mark payment paid
      ↓
Add scan credits once
```

The application must never add credits from a browser-side `success=true` parameter.

### bKash configuration

Copy `config.example.php` to `config.php` and set your approved merchant values. Keep all private values outside Git.

## 🤖 AI Bird Identification

The backend sends the uploaded image to a vision-capable AI model and requests a structured result including:

- Possible bird name
- Scientific name
- Confidence estimate
- Juvenile/adult stage guidance
- Visible identification clues
- Expected mature appearance
- Alternative species

Example configuration:

```php
$OPENAI_API_KEY = 'YOUR_OPENAI_API_KEY';
$BIRD_MODEL = 'gpt-4.1-mini';
```

Model availability and API pricing can change, so use the model configuration appropriate for your OpenAI account.

## 👤 Guest Mode

There is no required account registration or login.

A secure random server session identifies each guest. Purchased credits are attached to that session.

### Guest limitation

Because this is a guest system, clearing cookies, changing browser/device or losing the session can make previously purchased credits inaccessible. For a larger production service, an optional recovery mechanism can be added later using a signed payment receipt, phone number or email.

## 🗄️ Database Structure

Import `schema.sql` into MySQL, then apply the V2.1 migration.

Main tables:

- `users` — guest identities and scan credits
- `payments` — payment provider, invoice, amount, scan quantity and status
- `scan_logs` — successful bird identification results
- `favorites` — saved bird species per user identity
- `corrections` — community correction submissions awaiting review

## ⚙️ cPanel / VPS Installation

### Requirements

- PHP 8+
- MySQL/MariaDB
- PDO MySQL
- cURL
- HTTPS/SSL
- OpenAI API access
- Stripe account for Stripe payments
- Approved bKash merchant/API access for bKash payments
- Optional verified bird-audio API for Sound ID

### Install

1. Create a MySQL database and database user.
2. Import `schema.sql`.
3. Apply `migrations/2026-08-11-v2-1.sql`.
4. Upload the project to your website.
5. Copy `config.example.php` → `config.php`.
6. Copy `db_config.example.php` → `db_config.php`.
7. Add private API/database credentials.
8. Make sure private configuration files cannot be downloaded publicly.
9. Enable HTTPS.
10. Configure Stripe webhook.
11. Configure the official bKash callback and verification flow.
12. Optionally configure a verified bird-audio provider for Sound ID.
13. Test payments in the appropriate test/sandbox environment.
14. Move to production only after successful verification.

## 🔐 Security Checklist

Never commit:

```text
config.php
db_config.php
.env
OpenAI API keys
Stripe secret keys
Stripe webhook secrets
bKash App Secret
bKash Password
Database passwords
Bird Sound provider API keys
Admin tokens
```

Production security should include:

- Server-side amount calculation
- Stripe webhook signature verification
- bKash server-side payment verification
- Invoice/order matching
- Exact amount matching
- Payment-status validation
- One-time crediting
- Database transactions
- HTTPS
- Rate limiting/CAPTCHA for public usage
- Restricted/noindex admin dashboard

## ❤️ Support Bird ID Development

Bird ID is being developed as a useful public **AI bird identification tool** for bird lovers, learners, photographers, students and wildlife enthusiasts.

If you find Bird ID useful and want to help with continued development, hosting, AI/API costs, security, maintenance and new bird-identification features, you can voluntarily support the project through the following ShurjoPay payment link:

### 💝 Donate / Support the Project

**ShurjoPay Support & Donation Link:**

https://pay.shurjopayment.com/d21kNExwjP

Every voluntary contribution can help keep the project running and support future improvements such as a larger bird database, better juvenile-bird recognition, multilingual results, bird sound identification and additional payment options.

This link is presented as voluntary project support. It should not be described as a tax-deductible charitable donation unless the recipient organization and applicable law specifically provide that status.

## 🌍 SEO Keywords

**Primary keywords:**

`AI bird identification`, `bird identification from photo`, `identify bird by image`, `bird species identifier`, `bird photo scanner`, `bird species recognition`, `AI bird scanner`, `bird identification app`, `bird identification website`.

**Long-tail keywords:**

`identify a bird from a photo`, `identify bird species using AI`, `free bird identification tool`, `baby bird identification from photo`, `juvenile bird identification`, `bird scientific name finder`, `bird species detector`, `bird recognition AI`, `Bangladesh bird identification`, `Bangla bird identification`.

**Bangla keywords:**

`পাখি চেনার অ্যাপ`, `ছবি দিয়ে পাখি চেনা`, `পাখির ছবি দিয়ে জাত চেনা`, `পাখি শনাক্ত করার AI`, `বাচ্চা পাখি চেনা`, `পাখির প্রজাতি চেনা`, `পাখির বৈজ্ঞানিক নাম`, `ছবি দেখে পাখি শনাক্ত`.

## 🚀 Roadmap

### V2.1 foundation

- Larger global bird species database
- Regional species suggestions
- Bird habitat and distribution information
- Searchable species directory
- Bird sound identification adapter
- Identification history
- Admin dashboard and payment analytics
- Rate limiting foundation
- PWA/mobile foundation
- Favorites and community-correction data model

### V2.2 / V3 next

- Verified large-scale species dataset with source attribution
- Production bird-audio classifier integration
- Full favorites UI and correction submission/review workflow
- Expert review queue
- Optional user accounts and credit recovery
- Multilingual results
- Improved juvenile bird recognition evaluation
- SEO landing pages for species and regions
- More payment providers and deeper payment analytics

## ⚠️ Responsible Use

Do not use an AI result as the sole basis for wildlife ownership, trade, rescue, veterinary treatment, legal identification or scientific publication. When accuracy matters, confirm the species with a qualified ornithologist, wildlife authority, veterinarian or other appropriate expert.

Do not use Bird ID to facilitate illegal capture, trafficking, sale or collection of protected wildlife.

## 🤝 Contributions

Issues, documentation improvements, translations, feature suggestions and responsible code contributions are welcome.

Please keep all payment credentials, API keys, database passwords, admin tokens and private configuration outside Git commits.

## 📜 License

Add the license that matches your intended distribution model before publishing a stable production release.

---

**Bird ID — Identify birds from photos with AI. 🦜**

Built for bird lovers, learners, photographers and developers.
