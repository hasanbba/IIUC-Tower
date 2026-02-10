<?php

namespace App\Filament\Resources\OfficeExpenseHeads\Pages;

use App\Filament\Resources\OfficeExpenseHeads\OfficeExpenseHeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficeExpenseHeads extends ListRecords
{
    protected static string $resource = OfficeExpenseHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
