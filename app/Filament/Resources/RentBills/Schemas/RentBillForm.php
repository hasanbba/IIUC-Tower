<?php

namespace App\Filament\Resources\RentBills\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Tenant; 
use App\Models\RentBill; 

class RentBillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->schema([
                        TextInput::make('invoice_id')->required(),
                        TextInput::make('client_name')->required(),
                        DatePicker::make('bill_month')->required(),
                    ])->columns(3)->columnSpanFull(),

                Section::make('Rent Items')
                    ->schema([
                        Repeater::make('rent_items')
                            ->default(fn ($record) => $record?->rent_items ?? [])
                            ->schema([
                                TextInput::make('sft')
                                    ->label('SFT')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) =>
                                        $set('line_total', ($get('sft') ?? 0) * ($get('rate') ?? 0))
                                    ),

                                TextInput::make('rate')
                                    ->numeric()
                                    ->live(onBlur: true)
                                    ->suffix('BDT')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) =>
                                        $set('line_total', ($get('sft') ?? 0) * ($state ?? 0))
                                    ),

                                TextInput::make('line_total')
                                    ->label('Line Total')
                                    ->readOnly(),
                            ]) ->columns(3)->columnSpanFull(),
                            // ->reactive()
                            // ->defaultItems(1)
                            // ->afterStateUpdated(fn ($state, $set) => 
                            //     $set('rent', collect($state)->sum('line_total'))
                            // ),
                    ])->columnSpanFull(),
                    
                Section::make('Totals')
                    ->schema([
                        TextInput::make('rent')->readOnly()->suffix('BDT'),
                        
                        TextInput::make('parking_qty')
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($s, $set, $get) => self::recalc($set, $get)),
                        
                        TextInput::make('parking_rate')->numeric()
                            ->numeric()
                            ->suffix('BDT')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($s, $set, $get) => self::recalc($set, $get)),
                        
                        TextInput::make('parking_total')
                            ->readOnly()
                            ->suffix('BDT'),
                        
                        TextInput::make('others_cost')
                            ->suffix('BDT')
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($s, $set, $get) => self::recalc($set, $get)),
                        
                        TextInput::make('total')
                            ->readOnly() 
                            ->suffix('BDT'),
                        
                        TextInput::make('tax_percent')
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($s, $set, $get) => self::recalc($set, $get)),

                        TextInput::make('income_tax')
                            ->readOnly()
                            ->suffix('BDT'),
                        TextInput::make('balance')
                            ->readOnly()
                            ->suffix('BDT'),
                        TextInput::make('rent_advance')
                            ->label('Monthly Advance')
                            ->suffix('BDT')
                            ->readOnly()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if (! $record?->tenant) {
                                    return;
                                }

                                $tenant = $record->tenant;

                                $months = max(1, ((int) $tenant->agreement_year) * 12);
                                $monthlyAdvance = ($tenant->rent_advance ?? 0) / $months;

                                $set('rent_advance', round($monthlyAdvance, 2));
                            }),
                        TextInput::make('amount_to_pay')
                            ->readOnly()
                            ->suffix('BDT'),
                        TextInput::make('vat_percent')
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($s, $set, $get) => self::recalc($set, $get)),
                        TextInput::make('vat_total')
                            ->readOnly()
                            ->suffix('BDT'),
                        TextInput::make('grand_total')
                            ->readOnly()
                            ->suffix('BDT'),
                    ])->columns(3)->columnSpanFull(),
            ]);
    }

    protected static function recalc(callable $set, callable $get): void
    {
        $rent        = (float) ($get('rent') ?? 0);
        $parkingQty  = (float) ($get('parking_qty') ?? 0);
        $parkingRate = (float) ($get('parking_rate') ?? 0);
        $othersCost  = (float) ($get('others_cost') ?? 0);
        $taxPercent  = (float) ($get('tax_percent') ?? 0);
        $advance     = (float) ($get('rent_advance') ?? 0);
        $vatPercent  = (float) ($get('vat_percent') ?? 0);

        // Parking
        $parkingTotal = $parkingQty * $parkingRate;
        $set('parking_total', round($parkingTotal, 2));

        // Total
        $total = $rent + $parkingTotal + $othersCost;
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
        $set('amount_to_pay', round($amountToPay, 2));

        // VAT
        $vatTotal = ($amountToPay * $vatPercent) / 100;
        $set('vat_total', round($vatTotal, 2));

        // Grand total
        $grandTotal = $amountToPay + $vatTotal;
        $set('grand_total', round($grandTotal, 2));

    }
}
