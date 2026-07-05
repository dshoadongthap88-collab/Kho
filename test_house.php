<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

// Delete old test users and products
User::where("email", "like", "%@test.com")->delete();
Product::where("name", "like", "Test Product%")->delete();

// Create User in House 1 (Hoc Mon)
$user1 = User::create([
    "code" => "U1",
    "username" => "user1",
    "name" => "Test User 1",
    "email" => "test1@test.com",
    "password" => bcrypt("password"),
    "current_house_id" => 1,
    "role" => "user"
]);

// Create User in House 2 (Hau Nghia)
$user2 = User::create([
    "code" => "U2",
    "username" => "user2",
    "name" => "Test User 2",
    "email" => "test2@test.com",
    "password" => bcrypt("password"),
    "current_house_id" => 2,
    "role" => "user"
]);

// HR User
$userHR = User::create([
    "code" => "UHR",
    "username" => "userhr",
    "name" => "HR User",
    "email" => "hr@test.com",
    "password" => bcrypt("password"),
    "current_house_id" => 5,
    "role" => "hr"
]);

// Login as User 1
Auth::login($user1);
Product::create(["name" => "Test Product House 1", "code" => "TPH1"]);

// Login as User 2
Auth::login($user2);
Product::create(["name" => "Test Product House 2", "code" => "TPH2"]);

// Check products for User 1
Auth::login($user1);
echo "User 1 sees: " . Product::where("name", "like", "Test Product%")->count() . " products (Expected: 1)\n";
$p1 = Product::where("name", "like", "Test Product%")->first();
echo "Product name: " . ($p1 ? $p1->name : "None") . "\n";

// Check products for User 2
Auth::login($user2);
echo "User 2 sees: " . Product::where("name", "like", "Test Product%")->count() . " products (Expected: 1)\n";
$p2 = Product::where("name", "like", "Test Product%")->first();
echo "Product name: " . ($p2 ? $p2->name : "None") . "\n";

// Check products for HR
Auth::login($userHR);
echo "HR sees: " . Product::where("name", "like", "Test Product%")->count() . " test products (Expected: 2)\n";

