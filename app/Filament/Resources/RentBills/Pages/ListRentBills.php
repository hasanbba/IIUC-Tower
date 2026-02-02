<?php

namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Pages\RentBillGenerator;
use App\Filament\Resources\RentBills\RentBillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentBills extends ListRecords
{
    protected static string $resource = RentBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
            CreateAction::make('create')
                ->label('Generate Bill') // Optional: change the button label
                ->url(RentBillGenerator::getUrl()), // Set the URL to your custom page
        ];
    }

}
