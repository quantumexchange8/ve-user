<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RedemptionController;

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/redemption_code_request', [RedemptionController::class, 'index'])->middleware(['auth', 'verified'])->name('redemption_code_request');

Route::middleware('auth')->group(function () {
    Route::prefix('redemption')->group(function () {
        Route::get('/getRedemptionCodeRequest', [RedemptionController::class, 'getRedemptionCodeRequest'])->name('redemption.getRedemptionCodeRequest');
        Route::get('/getRedemptionCodes', [RedemptionController::class, 'getRedemptionCodes'])->name('redemption.getRedemptionCodes');
        
        Route::post('/requestRedemptionCode', [RedemptionController::class, 'requestRedemptionCode'])->name('redemption.requestRedemptionCode');

        Route::get('/redemptionCodeListing', [RedemptionController::class, 'redemptionCodeListing'])->name('redemption.redemptionCodeListing');
        Route::get('/getRedemptionCodeListing', [RedemptionController::class, 'getRedemptionCodeListing'])->name('redemption.getRedemptionCodeListing');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
