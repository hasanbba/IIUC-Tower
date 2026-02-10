<?php

namespace App\Filament\Resources\OfficeExpenses\Schemas;

use App\Models\OfficeExpenseHead;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OfficeExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('expense_date')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('bill_no')
                    ->label('Bill No')
                    ->disabled()
                    ->dehydrated(),
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('head_id')
                            ->label('Expense Head')
                            ->options(OfficeExpenseHead::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->suffix('BDT'),
                        Textarea::make('remark')
                            ->label('Remark')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add Expense Item')
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $items = $get('items') ?? [];
                        $total = collect($items)->sum(fn ($i) => (float) ($i['amount'] ?? 0));
                        $set('total_amount', round($total, 2));
                    }),
                TextInput::make('total_amount')
                    ->label('Total')
                    ->suffix('BDT')
                    ->disabled()
                    ->dehydrated(),
            ])
            ->columns(3);
    }
}
