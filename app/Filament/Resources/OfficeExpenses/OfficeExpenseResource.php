<?php

namespace App\Filament\Resources\OfficeExpenses;

use App\Filament\Resources\OfficeExpenses\Pages\CreateOfficeExpense;
use App\Filament\Resources\OfficeExpenses\Pages\EditOfficeExpense;
use App\Filament\Resources\OfficeExpenses\Pages\ListOfficeExpenses;
use App\Filament\Resources\OfficeExpenses\Pages\ViewOfficeExpense;
use App\Filament\Resources\OfficeExpenses\Schemas\OfficeExpenseForm;
use App\Filament\Resources\OfficeExpenses\Tables\OfficeExpensesTable;
use App\Models\OfficeExpenseGroup;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfficeExpenseResource extends Resource
{
    protected static ?string $model = OfficeExpenseGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Office Expenses';
    protected static string|UnitEnum|null $navigationGroup = 'Office Expenses';

    public static function form(Schema $schema): Schema
    {
        return OfficeExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeExpensesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficeExpenses::route('/'),
            'create' => CreateOfficeExpense::route('/create'),
            'view' => ViewOfficeExpense::route('/{record}'),
            'edit' => EditOfficeExpense::route('/{record}/edit'),
        ];
    }
}
