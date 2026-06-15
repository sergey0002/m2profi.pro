<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = env('PLATFORM_ADMIN_EMAIL');
$password = env('PLATFORM_ADMIN_PASSWORD');

if (!$email || !$password) {
    echo "PLATFORM_ADMIN_EMAIL or PLATFORM_ADMIN_PASSWORD not set in .env\n";
    exit(1);
}

$user = User::where('email', $email)->orWhere('login', $email)->first();

if (!$user) {
    echo "Creating admin user: {$email}\n";
    $user = new User();
    $user->email = $email;
    $user->login = $email;
    $user->name = 'Platform Admin';
}

echo "Updating password for admin user: {$email}\n";
$user->password = Hash::make($password);
$user->save();

echo "Admin user ready.\n";
