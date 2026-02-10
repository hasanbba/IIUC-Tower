<?php

namespace App\Filament\Resources\OfficeExpenseHeads;

use App\Filament\Resources\OfficeExpenseHeads\Pages\CreateOfficeExpenseHead;
use App\Filament\Resources\OfficeExpenseHeads\Pages\EditOfficeExpenseHead;
use App\Filament\Resources\OfficeExpenseHeads\Pages\ListOfficeExpenseHeads;
use App\Filament\Resources\OfficeExpenseHeads\Pages\ViewOfficeExpenseHead;
use App\Filament\Resources\OfficeExpenseHeads\Schemas\OfficeExpenseHeadForm;
use App\Filament\Resources\OfficeExpenseHeads\Tables\OfficeExpenseHeadsTable;
use App\Models\OfficeExpenseHead;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfficeExpenseHeadResource extends Resource
{
    protected static ?string $model = OfficeExpenseHead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Expense Heads';
    protected static string|UnitEnum|null $navigationGroup = 'Office Expenses';

    public static function form(Schema $schema): Schema
    {
        return OfficeExpenseHeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficeExpenseHeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficeExpenseHeads::route('/'),
            'create' => CreateOfficeExpenseHead::route('/create'),
            'view' => ViewOfficeExpenseHead::route('/{record}'),
            'edit' => EditOfficeExpenseHead::route('/{record}/edit'),
        ];
    }
}
