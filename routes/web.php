<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tenant;
use App\Models\RentBill;
use App\Models\OfficeExpenseGroup;
use Illuminate\Http\Request;
use Carbon\Carbon;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tenant/{tenant}/print', function (Tenant $tenant) {
    return view('tenants.print', compact('tenant'));
})->name('tenant.print');

Route::get('/rentbill/{rentbill}/print', function (RentBill $rentbill) {
    return view('rentbills.print', compact('rentbill'));
})->name('rentbill.print');

Route::get('/reports/monthly-rent-bill/print', function (Request $request) {
    $billMonth = $request->query('bill_month');
    $month = $billMonth ? Carbon::parse($billMonth)->startOfMonth() : now()->startOfMonth();

    $rows = RentBill::query()
        ->whereYear('bill_month', $month->year)
        ->whereMonth('bill_month', $month->month)
        ->orderBy('client_name')
        ->get();

    $totals = [
        'rent' => round($rows->sum('rent'), 2),
        'parking_total' => round($rows->sum('parking_total'), 2),
        'total' => round($rows->sum('total'), 2),
        'income_tax' => round($rows->sum('income_tax'), 2),
        'rent_advance' => round($rows->sum('rent_advance'), 2),
        'grand_total' => round($rows->sum('grand_total'), 2),
    ];

    return view('reports.monthly-rent-bill-print', [
        'month' => $month,
        'rows' => $rows,
        'totals' => $totals,
    ]);
})->name('reports.monthly-rent-bill.print');

Route::get('/reports/yearly-tenant-rent-bill/print', function (Request $request) {
    $tenantId = $request->query('tenant_id');
    $year = (int) ($request->query('year') ?? now()->year);

    $tenant = $tenantId ? Tenant::find($tenantId) : null;
    $rows = RentBill::query()
        ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
        ->whereYear('bill_month', $year)
        ->orderBy('bill_month')
        ->get();

    $totals = [
        'rent' => round($rows->sum('rent'), 2),
        'parking_total' => round($rows->sum('parking_total'), 2),
        'total' => round($rows->sum('total'), 2),
        'income_tax' => round($rows->sum('income_tax'), 2),
        'rent_advance' => round($rows->sum('rent_advance'), 2),
        'grand_total' => round($rows->sum('grand_total'), 2),
    ];

    return view('reports.yearly-tenant-rent-bill-print', [
        'year' => $year,
        'tenant' => $tenant,
        'rows' => $rows,
        'totals' => $totals,
    ]);
})->name('reports.yearly-tenant-rent-bill.print');

Route::get('/reports/office-expense/print', function (Request $request) {
    $billNo = $request->query('bill_no');
    $group = OfficeExpenseGroup::with('items.head')
        ->where('bill_no', $billNo)
        ->firstOrFail();

    return view('reports.office-expense-print', [
        'group' => $group,
    ]);
})->name('reports.office-expense.print');
