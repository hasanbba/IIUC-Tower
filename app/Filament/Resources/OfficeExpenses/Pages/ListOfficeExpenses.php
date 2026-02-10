<?php

namespace App\Filament\Resources\OfficeExpenses\Pages;

use App\Filament\Resources\OfficeExpenses\OfficeExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficeExpenses extends ListRecords
{
    protected static string $resource = OfficeExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
