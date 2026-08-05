<?php

use App\Http\Controllers\Api\V1\BrandingController;
use Illuminate\Support\Facades\Route;

Route::get('branding', [BrandingController::class, 'show'])->name('branding.show');