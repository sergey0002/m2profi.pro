<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\SettingSectionResource\Pages;
use App\Filament\Platform\Resources\SettingSectionResource\RelationManagers;
use App\Models\SettingSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingSectionResource extends Resource
{
    protected static ?string $model = SettingSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'Разделы настроек';
    protected static ?string $modelLabel = 'Раздел';
    protected static ?string $pluralModelLabel = 'Разделы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('module_id')
                    ->relationship('module', 'name')
                    ->required()
                    ->label('Модуль'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Название'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Название'),
                Tables\Columns\TextColumn::make('module.name')
                    ->sortable()
                    ->searchable()
                    ->label('Модуль'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('module_id')
                    ->relationship('module', 'name')
                    ->label('Модуль'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListSettingSections::route('/'),
            'create' => Pages\CreateSettingSection::route('/create'),
            'edit' => Pages\EditSettingSection::route('/{record}/edit'),
        ];
    }
}
