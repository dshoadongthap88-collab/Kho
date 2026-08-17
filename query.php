<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'LIKE', '%NGÀ%')->orWhere('phone', 'LIKE', '%086716207%')->first();
if ($user) {
    echo json_encode([
        'id' => $user->id,
        'name' => $user->name,
        'phone' => $user->phone,
        'email' => $user->email,
        'username' => $user->username,
        'status' => $user->status,
        'password_hash' => $user->password
    ], JSON_PRETTY_PRINT);
} else {
    echo 'No user found';
}
