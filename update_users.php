<?php

require __DIR__ . '/../bootstrap/app.php';

$app = app();
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'admin@example.com')->first();
if ($user) {
    $user->update(['phone' => '0123456789', 'password' => bcrypt('123456')]);
    echo "Updated admin user\n";
}

$user2 = \App\Models\User::where('email', 'user@example.com')->first();
if ($user2) {
    $user2->update(['phone' => '0987654321', 'password' => bcrypt('123456')]);
    echo "Updated staff user\n";
}

echo "Done!\n";
