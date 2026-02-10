<?php

namespace App\Filament\Resources\OfficeExpenseHeads\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfficeExpenseHeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(2);
    }
}
