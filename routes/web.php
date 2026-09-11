<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorProfileController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WasteListingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProcessorController;
use App\Http\Controllers\AdminController;

// =============================================
// PUBLIC ROUTES
// =============================================

// Landing page
Route::get('/', function () {
    return view('landing');
})->name('home');

// Waitlist route
Route::post('/waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');

// Role-based registration
Route::get('/register/{role?}', function ($role = null) {
    return view('auth.register', compact('role'));
})->name('register.with.role');

// =============================================
// AUTHENTICATED ROUTES (Breeze default)
// =============================================

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =============================================
// VENDOR ROUTES
// =============================================

Route::middleware(['auth'])->group(function () {
    // Vendor setup
    Route::get('/vendor/setup', [VendorController::class, 'setup'])->name('vendor.setup');
    Route::post('/vendor/setup', [VendorController::class, 'storeSetup'])->name('vendor.setup.store');

    // Vendor dashboard
    Route::get('/vendor/dashboard', [VendorController::class, 'dashboard'])->name('vendor.dashboard');

    // Waste listings
    Route::post('/vendor/listings/store', [WasteListingController::class, 'store'])->name('vendor.listings.store');
    Route::post('/vendor/listings/{id}/cancel', [WasteListingController::class, 'cancel'])->name('vendor.listings.cancel');
});

Route::middleware(['auth'])->get('/processor/dashboard', function () {
    return view('dashboard');
})->name('processor.dashboard');

Route::middleware(['auth'])->put('/vendor/profile/update', [VendorProfileController::class, 'update'])->name('vendor.profile.update');
Route::middleware(['auth'])->get('/vendor/my-listings', [VendorController::class, 'myListings'])->name('vendor.my-listings');
Route::middleware(['auth'])->get('/vendor/listings/{id}', [WasteListingController::class, 'show'])->name('vendor.listings.show');
Route::middleware(['auth'])->put('/vendor/listings/{id}/update', [WasteListingController::class, 'update'])->name('vendor.listings.update');
Route::middleware(['auth'])->get('/vendor/matches', [VendorController::class, 'matches'])->name('vendor.matches');
Route::middleware(['auth'])->get('/vendor/matches/{id}', [VendorController::class, 'getMatchDetails'])->name('vendor.matches.details');
Route::middleware(['auth'])->post('/vendor/matches/{id}/rate', [VendorController::class, 'rateMatch'])->name('vendor.matches.rate');
Route::middleware(['auth'])->get('/vendor/analytics', [VendorController::class, 'analytics'])->name('vendor.analytics');
Route::middleware(['auth'])->get('/vendor/analytics/data', [VendorController::class, 'getAnalyticsData'])->name('vendor.analytics.data');
Route::middleware(['auth'])->get('/vendor/analytics/download', [VendorController::class, 'downloadAnalyticsReport'])->name('vendor.analytics.download');
Route::middleware(['auth', 'role:vendor'])->post('/vendor/matches/{id}/confirm', [VendorController::class, 'confirmClaim'])->name('vendor.matches.confirm');
Route::middleware(['auth', 'role:vendor'])->post('/vendor/matches/{id}/reject', [VendorController::class, 'rejectClaim'])->name('vendor.matches.reject');
Route::middleware(['auth', 'role:vendor'])->post('/vendor/matches/{id}/rate', [VendorController::class, 'rateMatch'])->name('vendor.matches.rate');

// Include Breeze auth routes (login, register, password reset, etc.)


// =============================================
// PROCESSOR ROUTES
// =============================================
// Processor Routes
Route::middleware(['auth', 'role:processor'])->group(function () {
    Route::get('/processor/dashboard', [ProcessorController::class, 'dashboard'])->name('processor.dashboard');
    Route::get('/processor/find-waste', [ProcessorController::class, 'findWaste'])->name('processor.find-waste');
    Route::get('/processor/my-claims', [ProcessorController::class, 'myClaims'])->name('processor.my-claims');
    Route::get('/processor/history', [ProcessorController::class, 'history'])->name('processor.history');
    Route::get('/processor/listings/{id}', [ProcessorController::class, 'getListingDetails'])->name('processor.listings.details');
    Route::post('/processor/listings/{id}/claim', [ProcessorController::class, 'claimWaste'])->name('processor.listings.claim');
    Route::put('/processor/matches/{id}/status', [ProcessorController::class, 'updateMatchStatus'])->name('processor.matches.status');
    Route::put('/processor/profile/update', [ProcessorController::class, 'updateProfile'])->name('processor.profile.update');
    Route::middleware(['auth', 'role:processor'])->get('/processor/listings/api', [ProcessorController::class, 'getListingsApi'])->name('processor.listings.api');
    Route::middleware(['auth', 'role:processor'])->get('/processor/history/download', [ProcessorController::class, 'downloadHistory'])->name('processor.history.download');
});

Route::middleware(['auth'])->post('/vendor/matches/{id}/confirm', [VendorController::class, 'confirmClaim'])->name('vendor.matches.confirm');
Route::middleware(['auth'])->post('/vendor/matches/{id}/reject', [VendorController::class, 'rejectClaim'])->name('vendor.matches.reject');

// =============================================
// ADMIN ROUTES
// =============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'getUserDetails'])->name('users.details');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser'])->name('users.verify');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Listings Management
    Route::get('/listings', [AdminController::class, 'listings'])->name('listings');
    Route::get('/listings/{id}', [AdminController::class, 'getListingDetails'])->name('listings.details');
    Route::delete('/listings/{id}', [AdminController::class, 'deleteListing'])->name('listings.delete');
    Route::get('/listings/export/csv', [AdminController::class, 'exportListings'])->name('listings.export');

    // Matches Management
    Route::get('/matches', [AdminController::class, 'matches'])->name('matches');
    Route::get('/matches/{id}', [AdminController::class, 'getMatchDetails'])->name('matches.details');
    Route::put('/matches/{id}/status', [AdminController::class, 'updateMatchStatus'])->name('matches.status');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AdminController::class, 'exportReport'])->name('reports.export');

    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/categories/{id}/toggle', [AdminController::class, 'toggleCategory'])->name('categories.toggle');
    Route::post('/waste-types', [AdminController::class, 'addWasteType'])->name('waste-types.store');
});


require __DIR__ . '/auth.php';