<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgencyDocResource\Pages;
use App\Models\AgencyDoc;
use App\Models\Agency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgencyDocResource extends Resource
{
    protected static ?string $model = AgencyDoc::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Документы агентств NEW';
    protected static ?string $modelLabel = 'Документ';
    protected static ?string $pluralModelLabel = 'Документы';

    protected static bool $shouldRegisterNavigation = false; // Будем выводить через Relation или только для админа

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('agency_id')
                    ->label('Агентство')
                    ->options(function() {
                        if (!\Illuminate\Support\Facades\Schema::hasTable('agency')) return [];
                        return Agency::all()->pluck('caption', 'agency_id');
                    })
                    ->searchable()
                    ->required(),
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
                    ->label('Дата окончания (если есть)'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agency.caption')
                    ->label('Агентство')
                    ->sortable()
                    ->searchable(),
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
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('doc_and')
                    ->label('Окончание')
                    ->date()
                    ->sortable()
                    ->color(fn (AgencyDoc $record) => $record->isExpired() ? 'danger' : null),
                Tables\Columns\IconColumn::make('agency_file')
                    ->label('Файл')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (AgencyDoc $record) => $record->agency_file ? \Storage::url($record->agency_file) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('agency')
                    ->relationship('agency', 'caption')
                    ->visible(fn () => \Illuminate\Support\Facades\Schema::hasTable('agency')),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAgencyAdmin()) {
            return $query->where('agency_id', $user->agency_id);
        }

        return $query->where('id', 0); // Обычным агентам нельзя
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgencyDocs::route('/'),
            'create' => Pages\CreateAgencyDoc::route('/create'),
            'edit' => Pages\EditAgencyDoc::route('/{record}/edit'),
        ];
    }
}
