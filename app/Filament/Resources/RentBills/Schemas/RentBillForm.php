<?php

namespace App\Filament\Resources\RentBills\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentBillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                ->schema([
                    TextInput::make('invoice_id')
                        ->placeholder("Enter Full Name Here")
                        ->required(),
                    TextInput::make('client_name')
                        ->placeholder("Enter Full Name Here")
                        ->required(),

                    Repeater::make('rent_items')
                        ->schema([
                            TextInput::make('sft')
                                ->numeric()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, $set, $get) =>
                                    $set('line_total', ($get('sft') ?? 0) * ($get('rate') ?? 0))
                                ),

                            TextInput::make('rate')
                                ->numeric()
                                ->suffix('BDT')
                                ->reactive()
                                ->afterStateUpdated(fn ($state, $set, $get) =>
                                    $set('line_total', ($get('sft') ?? 0) * ($get('rate') ?? 0))
                                ),

                            TextInput::make('line_total')
                                ->disabled()
                                ->dehydrated(),
                        ])
                        ->columns(3)
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            $sum = collect($state)->sum('line_total');
                            $set('rent', $sum);
                        }),
                    
                    TextInput::make('rent')
                    ->numeric()
                    ->suffix('BDT')




                ])->columnSpanFull()->columns(3)

                
            ]);
    }
}
