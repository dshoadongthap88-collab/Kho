<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/hr/projects', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
// If it's a redirect, print destination
if ($response->isRedirection()) {
    echo "Redirect: " . $response->headers->get('Location') . "\n";
} else {
    echo "Output: \n" . substr($response->getContent(), 0, 500) . "\n";
}
$kernel->terminate($request, $response);
