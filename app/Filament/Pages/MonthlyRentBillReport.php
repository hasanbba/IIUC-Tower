<?php

namespace App\Filament\Pages;

use App\Models\RentBill;
use BackedEnum;
use UnitEnum; 
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MonthlyRentBillReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.monthly-rent-bill-report';
    protected static ?string $navigationLabel = 'Monthly Report';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static string | UnitEnum | null $navigationGroup = 'RentBill';
    protected static ?int $navigationSort = 20;
    protected static bool $shouldRegisterNavigation = false; 

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'bill_month' => now()->startOfMonth(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                DatePicker::make('bill_month')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $billMonth = $this->data['bill_month'] ?? null;
        if (blank($billMonth)) {
            return RentBill::query()->whereRaw('1 = 0');
        }

        $month = Carbon::parse($billMonth)->startOfMonth();
        return RentBill::query()
            ->whereYear('bill_month', $month->year)
            ->whereMonth('bill_month', $month->month)
            ->orderBy('client_name')
            ;
    }

    public function getPrintRowsProperty(): Collection
    {
        $billMonth = $this->data['bill_month'] ?? null;
        if (blank($billMonth)) {
            return collect();
        }

        $month = Carbon::parse($billMonth)->startOfMonth();

        return RentBill::query()
            ->whereYear('bill_month', $month->year)
            ->whereMonth('bill_month', $month->month)
            ->orderBy('client_name')
            ->get();
    }

    public function getPrintTotalsProperty(): array
    {
        $rows = $this->printRows;

        return [
            'rent' => round($rows->sum('rent'), 2),
            'parking_total' => round($rows->sum('parking_total'), 2),
            'total' => round($rows->sum('total'), 2),
            'income_tax' => round($rows->sum('income_tax'), 2),
            'rent_advance' => round($rows->sum('rent_advance'), 2),
            'grand_total' => round($rows->sum('grand_total'), 2),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('invoice_id')->label('Invoice')->searchable(),
                TextColumn::make('client_name')->label('Client')->searchable(),
                TextColumn::make('rent')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('parking_total')->label('Parking')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('others_cost')->label('Others')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('total')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('income_tax')->label('Tax')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('vat_total')->label('VAT')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('grand_total')->label('Grand Total')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('status')->label('Status'),
            ])
            ->defaultSort('client_name');
    }
}
