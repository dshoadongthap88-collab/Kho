<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\Auth;
use App\Models\User;
$user1 = User::first();
Auth::login($user1);
echo auth()->check() ? "Logged in as " . auth()->user()->name : "Not logged in";
echo "\nHouse ID from auth: " . auth()->user()->current_house_id;
