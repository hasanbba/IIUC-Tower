<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\MonthlyRentBillReport;
use App\Filament\Pages\RentBillGenerator;
use App\Filament\Pages\YearlyTenantRentBillReport;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';
}
