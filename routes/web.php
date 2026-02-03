<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use App\Models\RentBill;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tenant/{tenant}/print', function (Tenant $tenant) {
    return view('tenants.print', compact('tenant'));
})->name('tenant.print');

Route::get('/rentbill/{rentbill}/print', function (RentBill $rentbill) {
    return view('rentbills.print', compact('rentbill'));
})->name('rentbill.print');