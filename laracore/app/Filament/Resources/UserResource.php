<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Agency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Пользователи NEW';
    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Данные пользователя')
                    ->schema([
                        Forms\Components\TextInput::make('login')
                            ->label('Логин')
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'login', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль (legacy: открытый вид)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label('ФИО')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('e_mail')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Select::make('agency_id')
                            ->label('Агентство')
                            ->options(function () {
                                if (!\Illuminate\Support\Facades\Schema::hasTable('agency')) return [];
                                return Agency::all()->pluck('caption', 'agency_id');
                            })
                            ->searchable()
                            ->required()
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),
                        Forms\Components\Select::make('roles')
                            ->label('Роли')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('login')
                    ->label('Логин')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agency.caption')
                    ->label('Агентство')
                    ->sortable()
                    ->visible(fn () => \Illuminate\Support\Facades\Schema::hasTable('agency') && auth()->user()?->isSuperAdmin()),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'super-admin' => 'danger',
                        'agency-admin' => 'warning',
                        'agent' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('del')
                    ->label('Удален')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('agency')
                    ->label('Агентство')
                    ->relationship('agency', 'caption')
                    ->visible(fn () => \Illuminate\Support\Facades\Schema::hasTable('agency') && auth()->user()?->isSuperAdmin()),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Роль')
                    ->relationship('roles', 'name'),
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

        return $query->where('id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
