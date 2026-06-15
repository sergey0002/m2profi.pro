<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgencyResource\Pages;
use App\Filament\Resources\AgencyResource\RelationManagers;
use App\Models\Agency;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AgencyResource extends Resource
{
    protected static ?string $model = Agency::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Агентства NEW';
    protected static ?string $modelLabel = 'Агентство';
    protected static ?string $pluralModelLabel = 'Агентства';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('caption')
                            ->label('Название организации')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('inn')
                            ->label('ИНН')
                            ->required()
                            ->maxLength(12),
                        Forms\Components\Select::make('type')
                            ->label('Тип')
                            ->options([
                                Agency::TYPE_SELF_REGISTERED => 'Самозарегистрированное',
                                Agency::TYPE_GLOBAL_USER => 'Глобальный пользователь',
                                Agency::TYPE_SALES_DEPARTMENT => 'Отдел продаж',
                                Agency::TYPE_ADMINS => 'Администраторы',
                            ])
                            ->default(Agency::TYPE_SELF_REGISTERED)
                            ->required(),
                        Forms\Components\Select::make('registration_status')
                            ->label('Статус регистрации')
                            ->options([
                                Agency::STATUS_ACTIVE => 'Активно',
                                Agency::STATUS_APPLICATION => 'Заявка',
                                Agency::STATUS_REJECTED => 'Отклонено',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Toggle::make('unactiv')
                            ->label('Заблокировано')
                            ->helperText('Блокирует доступ всем пользователям агентства'),
                        Forms\Components\Textarea::make('comment')
                            ->label('Примечание (служебный комментарий)')
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Section::make('Данные из заявки')
                    ->schema([
                        Forms\Components\ViewField::make('registration_data_view')
                            ->view('filament.resources.agency.registration-data')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->registration_data),
                    ])->visible(fn ($record) => $record && $record->registration_status !== Agency::STATUS_ACTIVE),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agency_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inn')
                    ->label('ИНН')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn (Agency $record) => $record->getTypeName())
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        Agency::TYPE_SALES_DEPARTMENT => 'success',
                        Agency::TYPE_ADMINS => 'danger',
                        Agency::TYPE_GLOBAL_USER => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('registration_status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        Agency::STATUS_ACTIVE => 'Активно',
                        Agency::STATUS_APPLICATION => 'Заявка',
                        Agency::STATUS_REJECTED => 'Отклонено',
                        default => '?',
                    })
                    ->color(fn (int $state): string => match ($state) {
                        Agency::STATUS_ACTIVE => 'success',
                        Agency::STATUS_APPLICATION => 'warning',
                        Agency::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('unactiv')
                    ->label('Блок')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Примечание')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('registration_status')
                    ->label('Статус регистрации')
                    ->options([
                        Agency::STATUS_ACTIVE => 'Активно',
                        Agency::STATUS_APPLICATION => 'Заявки',
                        Agency::STATUS_REJECTED => 'Отклоненные',
                    ]),
                Tables\Filters\TernaryFilter::make('unactiv')
                    ->label('Заблокированные'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Подтвердить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Agency $record) => $record->registration_status === Agency::STATUS_APPLICATION)
                    ->form([
                        Forms\Components\TextInput::make('login')
                            ->label('Логин администратора')
                            ->required()
                            ->default(fn (Agency $record) => \Str::slug($record->caption, '_'))
                            ->unique('users', 'login'),
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->required()
                            ->default(fn () => \Str::random(10)),
                    ])
                    ->action(function (Agency $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $regData = $record->registration_data;
                            
                            // Создаем админа
                            $user = User::create([
                                'login' => $data['login'],
                                'password' => $data['password'],
                                'name' => $regData['admin_name'] ?? 'Admin',
                                'e_mail' => $regData['admin_email'] ?? '',
                                'phone' => $regData['admin_phone'] ?? '',
                                'agency_id' => $record->agency_id,
                                'add_datetime' => now(),
                            ]);
                            
                            $user->assignRole('agency-admin');
                            
                            $record->update([
                                'registration_status' => Agency::STATUS_ACTIVE,
                                'unactiv' => 0,
                                'admin_user_id' => $user->id,
                            ]);

                            // Отправка email (будет реализовано позже)
                        });

                        Notification::make()
                            ->title('Агентство подтверждено')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Agency $record) => $record->registration_status === Agency::STATUS_APPLICATION)
                    ->requiresConfirmation()
                    ->action(function (Agency $record) {
                        $record->update(['registration_status' => Agency::STATUS_REJECTED]);
                        Notification::make()
                            ->title('Заявка отклонена')
                            ->warning()
                            ->send();
                    }),
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
            RelationManagers\DocsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgencies::route('/'),
            'create' => Pages\CreateAgency::route('/create'),
            'edit' => Pages\EditAgency::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('agency')) {
            return false;
        }
        return auth()->user()?->isSuperAdmin() ?? false;
    }
}
