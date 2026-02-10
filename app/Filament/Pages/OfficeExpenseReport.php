<?php

namespace App\Filament\Pages;

use App\Models\OfficeExpenseGroup;
use App\Models\OfficeExpenseHead;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OfficeExpenseReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.office-expense-report';

    protected static ?string $navigationLabel = 'Expense Report';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;
    protected static string|UnitEnum|null $navigationGroup = 'Office Expenses';
    protected static ?int $navigationSort = 30;

    public ?int $bill_no = null;
    public ?OfficeExpenseGroup $group = null;
    public array $groupedItems = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('bill_no')
                    ->label('Bill No')
                    ->options(fn () =>
                        OfficeExpenseGroup::orderByDesc('bill_no')
                            ->pluck('bill_no', 'bill_no')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->loadReportByBillNo($state)),
            ]);
    }

    public function loadReport(): void
    {
        $this->loadReportByBillNo($this->bill_no);
    }

    public function updatedBillNo($value): void
    {
        $this->loadReportByBillNo($value);
    }

    public function updated($name, $value): void
    {
        if ($name === 'bill_no') {
            $this->loadReportByBillNo($value);
        }
    }

    public function loadReportByBillNo($billNo): void
    {
        if (blank($billNo)) {
            $this->group = null;
            $this->groupedItems = [];
            return;
        }

        $this->group = OfficeExpenseGroup::with('items.head')
            ->where('bill_no', $billNo)
            ->orWhere('id', $billNo)
            ->first();

        if (! $this->group) {
            $this->groupedItems = [];
            return;
        }

        $used = $this->group->items
            ->groupBy('head_id')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'head_id' => $first?->head?->id,
                    'head_code' => $first?->head?->code ?? '',
                    'head_name' => $first?->head?->name ?? '',
                    'total' => $items->sum('amount'),
                    'remark' => $items->pluck('remark')->filter()->implode(', '),
                ];
            });

        $this->groupedItems = OfficeExpenseHead::orderBy('code')
            ->get()
            ->map(function ($head) use ($used) {
                $row = $used->get($head->id);
                return [
                    'head_id' => $head->id,
                    'head_code' => $head->code,
                    'head_name' => $head->name,
                    'total' => $row['total'] ?? 0,
                    'remark' => $row['remark'] ?? '',
                ];
            })
            ->toArray();
    }
}
