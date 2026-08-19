<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $user = \App\Models\User::first();
    Auth::login($user);
    $response = app()->handle(Illuminate\Http\Request::create('/hr/permissions'));
    echo 'STATUS: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() == 500) {
        if (isset($response->exception)) {
            echo $response->exception;
        } else {
            echo $response->getContent();
        }
    }
} catch (\Exception $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
} catch (\Throwable $e) {
    echo 'THROWABLE: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
}
