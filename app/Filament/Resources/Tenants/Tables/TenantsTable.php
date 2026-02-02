<?php

namespace App\Filament\Resources\Tenants\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;


class TenantsTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('client_name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('rent_start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('expired_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('agreement_year')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('floor')
                    ->label('Floor Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_rent')
                    ->label('Base Rent')
                    ->numeric()
                    ->sortable()
                    ->money('BDT')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_rent')
                    ->label('Current Rent')
                    ->money('BDT')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rent_advance')
                    ->numeric()
                    ->sortable()
                    ->money('BDT')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                        ->color(fn (string $state): string => match ($state) {
                            'active' => 'success',
                            'expired' => 'danger',
                            'inactive' => 'gray',
                            default => 'secondary',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                            'active' => 'Active',
                            'expired' => 'Expired',
                            'inactive' => 'Inactive',
                        ]),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->recordActions([
                Action::make('renew')
                    ->label('Renew')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(function ($record) {
                        if ($record->status !== 'expired') {
                            return false;
                        }
                        $hasRenewed = \App\Models\Tenant::where('client_id', 'like', $record->client_id . '-R%')
                            ->where('status', 'active')
                            ->exists();
                        return ! $hasRenewed;
                    })
                    ->modalHeading('Renew Tenant Agreement')
                    ->modalSubmitActionLabel('Confirm Renew')
                ->form([
                    TextInput::make('agreement_year')
                        ->label('Agreement Years')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->reactive(),
                    DatePicker::make('rent_start_date')
                        ->label('New Start Date')
                        ->default(now())
                        ->required()
                        ->reactive(),
                    Placeholder::make('preview')
                        ->label('Preview (Per SFT and Total Rent)')
                        ->content(function (callable $get, $record) {

                            $increase = (float) $get('rent_increase');
                            $html = '';
                            $total = 0;

                            // Make sure rent_items is loaded and is an array
                            $items = $record->rent_items ?? [];

                            if (empty($items)) {
                                return 'No rent items found.';
                            }

                            foreach ($items as $index => $item) {
                                $sft = (float) ($item['sft'] ?? 0);
                                $rate = (float) ($item['rate'] ?? 0);
                                $sub = $sft * $rate;
                                
                                $html .= "SFT " . ($index + 1) . ": {$sft}, Rate: " . number_format($rate, 2) .", Total: " . number_format($sub, 2) . " BDT<br>";
                                $total += $sub;
                            }

                            $html .= "<strong>Total: " . number_format($total, 2) . " BDT</strong>";

                            return new \Illuminate\Support\HtmlString($html);
                        }),
                ])
                ->action(function ($record, array $data) {

                    // 🔹 Generate new client_id with R1, R2 suffix
                    $oldId = $record->client_id;
                    $count = \App\Models\Tenant::where('client_id', 'like', $oldId . '-R%')->count();
                    $newClientId = $oldId . '-R' . ($count + 1);

                    // 🔹 Replicate old tenant
                    $newTenant = $record->replicate();
                    $newTenant->client_id = $newClientId;
                    $newTenant->agreement_year = $data['agreement_year'];
                    $newTenant->rent_start_date = $data['rent_start_date'];
                    $newTenant->expired_date = Carbon::parse($data['rent_start_date'])->addYears($data['agreement_year']);
                    $newTenant->status = 'active';

                    // Calculate rent_items and total rent
                    $total = 0;
                    $newRentItems = [];

                    foreach ($record->rent_items ?? [] as $item) {
                        $sft = (float) ($item['sft'] ?? 0);
                        $rate = (float) ($item['rate'] ?? 0);
                        $sub = $sft * $rate;

                        $newRentItems[] = [
                            'sft' => $sft,
                            'rate' => round($rate, 2),
                            'total' => round($sub, 2),
                        ];
                        $total += $sub;
                    }

                    $newTenant->rent_items = $newRentItems;
                    $newTenant->total_rent = round($total, 2);
                    $newTenant->save();

                    // 🔹 Disable old tenant
                    $record->status = 'inactive';
                    $record->save();
                }),
                
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
