<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('username', '0708091050')->first();
if ($user) {
    $user->password = bcrypt('101088');
    $user->save();
    echo "Password reset successfully.";
} else {
    echo "User not found.";
}
