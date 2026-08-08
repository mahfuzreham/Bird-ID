<?php
$OPENAI_API_KEY = 'YOUR_OPENAI_API_KEY';
$BIRD_MODEL = 'gpt-4.1-mini';
$PRICE_PER_SCAN = 10;
$CURRENCY = 'BDT';
$APP_URL = 'https://example.com/bird-id';

// Stripe
$STRIPE_SECRET_KEY = 'sk_test_REPLACE_ME';
$STRIPE_WEBHOOK_SECRET = 'whsec_REPLACE_ME';

// bKash Checkout
// Use 'sandbox' while testing. Change to 'live' only after your bKash merchant account is approved for production.
$BKASH_MODE = 'sandbox'; // sandbox | live
$BKASH_APP_KEY = 'YOUR_BKASH_APP_KEY';
$BKASH_APP_SECRET = 'YOUR_BKASH_APP_SECRET';
$BKASH_USERNAME = 'YOUR_BKASH_USERNAME';
$BKASH_PASSWORD = 'YOUR_BKASH_PASSWORD';

// Official bKash tokenized Checkout base URLs.
$BKASH_SANDBOX_URL = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
$BKASH_LIVE_URL = 'https://tokenized.pay.bka.sh/v1.2.0-beta';
