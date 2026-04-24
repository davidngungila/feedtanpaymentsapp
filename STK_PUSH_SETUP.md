# STK Push Notification Setup Guide

## Overview
To receive actual STK push notifications on your phone (0622239304), you need to configure real ClickPesa API credentials and disable test mode.

## Step 1: Get ClickPesa API Credentials

1. **Sign up for ClickPesa account** at https://clickpesa.com
2. **Create an application** in your ClickPesa dashboard
3. **Get your API credentials:**
   - API Key
   - Client ID

## Step 2: Configure Environment Variables

Add the following to your `.env` file:

```env
# ClickPesa API Configuration
CLICKPESA_API_BASE_URL=https://api.clickpesa.com/v2
CLICKPESA_API_KEY=your_actual_api_key_here
CLICKPESA_CLIENT_ID=your_actual_client_id_here
CLICKPESA_DEFAULT_CURRENCY=TZS
CLICKPESA_TEST_MODE=false  # IMPORTANT: Set to false for real STK push

# Messaging Service Configuration (optional)
MESSAGING_TOKEN=your_messaging_token_here
MESSAGING_SENDER_ID=FEEDTAN
```

## Step 3: Clear Configuration Cache

After updating your `.env` file, run:

```bash
php artisan config:clear
php artisan cache:clear
```

## Step 4: Test Real STK Push

Run the test command with real API:

```bash
php artisan test:payment 2000 0622239304 "Your Name"
```

## Step 5: Check Your Phone

You should receive:
1. **STK Push notification** on phone 0622239304
2. **USSD menu** to enter your PIN
3. **Payment confirmation** after entering PIN

## Step 6: Verify Payment Status

Check the payment status:

```bash
php artisan test:payment-status YOUR_ORDER_REFERENCE
```

## Important Notes

### Phone Number Format
- Use: `0622239304` or `25522239304`
- System automatically converts to international format

### Payment Flow
1. System sends USSD Push request to ClickPesa
2. ClickPesa sends STK push to your phone
3. You enter PIN on your phone
4. Payment is processed
5. Status updates to SUCCESS

### Test Mode vs Production
- **Test Mode** (`CLICKPESA_TEST_MODE=true`): Simulates responses, no real STK push
- **Production** (`CLICKPESA_TEST_MODE=false`): Real API calls, actual STK push

### Troubleshooting

**No STK Push Received:**
1. Verify API credentials are correct
2. Ensure `CLICKPESA_TEST_MODE=false`
3. Check phone number format
4. Verify ClickPesa account has sufficient balance

**Payment Failed:**
1. Check ClickPesa account balance
2. Verify phone number is active
3. Check if payment method is available for your carrier

### Security
- Never commit API credentials to version control
- Keep your `.env` file secure
- Use environment-specific configurations

## Web Interface Alternative

You can also test via web interface:
1. Visit: `http://127.0.0.1:8003/payments/initiate`
2. Fill in payment details
3. Submit form
4. Check phone for STK push

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify ClickPesa account status
3. Contact ClickPesa support for API issues
