<?php

namespace App\Filament\Resources\OfficeExpenses\Pages;

use App\Filament\Resources\OfficeExpenses\OfficeExpenseResource;
use Filament\Resources\Pages\EditRecord;

class EditOfficeExpense extends EditRecord
{
    protected static string $resource = OfficeExpenseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total_amount'] = $this->calculateTotal($data);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->update([
            'total_amount' => $this->record->items()->sum('amount'),
        ]);
    }

    private function calculateTotal(array $data): float
    {
        $items = $data['items'] ?? [];
        $total = collect($items)->sum(fn ($i) => (float) ($i['amount'] ?? 0));
        return round($total, 2);
    }
}
