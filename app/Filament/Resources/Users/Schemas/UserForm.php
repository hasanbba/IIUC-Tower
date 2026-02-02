<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->visible(fn ($livewire) => $livewire instanceof CreateUser)
                            ->required(fn ($livewire) => $livewire instanceof CreateUser)
                            ->confirmed(),

                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->visible(fn ($livewire) => $livewire instanceof CreateUser)
                            ->required(fn ($livewire) => $livewire instanceof CreateUser),
                        
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            //->multiple()
                            ->preload()
                            ->searchable(),
                        FileUpload::make('avatar')
                            ->image()
                            ->directory('admins')
                            ->avatar()
                            ->imageEditor(),
                        Radio::make('is_active')
                            ->label('User Status')
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ])
                            ->default(1)
                            ->required()
                            ->inline(),
                    ])->columnSpanFull()->collapsible()->columns(4),

                // -------------------
                // CHANGE PASSWORD
                // -------------------

                Section::make('Change Password')
                    ->description('Enable to change user password')
                    ->visible(fn ($livewire) => $livewire instanceof EditUser)
                    ->schema([

                        Toggle::make('change_password')
                            ->label('Change Password')
                            ->live(),

                        TextInput::make('old_password')
                            ->password()
                            ->revealable()
                            ->visible(fn ($get) => $get('change_password'))
                            ->required(fn ($get) => $get('change_password'))
                            ->rule(function ($get, $record) {
                                return function ($attribute, $value, $fail) use ($record) {
                                    if ($record && ! Hash::check($value, $record->password)) {
                                        $fail('Old password does not match.');
                                    }
                                };
                            }),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->visible(fn ($get) => $get('change_password'))
                            ->required(fn ($get) => $get('change_password'))
                            ->confirmed(),

                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->visible(fn ($get) => $get('change_password'))
                            ->required(fn ($get) => $get('change_password')),

                    ])->columnSpanFull()->collapsed(),
            ]);
    }
}
