<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            Section::make()
            ->schema([
                TextInput::make('client_name')
                    ->placeholder("Enter Full Name Here")
                    ->required(),
                TextInput::make('client_id')
                    ->placeholder("Enter Uniq ID Here")
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('floor')
                    ->placeholder("Enter Floor Name Here"),
                Textarea::make('contact_address'),
                TextInput::make('agreement_year')
                    ->numeric()
                    ->suffix('Total Year')
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) =>
                        $get('rent_start_date') && $get('agreement_year')
                            ? $set('expired_date',
                                \Carbon\Carbon::parse($get('rent_start_date'))
                                    ->addYears((int)$get('agreement_year'))
                                    ->format('Y-m-d')
                            )
                            : null
                    ),
                TextInput::make('rent_increase')
                    ->label('Rent Increase %')
                    ->numeric()
                    ->suffix('%')
                    ->default(0),
                DatePicker::make('rent_start_date')
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) =>
                        $get('rent_start_date') && $get('agreement_year')
                            ? $set('expired_date',
                                \Carbon\Carbon::parse($get('rent_start_date'))
                                    ->addYears((int)$get('agreement_year'))
                                    ->format('Y-m-d')
                            )
                            : null
                    ),

                DatePicker::make('expired_date')
                    ->disabled()
                    ->suffixIcon('heroicon-o-calendar')
                    ->dehydrated(true),

                TextInput::make('rent_advance')
                    ->suffix('BDT')
                    ->numeric(),

                    // -----------------------
                    // RENT ITEMS
                    // -----------------------

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
                                    ->placeholder('Enter Square Feet Here')
                                    ->numeric()
                                    ->lazy()
                                    ->afterStateUpdated(fn (Get $get, Set $set) =>
                                        $set('line_total', ((float) ($get('sft') ?? 0)) * ((float) ($get('rate') ?? 0)))
                                    ),

                                TextInput::make('rate')
                                     ->label('Rate Per SFT')
                                    ->numeric()
                                    ->lazy()
                                    ->suffix('BDT')
                                    ->afterStateUpdated(fn (Get $get, Set $set) =>
                                        $set('line_total', ((float) ($get('sft') ?? 0)) * ((float) ($get('rate') ?? 0)))
                                    ),

                                TextInput::make('line_total')
                                    ->label('Total')    
                                    ->disabled()
                                    ->suffix('BDT')
                                    ->dehydrated(false),

                        ])
                        ->columnSpanFull()
                        ->columns(3)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $items = $get('rent_items') ?? [];

                            $total = collect($items)->sum(fn ($i) =>
                                ((float)($i['sft'] ?? 0)) * ((float)($i['rate'] ?? 0))
                            );

                            $set('total_rent', $total);
                        })
                        ->addActionLabel('Add More SFT'),

                TextInput::make('total_rent')
                    ->numeric()
                    ->disabled()
                    ->suffix('BDT')
                    ->dehydrated()
                    ->default(0),

                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                    ])->default('active'),
        
        ])->columnSpanFull()
        ->columns(3),
        
        ]);
    }
}
