<?php

use App\Http\Controllers\Admin\AudioManagementController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CallLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IvrOptionController;
use App\Http\Controllers\Admin\IvrSettingController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Twilio\TwilioVoiceController;
use Illuminate\Support\Facades\Route;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Public Twilio Webhook Endpoints
|--------------------------------------------------------------------------
| These routes handle inbound telephone calls and DTMF keypad callbacks
| directly from Twilio. CSRF is exempted via bootstrap/app.php, and
| signature verification is handled via the twilio.validate middleware.
|
*/
Route::prefix('twilio')->middleware(['twilio.validate'])->group(function () {
    Route::post('/voice', [TwilioVoiceController::class, 'voice'])->name('twilio.voice');
    Route::post('/menu', [TwilioVoiceController::class, 'menu'])->name('twilio.menu');
    Route::post('/status', [TwilioVoiceController::class, 'status'])->name('twilio.status');
    Route::post('/sms-status', [TwilioVoiceController::class, 'smsStatus'])->name('twilio.sms.status');
});

/*
|--------------------------------------------------------------------------
| IVR Audio Streaming (Public / Phone & Browser Playback)
|--------------------------------------------------------------------------
| Streams audio files directly with HTTP 206 Partial Content / Range support
| ensuring uninterrupted playback in modern browsers and Twilio voice calls.
|
*/
Route::get('/ivr/audio/stream/{filename}', [AudioManagementController::class, 'stream'])
    ->where('filename', '.*')
    ->name('ivr.audio.stream');


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::get('/signin', fn () => redirect()->route('admin.login'))->name('signin');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Admin Protected IVR Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index']);
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // IVR Base redirect
    Route::get('/ivr', function () {
        return redirect()->route('admin.ivr.audio.index');
    })->name('ivr.index');

    // Audio Management
    Route::prefix('ivr/audio')->name('ivr.audio.')->group(function () {
        Route::get('/', [AudioManagementController::class, 'index'])->name('index');
        Route::post('/upload', [AudioManagementController::class, 'upload'])->name('upload');
        Route::post('/delete', [AudioManagementController::class, 'destroy'])->name('delete');
    });

    // IVR Keypad Options CRUD
    Route::prefix('ivr/options')->name('ivr.options.')->group(function () {
        Route::get('/', [IvrOptionController::class, 'index'])->name('index');
        Route::get('/create', [IvrOptionController::class, 'create'])->name('create');
        Route::post('/', [IvrOptionController::class, 'store'])->name('store');
        Route::get('/{option}/edit', [IvrOptionController::class, 'edit'])->name('edit');
        Route::put('/{option}', [IvrOptionController::class, 'update'])->name('update');
        Route::delete('/{option}', [IvrOptionController::class, 'destroy'])->name('destroy');
        Route::post('/{option}/toggle', [IvrOptionController::class, 'toggle'])->name('toggle');
    });

    // IVR Settings
    Route::prefix('ivr/settings')->name('ivr.settings.')->group(function () {
        Route::get('/', [IvrSettingController::class, 'index'])->name('index');
        Route::put('/', [IvrSettingController::class, 'update'])->name('update');
    });

    // Call Logs & History
    Route::prefix('ivr/logs')->name('ivr.logs.')->group(function () {
        Route::get('/', [CallLogController::class, 'index'])->name('index');
        Route::get('/{callLog}', [CallLogController::class, 'show'])->name('show');
    });

    // Profile, Security & Dynamic Notification Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::put('/', [ProfileController::class, 'updateProfile'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::put('/notifications', [ProfileController::class, 'updateNotifications'])->name('notifications');
        Route::post('/notifications/test', [ProfileController::class, 'sendTestNotification'])->name('notifications.test');
    });
});

// Top-level profile redirect
Route::get('/profile', fn () => redirect()->route('admin.profile'))->name('profile.direct');
