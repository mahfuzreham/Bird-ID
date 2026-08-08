# Bird ID 🦜

Guest-mode AI bird identification web app with pay-per-scan credits.

## Features
- No registration/login required
- Bird photo upload and AI identification
- Pay-per-scan credits
- Stripe Checkout + signed webhook flow
- bKash integration scaffold for official Checkout/Execute/Query APIs
- MySQL payment and scan records
- Server-side API secrets

## Live setup
1. Import `schema.sql`.
2. Copy `config.example.php` to `config.php` and add OpenAI, Stripe and bKash credentials.
3. Copy `db_config.example.php` to `db_config.php` and add MySQL credentials.
4. Use HTTPS.
5. Configure Stripe webhook to `stripe_webhook.php` for `checkout.session.completed`.
6. Complete the official bKash API flow in `bkash_create.php` and `bkash_verify.php` for your merchant environment.

Never put payment/API secrets in frontend JavaScript and never trust a browser-side payment-success flag.
