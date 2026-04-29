<?php

use App\Http\Controllers\Api\LegacyMobileApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login.php', [LegacyMobileApiController::class, 'login']);
Route::post('/register.php', [LegacyMobileApiController::class, 'register']);
Route::post('/otp_verify.php', [LegacyMobileApiController::class, 'verifyOtp']);
Route::post('/resend_otp.php', [LegacyMobileApiController::class, 'resendOtp']);
Route::get('/profile.php', [LegacyMobileApiController::class, 'profile']);
Route::get('/requests.php', [LegacyMobileApiController::class, 'requests']);
Route::post('/create_request.php', [LegacyMobileApiController::class, 'createRequest']);
Route::post('/generate_caption.php', [LegacyMobileApiController::class, 'generateCaption']);
Route::get('/calendar.php', [LegacyMobileApiController::class, 'calendar']);
Route::post('/update_profile.php', [LegacyMobileApiController::class, 'updateProfile']);
Route::get('/notifications.php', [LegacyMobileApiController::class, 'notifications']);
Route::post('/mark_notification_read.php', [LegacyMobileApiController::class, 'markNotificationRead']);
Route::post('/mark_messages_read.php', [LegacyMobileApiController::class, 'markMessagesRead']);
Route::get('/request_details.php', [LegacyMobileApiController::class, 'requestDetails']);
Route::get('/messages.php', [LegacyMobileApiController::class, 'messageThreads']);
Route::get('/message_thread.php', [LegacyMobileApiController::class, 'messageThread']);
Route::post('/message_thread.php', [LegacyMobileApiController::class, 'sendMessage']);