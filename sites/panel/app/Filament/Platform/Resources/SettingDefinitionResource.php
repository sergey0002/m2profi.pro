<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\SettingDefinitionResource\Pages;
use App\Models\SettingDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingDefinitionResource extends Resource
{
    protected static ?string $model = SettingDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Определения настроек';
    protected static ?string $modelLabel = 'Настройка';
    protected static ?string $pluralModelLabel = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('module_id')
                    ->label('Модуль')
                    ->relationship('module', 'name')
                    ->required()
                    ->default(fn () => \App\Models\Module::where('slug', 'main')->first()?->id),
                Forms\Components\TextInput::make('key')
                    ->label('Ключ (slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Тип')
                    ->options([
                        'string' => 'Строка',
                        'text' => 'Текст',
                        'boolean' => 'Логическое (Да/Нет)',
                        'integer' => 'Число',
                        'json' => 'JSON',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        // Reset virtual fields when type changes to avoid confusion
                        $set('default_value_boolean', false);
                        $set('default_value_text', null);
                        $set('default_value_string', null);
                        // Reset actual value
                        $set('default_value', null);
                    }),
                
                Forms\Components\Hidden::make('default_value')
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? trim($state) : $state),

                Forms\Components\Checkbox::make('default_value_boolean')
                    ->label('Значение по умолчанию')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'boolean')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\Checkbox $component, $state, $record) {
                        if ($record) {
                            $component->state((bool) $record->default_value);
                        }
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('default_value', $state ? '1' : '0'); 
                    }),

                Forms\Components\Textarea::make('default_value_text')
                    ->label('Значение по умолчанию')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'text')
                    ->dehydrated(false)
                    ->live(debounce: 500)
                    ->afterStateHydrated(function (Forms\Components\Textarea $component, $state, $record) {
                        if ($record) {
                            $component->state($record->default_value);
                        }
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('default_value', trim($state ?? ''));
                    })
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('default_value_string')
                    ->label('Значение по умолчанию')
                    ->visible(fn (Forms\Get $get) => !in_array($get('type'), ['boolean', 'text']))
                    ->dehydrated(false)
                    ->live(debounce: 500)
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $record) {
                        if ($record) {
                            $component->state($record->default_value);
                        }
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('default_value', trim($state ?? ''));
                    }),

                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Select::make('section_id')
                            ->label('Раздел')
                            ->relationship('section', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('module_id')
                                    ->relationship('module', 'name')
                                    ->required(),
                            ]),

                        Forms\Components\Toggle::make('is_global')
                            ->label('Глобальная настройка')
                            ->helperText('Если включено, настройка общая для всех площадок')
                            ->default(false),
                            
                        Forms\Components\Toggle::make('is_public')
                            ->label('Отображать в панели клиента')
                            ->default(true),

                        Forms\Components\Toggle::make('is_system')
                            ->label('Системная настройка')
                            ->helperText('Защищена от удаления')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->content(view('filament.platform.resources.setting-definition.table'))
            ->paginated(false)
            ->searchable()
            ->persistFiltersInSession()
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->filters([
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('В панели клиента'),
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('Системная'),
                Tables\Filters\TernaryFilter::make('is_locked')
                    ->label('Заблокирована (ReadOnly)'),
                Tables\Filters\SelectFilter::make('module_id')
                    ->label('Модуль')
                    ->relationship('module', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('Раздел')
                    ->relationship('section', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('togglePublic')
                    ->label(fn (SettingDefinition $record) => $record->is_public ? 'Скрыть' : 'Показать')
                    ->icon(fn (SettingDefinition $record) => $record->is_public ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->color(fn (SettingDefinition $record) => $record->is_public ? 'success' : 'gray')
                    ->action(function (SettingDefinition $record) {
                        $record->update(['is_public' => !$record->is_public]);
                    }),

                Tables\Actions\Action::make('toggleSystem')
                    ->label('Система')
                    ->action(function (SettingDefinition $record) {
                        $record->update(['is_system' => !$record->is_system]);
                    }),

                Tables\Actions\EditAction::make()
                    ->modalWidth('2xl'),

                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (SettingDefinition $record) => $record->is_system)
                    ->before(function (Tables\Actions\DeleteAction $action, SettingDefinition $record) {
                        if ($record->is_system) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Ошибка')
                                ->body('Нельзя удалить системную настройку!')
                                ->send();
                            $action->cancel();
                        }
                    }),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                             $records->each(function ($record) {
                                 if (!$record->is_system) {
                                     $record->delete();
                                 }
                             });
                        }),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['module', 'section'])
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettingDefinitions::route('/'),
            'create' => Pages\CreateSettingDefinition::route('/create'),
            'edit' => Pages\EditSettingDefinition::route('/{record}/edit'),
        ];
    }
}
