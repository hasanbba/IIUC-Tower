<?php

namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewRentBill extends ViewRecord
{
    protected static string $resource = RentBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
			Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('rentbill.print', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
