<?php 
namespace App\Filament\Pages; 

use App\Models\RentBill; 
use App\Models\Tenant; 
use BackedEnum; 
use Carbon\Carbon; 
use Filament\Forms\Components\DatePicker; 
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Components\Select; 
use Filament\Forms\Components\TextInput; 
use Filament\Forms\Concerns\InteractsWithForms; 
use Filament\Forms\Contracts\HasForms; 
use Filament\Notifications\Notification; 
use Filament\Pages\Page; 
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema; 
use Filament\Support\Icons\Heroicon; 

    class RentBillGenerator extends Page implements HasForms 
    { 
        use InteractsWithForms; 
        protected string $view = 'filament.pages.rent-bill-generator'; 
        protected static ?string $navigationLabel = 'Rent Bills Generated'; 
        protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack; 
        protected static ?int $navigationSort = 5; 
        protected static bool $shouldRegisterNavigation = false; 
        public array $data = []; public bool $tenantSelected = false; 
        
        public function mount(): void 
        { 
            $this->form->fill(); 
        
        } 
        
        public function form(Schema $schema): Schema 
        { 
            return $schema ->statePath('data') ->schema([ 
                DatePicker::make('bill_month')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $tenantId = $get('tenant_id');
                        if (blank($state) || blank($tenantId)) {
                            return;
                        }

                        $month = Carbon::parse($state)->startOfMonth();
                        $exists = RentBill::where('tenant_id', $tenantId)
                            ->whereMonth('bill_month', $month->month)
                            ->whereYear('bill_month', $month->year)
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Bill already exists for this tenant and month.')
                                ->danger()
                                ->send();
                        }
                    }),
                Select::make('tenant_id')
                    ->options(function (Get $get) {
                        $billMonth = $get('bill_month');
                        if (blank($billMonth)) {
                            return [];
                        }

                        $month = Carbon::parse($billMonth)->startOfMonth();
                        

                        return Tenant::query()
                            ->whereDate('rent_start_date', '<=', $month)
                            ->whereDate('expired_date', '>=', $month)
                            ->pluck('client_name', 'id');
                    })
                    ->searchable() ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) 
                    { 
                        $billMonth = $get('bill_month');
                        if (blank($billMonth) || blank($state)) {
                            $this->tenantSelected = filled($state);
                            return;
                        }

                        $month = Carbon::parse($billMonth)->startOfMonth();
                        $exists = RentBill::where('tenant_id', $state)
                            ->whereMonth('bill_month', $month->month)
                            ->whereYear('bill_month', $month->year)
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Bill already exists for this tenant and month.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $tenant = Tenant::find($state); 
                        $this->tenantSelected = filled($state);
                         if ($tenant) 
                            { 
                                $set('floor', $tenant->floor);
                                $items = collect($tenant->rent_items ?? []);
                                $set('rent_items', array_values($items->toArray())); 
                                $base = $items->sum('total'); 
                                $set('rent', $tenant->current_rent ?? $base); 
                                $set('parking_qty', 0); $set('parking_rate', 0); 
                                $set('others_cost', 0); $set('tax_percent', 0); 
                                $set('vat_percent', 0); $months = max(1, ((int) $tenant->agreement_year) * 12); 
                                $monthlyAdvance = $tenant->rent_advance / $months; $set('rentrent_advance', round($monthlyAdvance, 2)); 
                                $this->recalculate($set, $get); 
                           } 
                    }), 
                           
                TextInput::make('floor') 
                    ->readOnly()
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                Repeater::make('rent_items') 
                    ->label('SFT & Rate')
                    ->itemLabel(function ($state, $key) {
                                static $counter = 0;
                                $counter++;
                                return 'SFT ' . $counter;
                            })
                
                    ->schema([ 
                        TextInput::make('sft')
                            ->label('Square Feet')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) =>
                                // $set('total', ((float) ($get('sft') ?? 0)) * ((float) ($get('rate') ?? 0)))
                                $set('line_total', ($get('sft') ?? 0) * ($get('rate') ?? 0))
                            ),
                        TextInput::make('rate')
                            ->label('Rate Per SFT')
                            ->live(onBlur: true)
                            ->suffix('BDT') 
                            ->afterStateUpdated(fn (Get $get, Set $set) =>
                                // $set('line_total', ((float) ($get('sft') ?? 0)) * ((float) ($get('rate') ?? 0)))
                                $set('line_total', ($get('sft') ?? 0) * ($state ?? 0))
                            ),

                        TextInput::make('line_total')
                            ->label('Line Total')
                            ->readOnly(),
                    ])
                    ->addable(false)->deletable(false)->reorderable(false)->columns(3)->columnSpanFull()->hidden(fn () => ! $this->tenantSelected),
                
                TextInput::make('rent') 
                    ->suffix('BDT')
                    ->live(onBlur: true) 
                    ->afterStateUpdated(fn ($s, $set, $get) => $this->recalculate($set, $get)) 
                    ->hidden(fn () => ! $this->tenantSelected), 
                
                TextInput::make('parking_qty') 
                    ->label('Parking Quantity')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true) 
                    ->afterStateUpdated(fn ($s, $set, $get) => $this->recalculate($set, $get)) 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('parking_rate') 
                    ->numeric()
                    ->default(0)
                    ->suffix('BDT')
                    ->live(onBlur: true) 
                    ->afterStateUpdated(fn ($s, $set, $get) => $this->recalculate($set, $get)) 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('parking_total') 
                    ->suffix('BDT')
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('others_cost') 
                    ->numeric()
                    ->default(0)
                    ->suffix('BDT')
                    ->live(onBlur: true) 
                    ->afterStateUpdated(fn ($s, $set, $get) => $this->recalculate($set, $get)) 
                    ->hidden(fn () => ! $this->tenantSelected), 
                
                TextInput::make('total') 
                    ->suffix('BDT') 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('tax_percent') 
                    ->label('Tax %')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true) 
                    ->afterStateUpdated(fn ($s, $set, $get) => $this->recalculate($set, $get)) 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('income_tax') 
                    ->suffix('BDT')
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('balance') 
                    ->suffix('BDT') 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('rentrent_advance') 
                    ->suffix('BDT') 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('amount_to_be_paid') 
                    ->suffix('BDT')
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('vat_percent') 
                    ->label('Vat %')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true) 
                    ->afterStateUpdated(fn ($s, $set, $get) => $this->recalculate($set, $get)) 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
                TextInput::make('vat_total') 
                    ->suffix('BDT')
                    ->hidden(fn () => ! $this->tenantSelected), 

                TextInput::make('grand_total') 
                    ->suffix('BDT') 
                    ->hidden(fn () => ! $this->tenantSelected), 
                    
            ])->columns(3); 
        } 
            
        protected function recalculate(callable $set, callable $get): void 
        { 
            $rent = (float) ($get('rent') ?? 0); 
            $parkingQty = (float) ($get('parking_qty') ?? 0); 
            $parkingRate = (float) ($get('parking_rate') ?? 0); 
            $others = (float) ($get('others_cost') ?? 0); 
            $taxPercent = (float) ($get('tax_percent') ?? 0); 
            $vatPercent = (float) ($get('vat_percent') ?? 0); 
            $advance = (float) ($get('rentrent_advance') ?? 0); 
            
            //Parking 
            $parkingTotal = $parkingQty * $parkingRate; 
            $set('parking_total', round($parkingTotal, 2)); 
            
            // Total 
            $total = $rent + $parkingTotal + $others; 
            $set('total', round($total, 2)); 
            // Tax 
            $incomeTax = ($total * $taxPercent) / 100; 
            $set('income_tax', round($incomeTax, 2)); 
            // Balance 
            $balance = $total - $incomeTax; 
            $set('balance', round($balance, 2)); 
            // Amount after advance 
            $amountToPay = $balance - $advance; 
            $amountToPay = max(0, $amountToPay); 
            $set('amount_to_be_paid', round($amountToPay, 2)); 
            
            // VAT ✅ SAFE 
            $vatTotal = ($amountToPay * $vatPercent) / 100; 
            $set('vat_total', round($vatTotal, 2)); 
            
            // Grand total 
            $grandTotal = $amountToPay + $vatTotal; 
            $set('grand_total', round($grandTotal, 2)); 
        }
            
        // ========================= 
        // GENERATE 
        // ========================= 
        
        public function generate(bool $single = false) 
        { 
            
            $this->validate([ 'data.bill_month' => ['required', 'date'], ]);
             // always use form state 
                $data = $this->form->getState(); 

            if ($single && empty($data['tenant_id'] ?? null)) { $this->addError('data.tenant_id', 'Please select a tenant.'); return; }
 
            $month = Carbon::parse($data['bill_month'])->startOfMonth(); 
            if ($single) {
                $exists = RentBill::where('tenant_id', $data['tenant_id'])
                    ->whereMonth('bill_month', $month->month)
                    ->whereYear('bill_month', $month->year)
                    ->exists();

                if ($exists) {
                    Notification::make()
                        ->title('Bill already exists for this tenant and month.')
                        ->danger()
                        ->send();
                    return;
                }
            }
            $tenants = $single 
            ? Tenant::whereKey($data['tenant_id'])->get() 
            : Tenant::query()->get(); 
            
            $created = 0; 
            $skipped = 0; 

            foreach ($tenants as $tenant) 
            { 
                if ($month->lt($tenant->rent_start_date) || $month->gt($tenant->expired_date)) 
                { 
                    continue; 
                } 
                    // prevent duplicate per month 
                    $exists = RentBill::where('tenant_id', $tenant->id) 
                        ->whereMonth('bill_month', $month->month) 
                        ->whereYear('bill_month', $month->year) 
                        ->exists(); 
                        if ($exists) 
                        { 
                            $skipped++; continue; 
                        } 
                
                // ---------------- SINGLE vs ALL ---------------- 

                if ($single) { 
                    $rent = $data['rent'] ?? 0; 
                    $parkingQty = $data['parking_qty'] ?? 0; 
                    $parkingRate = $data['parking_rate'] ?? 0; 
                    $parkingTotal = $data['parking_total'] ?? 0; 
                    $othersCost = $data['others_cost'] ?? 0; 
                    $total = $data['total'] ?? 0; 
                    $taxPercent = $data['tax_percent'] ?? 0; 
                    $incomeTax = $data['income_tax'] ?? 0; 
                    $balance = $data['balance'] ?? 0; 
                    $advance = $data['rentrent_advance'] ?? 0; 
                    $amountToPay = $data['amount_to_be_paid'] ?? 0; 
                    $vatPercent = $data['vat_percent'] ?? 0; 
                    $vatTotal = $data['vat_total'] ?? 0; 
                    $grandTotal = $data['grand_total'] ?? 0; } 
                    
                // ---------------- BULK ---------------- 
                
                else { 
                    $rent = $tenant->current_rent; 
                    $parkingQty = 0; 
                    $parkingRate = 0; 
                    $parkingTotal = 0; 
                    $othersCost = 0; 
                    $total = 0; 
                    $taxPercent = 0; 
                    $incomeTax = 0; 
                    $balance = 0; 
                    $advance = $data['rentrent_advance'] ?? 0; 
                    $amountToPay = 0; 
                    $vatPercent = 0; 
                    $vatTotal = 0; 
                    $grandTotal = 0; 
                } 
                
                RentBill::create([ 'invoice_id' => 'TRB' . str_pad((RentBill::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT), 
                    'tenant_id' => $tenant->id, 
                    'bill_month' => $month, 
                    'client_name' => $tenant->client_name, 
                    'rent_items' => $single ? ($data['rent_items'] ?? []) : ($tenant->rent_items ?? []),
                    'rent' => $rent, 
                    'parking_qty' => $parkingQty, 
                    'parking_rate' => $parkingRate, 
                    'parking_total' => $parkingTotal, 
                    'others_cost' => $othersCost, 
                    'total' => $total, 
                    'tax_percent' => $taxPercent, 
                    'income_tax' => $incomeTax, 
                    'balance' => $balance, 
                    'rent_advance' => $advance, 
                    'amount_to_pay' => $amountToPay, 
                    'vat_percent' => $vatPercent, 
                    'vat_total' => $vatTotal, 
                    'grand_total' => $grandTotal, 
                    'status' => $single ? 'ready' : 'notready', 
                ]); 
                
                $created++; 
            } 
            
            if ($created === 0) 
            { 
                Notification::make() 
                    ->title('Bills already exist for this month.') 
                    ->danger() 
                    ->send(); 
                return; 
            } 
                Notification::make() 
                ->title("Bills generated successfully. Created: {$created}, Skipped: {$skipped}") 
                ->success() 
                ->send(); 
                
        }
    }
