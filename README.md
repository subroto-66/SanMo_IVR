# 24/7 Twilio IVR Information & Recruitment System

A production-ready, automated, database-driven 24/7 inbound prerecorded IVR (Interactive Voice Response) phone system built with **Laravel 12**, **MySQL**, **Twilio Programmable Voice**, **Twilio TwiML**, **Twilio Programmable Messaging (SMS)**, and **Tailwind CSS**.

> **Note**: This system operates purely on deterministic prerecorded telephony and DTMF keypad menus using official Twilio TwiML. It does NOT use conversational AI, AI voice bots, OpenAI, or Vapi.

---

## 📞 Architecture Overview

```
Customer Dials Dedicated Twilio Number
             ↓
Twilio Voice Inbound Webhook (POST https://DOMAIN/twilio/voice)
             ↓
Laravel TwilioVoiceController & IvrService
             ↓
Twilio TwiML Response Generated
             ↓
Opening Welcome/Intro Plays → Main Recruitment Menu (<Gather> with barge-in)
             ↓
Caller Inputs DTMF Keypad Digit (1, 2, 3, 4, 5, 0, #)
             ↓
Twilio Menu Webhook (POST https://DOMAIN/twilio/menu)
             ↓
Database-Driven IVR Option Lookup (ivr_options table)
             ↓
   ┌───────────────────────┬───────────────────────┬───────────────────────┐
   ↓                       ↓                       ↓                       ↓
Option 1–4 (SMS + Audio)  Option 5 (Contact Req)  Keypad 0 & #            Invalid / Timeout
Plays confirmation &      Plays confirmation,     0: Returns to Menu      Plays error prompt,
dispatches SMS link       alerts staff via SMS,   #: Ends call with       tracks retries, or
in background.            and logs contact req.   goodbye gracefully.     hangs up if exceeded.
```

---

## 🚀 Key Features

1. **Zero-Code Audio Swapping**:
   - Administrators can upload and replace prerecorded MP3/WAV files for system greetings, the opening welcome, and keypad options (1 to 5) directly from the admin panel.
   - Replacing audio files never requires code changes, deployments, or server restarts.
2. **Text-to-Speech (TTS) Resilient Fallbacks**:
   - Whenever an audio recording has not yet been uploaded or is removed, the system speaks configurable text using Amazon Polly Joanna (`voice="Polly.Joanna"`), ensuring zero downtime.
3. **Database-Driven Keypad Options**:
   - Menu options (digits 1, 2, 3, 4, 5, 0, #) are managed in the `ivr_options` table.
   - Real-time duplicate digit conflict prevention.
   - Keypad options can be created, edited, enabled, or disabled on the fly.
4. **Twilio SMS Recruitment Link Dispatch**:
   - Callers pressing options 1 through 4 automatically receive a text message containing the appropriate recruitment or presentation link via Twilio Programmable Messaging.
   - Option 5 logs a personal contact request, texts the caller a confirmation, and sends an alert SMS to staff.
5. **Robust Retry & Timeout Protection**:
   - Keypad timeouts and invalid digits are caught gracefully.
   - Configurable retry limits (`MAX_IVR_RETRIES=3`). Calls terminate safely after reaching the threshold.
   - Pressing `0` safely loops back to the main menu without replaying the welcome greeting.
   - Pressing `#` terminates the call gracefully.
6. **Enterprise Security & Webhook Validation**:
   - Full Twilio webhook signature verification using official `Twilio\Security\RequestValidator` and `X-Twilio-Signature`.
   - CSRF protection is maintained across all web forms while safely exempted on `/twilio/*` endpoints.
   - Object storage support for Cloudflare R2 and AWS S3.
7. **Comprehensive Call Audit Logging**:
   - Tracks Call SID, Caller Number, Duration, Selected Options, and detailed JSON event audit trails for every call.

---

## 🛠️ Technology Stack

- **Framework**: Laravel 12 (PHP 8.3+)
- **Database**: MySQL 8.0+
- **Telephony & Messaging**: Twilio Programmable Voice (`twilio/sdk`), Twilio TwiML, Twilio Messaging API
- **Admin Frontend**: Laravel Blade, Tailwind CSS v4, Alpine.js
- **Storage Driver**: AWS S3 / Cloudflare R2 (production) / Local Public Disk (development)
- **Testing**: Pest PHP automated testing suite

---

## 📋 Server Requirements & PHP Extensions

- PHP `>= 8.2` (PHP 8.3 recommended)
- Required PHP Extensions:
  - `ext-pdo_mysql`
  - `ext-curl`
  - `ext-mbstring`
  - `ext-openssl`
  - `ext-fileinfo`
  - `ext-xml`
  - `ext-bcmath`
- Composer 2.x
- MySQL Server

---

## ⚙️ Environment Configuration (.env)

Create your `.env` file by copying `.env.example`:

```bash
cp .env.example .env
php artisan key:generate
```

Configure your MySQL and Twilio credentials in `.env`:

```env
APP_NAME="IVR System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ivr_system
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Twilio Credentials (Client provides their own Twilio Account)
TWILIO_ACCOUNT_SID=ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_PHONE_NUMBER=+1XXXXXXXXXX
TWILIO_WEBHOOK_VALIDATION=true

# Storage: Cloudflare R2 or AWS S3 (Production)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-ivr-bucket-name
AWS_ENDPOINT=https://your-account-id.r2.cloudflarestorage.com
AWS_URL=https://pub-your-domain.r2.dev
AWS_USE_PATH_STYLE_ENDPOINT=false
```

---

## 📦 Installation & Setup

1. **Install Composer Dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Run Migrations & Database Seeder**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=IvrSystemSeeder --force
   ```
   > Default seeded admin account:
   > - **Email**: `admin@example.com`
   > - **Password**: `password` *(Change this immediately after login)*

3. **Link Public Storage (if using local storage in dev)**:
   ```bash
   php artisan storage:link
   ```

4. **Compile Production Assets (if modifying UI)**:
   ```bash
   npm install && npm run build
   ```

---

## 📱 Twilio Console Configuration

1. Log in to your [Twilio Console](https://console.twilio.com).
2. Navigate to: **Phone Numbers** &rarr; **Manage** &rarr; **Active Numbers**.
3. Click on your dedicated phone number.
4. Scroll down to the **Voice Configuration** section:
   - Under **A Call Comes In**:
     - Select: **Webhook**
     - URL: `https://YOUR_DOMAIN/twilio/voice`
     - HTTP Method: **HTTP POST**
   - Under **Call Status Changes** (Status Callback):
     - URL: `https://YOUR_DOMAIN/twilio/status`
     - HTTP Method: **HTTP POST**
5. Save your phone number settings.
6. Verify SMS capability on your Twilio number under the Messaging configuration tab.

---

## 💻 Local Development with Ngrok

Because Twilio webhooks require public HTTPS endpoints, use `ngrok` or a similar tunnel for local testing:

1. **Start the Laravel local server**:
   ```bash
   php artisan serve
   ```
   *(Runs on http://localhost:8000)*

2. **Start the HTTPS Tunnel**:
   ```bash
   ngrok http 8000
   ```
   *(Copy the provided HTTPS URL, e.g., `https://abc-123.ngrok-free.app`)*

3. **Update `.env`**:
   ```env
   APP_URL=https://abc-123.ngrok-free.app
   TWILIO_WEBHOOK_VALIDATION=false
   ```
   *(Clear config cache: `php artisan config:clear`)*

4. **Point Twilio Voice Webhook** to:
   `https://abc-123.ngrok-free.app/twilio/voice` (POST).

---

## 🧪 Automated Testing

Execute the Pest test suite:

```bash
php artisan test
```

All 45 automated test cases cover:
- Incoming voice TwiML generation (`<Response>`, `<Play>`, `<Say>`, `<Gather>`)
- Keypad DTMF processing (Digits 1, 2, 3, 4, 5, 0, #)
- Barge-in functionality throughout greeting and menus
- Sequential keypad press audit trails and activity tracking
- Option 5 personal contact requests and staff alert SMS notifications
- Background queue SMS dispatching with zero voice latency
- Dynamic audio replacement without code modifications
- SMS link dispatch and missing number handling
- Timeout and invalid input retries and goodbye termination
- Admin authentication, option management, duplicate digit validation, and audio uploads
- Admin profile, password management, and notification settings
- Webhook signature validation (`X-Twilio-Signature`) and CSRF security

---

## 🎯 Verification & Acceptance Test Checklist

- [x] **Test 1**: Call the Twilio number &rarr; Welcome audio/speech plays &rarr; Main presentation plays &rarr; Keypad menu instructions play.
- [x] **Test 2**: Press `1` &rarr; Option 1 audio plays &rarr; System offers prompt to return to menu.
- [x] **Test 3**: Press `9` &rarr; Returns back to main menu.
- [x] **Test 4**: Press `2` &rarr; Option 2 audio plays.
- [x] **Test 5**: Press unrecognized digit (e.g., `8`) &rarr; Invalid selection message plays &rarr; Menu is retried.
- [x] **Test 6**: No input entered &rarr; Timeout message plays &rarr; Menu is retried. After 3 attempts, Goodbye plays and call terminates.
- [x] **Test 7**: Press `5` (SMS) &rarr; Caller hears confirmation &rarr; SMS message is delivered to caller's mobile number.
- [x] **Test 8**: Admin logs in &rarr; Audio Management &rarr; Uploads new MP3 for Option 1 &rarr; Immediate redial to phone number &rarr; New audio plays automatically without code change or redeployment.

---

## 🔒 Security Best Practices

- **Never** expose Twilio Auth Tokens to frontend JavaScript.
- Keep `TWILIO_WEBHOOK_VALIDATION=true` in production.
- Use HTTPS for all public webhooks and audio asset delivery.
- Set strong passwords for admin accounts.
