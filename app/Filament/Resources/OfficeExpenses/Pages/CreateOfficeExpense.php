<?php

namespace App\Filament\Resources\OfficeExpenses\Pages;

use App\Filament\Resources\OfficeExpenses\OfficeExpenseResource;
use App\Models\OfficeExpenseGroup;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficeExpense extends CreateRecord
{
    protected static string $resource = OfficeExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_amount'] = $this->calculateTotal($data);

        if (empty($data['bill_no'])) {
            $max = (int) (OfficeExpenseGroup::max('bill_no') ?? 100);
            $data['bill_no'] = $max + 1;
        }

        return $data;
    }

    protected function afterCreate(): void
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
