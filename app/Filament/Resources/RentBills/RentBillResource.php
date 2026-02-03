<?php

namespace App\Filament\Resources\RentBills;

use App\Filament\Resources\RentBills\Pages\CreateRentBill;
use App\Filament\Resources\RentBills\Pages\EditRentBill;
use App\Filament\Resources\RentBills\Pages\ListRentBills;
use App\Filament\Resources\RentBills\Pages\ViewRentBill;
use App\Filament\Resources\RentBills\Schemas\RentBillForm;
use App\Filament\Resources\RentBills\Tables\RentBillsTable;
use App\Models\RentBill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RentBillResource extends Resource
{
    protected static ?string $model = RentBill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'RentBill';

    public static function form(Schema $schema): Schema
    {
        return RentBillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentBillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentBills::route('/'),
            'create' => CreateRentBill::route('/create'),
            'view' => ViewRentBill::route('/{record}'),
            'edit' => EditRentBill::route('/{record}/edit'),
        ];
    }
}
