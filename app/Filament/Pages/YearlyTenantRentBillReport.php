<?php

namespace App\Filament\Pages;

use App\Models\RentBill;
use App\Models\Tenant;
use BackedEnum;
use UnitEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class YearlyTenantRentBillReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.pages.yearly-tenant-rent-bill-report';

    protected static ?string $navigationLabel = 'Yearly Report';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static string | UnitEnum | null $navigationGroup = 'RentBill';
    protected static ?int $navigationSort = 21;
    protected static bool $shouldRegisterNavigation = false; 

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'year' => now()->year,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Select::make('tenant_id')
                    ->label('Tenant')
                    ->options(Tenant::orderBy('client_name')->pluck('client_name', 'id'))
                    ->searchable()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn () => $this->resetTable()),
                Select::make('year')
                    ->options($this->getYearOptions())
                    ->searchable()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ])
            ->columns(2);
    }

    protected function getYearOptions(): array
    {
        $years = RentBill::query()
            ->selectRaw('YEAR(bill_month) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year', 'year')
            ->toArray();

        if (! empty($years)) {
            return $years;
        }

        $current = now()->year;
        $options = [];
        for ($y = $current; $y >= $current - 10; $y--) {
            $options[$y] = (string) $y;
        }

        return $options;
    }

    protected function getTableQuery(): Builder
    {
        $tenantId = $this->data['tenant_id'] ?? null;
        $year = $this->data['year'] ?? null;

        if (blank($tenantId) || blank($year)) {
            return RentBill::query()->whereRaw('1 = 0');
        }

        return RentBill::query()
            ->where('tenant_id', $tenantId)
            ->whereYear('bill_month', $year)
            ->orderBy('bill_month')
            ;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('bill_month')
                    ->label('Month')
                    ->date('M Y'),
                TextColumn::make('invoice_id')->label('Invoice')->searchable(),
                TextColumn::make('rent')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('parking_total')->label('Parking')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('others_cost')->label('Others')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('total')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('income_tax')->label('Tax')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('vat_total')->label('VAT')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('grand_total')->label('Grand Total')->money('BDT')->summarize(Sum::make()),
                TextColumn::make('status')->label('Status'),
            ])
            ->defaultSort('bill_month');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->action('exportCsv'),
        ];
    }

    public function exportCsv()
    {
        $tenantId = $this->data['tenant_id'] ?? null;
        $year = $this->data['year'] ?? null;

        if (blank($tenantId) || blank($year)) {
            Notification::make()
                ->title('Select tenant and year first.')
                ->danger()
                ->send();
            return null;
        }

        $rows = RentBill::query()
            ->where('tenant_id', $tenantId)
            ->whereYear('bill_month', $year)
            ->orderBy('bill_month')
            ->get();

        if ($rows->isEmpty()) {
            Notification::make()
                ->title('No bills found for this tenant and year.')
                ->warning()
                ->send();
            return null;
        }

        $tenantName = Tenant::whereKey($tenantId)->value('client_name') ?? 'tenant';
        $fileName = 'rent-bills-' . preg_replace('/\s+/', '-', strtolower($tenantName)) . '-' . $year . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Invoice',
                'Bill Month',
                'Client',
                'Rent',
                'Parking',
                'Others',
                'Total',
                'Tax',
                'VAT',
                'Grand Total',
                'Status',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->invoice_id,
                    Carbon::parse($row->bill_month)->format('Y-m'),
                    $row->client_name,
                    $row->rent,
                    $row->parking_total,
                    $row->others_cost,
                    $row->total,
                    $row->income_tax,
                    $row->vat_total,
                    $row->grand_total,
                    $row->status,
                ]);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}
