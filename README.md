# Bird ID 🦜

Guest-mode AI bird identification web app with **pay-per-scan credits**.

## What this project does

A visitor can open the website without registration/login, buy scan credits, upload a bird photo, and receive an AI-assisted identification result.

### Main flow

`Guest → Buy Scan → Stripe or bKash → Verified Payment → Scan Credit → Upload Bird Photo → AI Identification`

## Features

- No registration/login required
- Guest session with scan credits
- Bird photo upload (JPG/PNG/WEBP)
- AI-assisted bird identification
- Juvenile/baby bird-aware identification prompt
- Confidence and alternative-species guidance
- Pay-per-scan pricing controlled server-side
- Stripe Checkout
- Stripe signed webhook verification
- bKash Tokenized Checkout API
- bKash **Sandbox and Live mode switch**
- bKash server-side token grant + payment create
- bKash server-side Execute/Query verification
- Payment amount/invoice validation
- Database transaction to prevent duplicate credits
- MySQL payment and scan records
- API secrets kept server-side

## Payment modes

### Stripe

Stripe Checkout is available from the main page. The server creates the Checkout Session and calculates the amount. Scan credits are added only after the signed `checkout.session.completed` webhook is validated.

### bKash Sandbox

Use this while developing/testing:

```php
$BKASH_MODE = 'sandbox';
```

The project uses the configured bKash sandbox base URL and the official Tokenized Checkout API flow.

### bKash Live

After your bKash merchant account is approved for production, switch only this value:

```php
$BKASH_MODE = 'live';
```

Then enter your **live** bKash App Key, App Secret, Username and Password. Do not put these credentials in GitHub. Keep them only in the server-side `config.php` or environment variables.

Live API base URL configured by the project:

`https://tokenized.pay.bka.sh/v1.2.0-beta`

Sandbox API base URL configured by the project:

`https://tokenized.sandbox.bka.sh/v1.2.0-beta`

## bKash Checkout flow

1. `bkash_create.php` creates a pending local order.
2. Server requests a bKash access token using merchant credentials.
3. Server creates the bKash Checkout payment with the exact scan amount and local invoice number.
4. User is redirected to the official bKash Checkout URL.
5. bKash returns the payment ID to `bkash_callback.php`.
6. Server executes the payment and falls back to payment-status query when necessary.
7. Server validates transaction status, merchant invoice number and exact amount.
8. The database transaction marks the payment as paid and adds scan credits exactly once.

**Never trust a browser-side `success=true` parameter.** Credits must come only after server-side bKash verification.

## Configuration

Copy:

- `config.example.php` → `config.php`
- `db_config.example.php` → `db_config.php`

Set:

- OpenAI API key/model
- Price per scan
- Public HTTPS app URL
- Stripe secret key and webhook secret
- bKash mode: `sandbox` or `live`
- bKash merchant credentials
- MySQL credentials

## Database

Import `schema.sql` into MySQL. It creates:

- `users` — guest session identities and scan credits
- `payments` — Stripe/bKash orders and payment state
- `scan_logs` — successful AI scan records

## cPanel deployment

1. Create a MySQL database and database user.
2. Import `schema.sql`.
3. Upload the project into `public_html/bird-id/` (or your preferred document root).
4. Create `config.php` and `db_config.php` from the example files.
5. Use PHP 8+ with cURL and PDO MySQL enabled.
6. Enable HTTPS.
7. Configure the Stripe webhook:
   `https://YOUR-DOMAIN/bird-id/stripe_webhook.php`
   Event: `checkout.session.completed`
8. bKash callback URL used by the project:
   `https://YOUR-DOMAIN/bird-id/bkash_callback.php`
9. Test with bKash Sandbox first.
10. After approval, change `BKASH_MODE` to `live` and replace credentials with production credentials.

## Security checklist

- Never commit `config.php` or `db_config.php`.
- Never expose Stripe secret keys, OpenAI keys or bKash credentials in JavaScript.
- Keep `.gitignore` configured for secrets.
- Use HTTPS in production.
- Validate payment amount, invoice/order ID and payment status server-side.
- Credit a payment only once inside a database transaction.
- Add rate limiting/CAPTCHA before opening the service publicly.
- Consider an optional recovery mechanism for guest-paid credits because clearing browser cookies can lose access to a guest session.

## Important note about the supplied ShurjoPayment URL

The following URL was supplied for reference:

`https://pay.shurjopayment.com/d21kNExwjP`

It is **not used as the bKash Checkout endpoint** in this project. ShurjoPay and bKash are separate payment integrations. If you want ShurjoPay added as a third gateway, it should be implemented separately with its own server-side verification flow.

## Production readiness

The code is structured for deployment, but payment providers still require your own approved merchant account and production credentials. Do not switch to bKash Live until your merchant credentials and production access have been issued by bKash and you have completed a successful sandbox test.

## License

Add your preferred license before public distribution.
