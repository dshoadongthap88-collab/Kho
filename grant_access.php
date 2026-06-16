<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('username', '0708091050')->first();
if ($user) {
    $houses = $user->allowed_houses ?? [];
    if (!is_array($houses)) {
        $houses = json_decode($houses, true) ?? [];
    }
    
    if (!in_array("5", $houses) && !in_array(5, $houses)) {
        $houses[] = "5";
    }
    
    $user->allowed_houses = $houses;
    $user->save();
    echo "Added Ngôi nhà HR (ID 5) to allowed_houses.\n";
} else {
    echo "User not found.\n";
}
