<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\ToolBasicAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(ToolBasicAuth::class)->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/preview', [CampaignController::class, 'preview'])->name('campaigns.preview');
    Route::post('/campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
});
