<?php

namespace App\Filament\Platform\Resources;

use App\Filament\Platform\Resources\TenantResource\Pages;
use App\Filament\Platform\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'Площадки (Сайты)';
    protected static ?string $modelLabel = 'Площадка';
    protected static ?string $pluralModelLabel = 'Площадки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subdomain')
                            ->label('Поддомен')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('domain')
                            ->label('Кастомный домен')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('status')
                            ->label('Активен')
                            ->onColor('success')
                            ->offColor('danger')
                            ->formatStateUsing(fn ($state) => $state === 'active')
                            ->dehydrateStateUsing(fn ($state) => $state ? 'active' : 'suspended')
                            ->default('active'),
                    ])->columns(2),

                Forms\Components\Section::make('Настройки Базы Данных')
                    ->schema([
                        Forms\Components\TextInput::make('db_host')
                            ->label('DB Host')
                            ->default('127.0.0.1')
                            ->required(),
                        Forms\Components\TextInput::make('db_port')
                            ->label('DB Port')
                            ->default('3306')
                            ->required(),
                        Forms\Components\TextInput::make('db_name')
                            ->label('DB Name')
                            ->required()
                            ->rules([
                                fn (Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $host = $get('db_host');
                                    $port = $get('db_port');
                                    $username = $get('db_username');
                                    $password = $get('db_password');
                    
                                    if (empty($password) && $record) {
                                        $password = $record->db_password;
                                    }
                    
                                    try {
                                        // Проверка подключения
                                        new \PDO("mysql:host={$host};port={$port};dbname={$value}", $username, $password, [
                                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                                            \PDO::ATTR_TIMEOUT => 5, // Таймаут 5 секунд
                                        ]);
                                    } catch (\Exception $e) {
                                        $fail("Ошибка подключения к БД: " . $e->getMessage());
                                    }
                                },
                            ]),
                        Forms\Components\TextInput::make('db_username')
                            ->label('DB Username')
                            ->default('root')
                            ->required(),
                        Forms\Components\TextInput::make('db_password')
                            ->label('DB Password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subdomain')
                    ->label('Поддомен')
                    ->sortable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label('Домен')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('db_name')
                    ->label('База данных'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended', 'deleted' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Активен',
                        'suspended' => 'Приостановлен',
                        'pending' => 'Ожидает',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\ModulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
