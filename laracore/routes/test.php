<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/test-filament-auth', function () {
    $user = User::where('email', 'admin@m2profi.pro')->first();
    
    if (!$user) {
        return 'User not found!';
    }
    
    $output = [];
    $output[] = "User: {$user->login}";
    $output[] = "Email: {$user->email}";
    $output[] = "Roles: " . $user->getRoleNames()->implode(', ');
    $output[] = "Has platform-admin role: " . ($user->hasRole('platform-admin') ? 'YES' : 'NO');
    
    // Test authentication
    $credentials = ['email' => 'admin@m2profi.pro', 'password' => 'SecurePassword123!'];
    $authResult = Auth::attempt($credentials);
    $output[] = "Auth::attempt result: " . ($authResult ? 'SUCCESS' : 'FAILED');
    
    if ($authResult) {
        $authenticatedUser = Auth::user();
        $output[] = "Authenticated as: {$authenticatedUser->login}";
        
        // Test panel access
        try {
            $panel = \Filament\Facades\Filament::getPanel('platform');
            $canAccess = $authenticatedUser->canAccessPanel($panel);
            $output[] = "Can access platform panel: " . ($canAccess ? 'YES' : 'NO');
            $output[] = "Panel ID: " . $panel->getId();
            $output[] = "Panel path: " . $panel->getPath();
            $output[] = "Panel domain: " . $panel->getDomain();
        } catch (\Exception $e) {
            $output[] = "Error getting panel: " . $e->getMessage();
        }
    }
    
    return '<pre>' . implode("\n", $output) . '</pre>';
})->middleware('web');
