<?php

namespace App\Filament\Platform\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.platform.pages.dashboard';
    
    public function getTitle(): string
    {
        return 'Platform Dashboard';
    }
    
    public function getHeading(): string
    {
        return 'Welcome to M2 Profi Platform';
    }
}
