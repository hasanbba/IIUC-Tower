<?php

namespace App\Filament\Resources\OfficeExpenses\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class OfficeExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bill_no')->label('Bill No')->sortable()->searchable(),
                TextColumn::make('expense_date')->date()->sortable(),
                TextColumn::make('items_count')->label('Items')->counts('items'),
                TextColumn::make('total_amount')->money('BDT')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('expense_date', 'desc');
    }
}
