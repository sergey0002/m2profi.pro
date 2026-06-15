<?php

namespace App\Filament\Platform\Resources\TenantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Module;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'tenantModules';
    protected static ?string $title = 'Активные модули';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('module_slug')
                    ->label('Модуль')
                    ->options(Module::all()->pluck('name', 'slug'))
                    ->required()
                    ->disabled(fn ($record) => $record !== null), // Нельзя менять модуль после создания привязки
                Forms\Components\Toggle::make('is_enabled')
                    ->label('Включен')
                    ->default(true),
                Forms\Components\KeyValue::make('settings')
                    ->label('Настройки модуля')
                    ->keyLabel('Параметр')
                    ->valueLabel('Значение'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('module_slug')
            ->columns([
                Tables\Columns\TextColumn::make('module.name')
                    ->label('Модуль'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Статус')
                    ->boolean(),
                Tables\Columns\TextColumn::make('enabled_at')
                    ->label('Дата активации')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['enabled_at'] = $data['is_enabled'] ? now() : null;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        if ($data['is_enabled'] && !$record->is_enabled) {
                            $data['enabled_at'] = now();
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
