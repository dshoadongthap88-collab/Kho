<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = new \App\Models\Product();
$scopes = $p->getGlobalScopes();
print_r(array_keys($scopes));
