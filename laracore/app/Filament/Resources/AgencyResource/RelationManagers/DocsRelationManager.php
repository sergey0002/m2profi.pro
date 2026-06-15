<?php

namespace App\Filament\Resources\AgencyResource\RelationManagers;

use App\Models\AgencyDoc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocsRelationManager extends RelationManager
{
    protected static string $relationship = 'docs';
    protected static ?string $title = 'Документы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('doc_type')
                    ->label('Тип документа')
                    ->options([
                        1 => 'Договор',
                        2 => 'Доп. соглашение',
                        3 => 'Учредительные документы',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('doc_start')
                    ->label('Дата начала'),
                Forms\Components\DatePicker::make('doc_and')
                    ->label('Дата окончания'),
                Forms\Components\FileUpload::make('agency_file')
                    ->label('Файл')
                    ->disk('public')
                    ->directory('agency_docs')
                    ->required(),
                Forms\Components\Textarea::make('comment')
                    ->label('Примечание')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->columns([
                Tables\Columns\TextColumn::make('doc_type')
                    ->label('Тип')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Договор',
                        2 => 'Доп. соглашение',
                        3 => 'Учредительные',
                        default => 'Другое',
                    }),
                Tables\Columns\TextColumn::make('doc_start')
                    ->label('Начало')
                    ->date(),
                Tables\Columns\TextColumn::make('doc_and')
                    ->label('Окончание')
                    ->date()
                    ->color(fn (AgencyDoc $record) => $record->isExpired() ? 'danger' : null),
                Tables\Columns\IconColumn::make('agency_file')
                    ->label('Файл')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (AgencyDoc $record) => $record->agency_file ? \Storage::url($record->agency_file) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
