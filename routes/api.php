<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CentralController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register', [AuthController::class, 'registerCustomer']);
Route::post('login', [AuthController::class, 'loginCustomer']);
Route::get('login', fn() => response()->json(['message' => 'Login is not allowed for public routes']));
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

Route::get('unauth_bootstrap', [AuthController::class, 'unme']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('bootstrap', [AuthController::class, 'me']);
   
    // API resources for authenticated users
    Route::apiResource('users', UserController::class);
    Route::post('/upload_documents', [UserController::class, 'uploadDocuments']);
    Route::put('/users/{id}/notifications', [UserController::class, 'updateNotificationPreferences']);
    Route::put('/users/{id}/account-details', [UserController::class, 'updateAccountDetails']);

    Route::apiResource('customers', CustomerController::class);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);

    // Exchange Rates
    Route::apiResource('exchange-rates', ExchangeRateController::class);
    
    
});
Route::get('/verify_email/{token?}/{email?}', [CentralController::class, 'verifyEmail']); 
Route::get('/confirm-change-email/{token}', [CentralController::class, 'confirmChangeEmail']); 

Route::post('/resend_email_verification', [CentralController::class, 'resendEmailVerification']);



Route::post('/reset_password', [CentralController::class, 'forgotPassword']);
Route::post('/confirm_reset_password', [CentralController::class, 'confirmForgotPassword']);

Route::post('/guide', [CentralController::class, 'guideUpdate']);
Route::get('/payment/callback/{gateway}', [TransactionController::class, 'callback']);


//Route::post('/cowrispay_webhook', [CentralController::class, 'cowrispayListener']);

// Uncomment this if MfaMethod routes are needed

Route::get('sms', [CentralController::class, 'testSms']);
//Route::apiResource('mfa-methods', MfaMethodController::class);
