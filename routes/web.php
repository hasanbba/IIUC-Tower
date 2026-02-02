<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tenant/{tenant}/print', function (Tenant $tenant) {
    return view('tenants.print', compact('tenant'));
})->name('tenant.print');
