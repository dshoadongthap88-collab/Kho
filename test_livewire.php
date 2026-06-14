<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/')
);

$transfer = \App\Models\StockTransfer::first();
if (!$transfer) {
    echo "No transfer found.";
    exit;
}

$component = Livewire\Livewire::test(\App\Livewire\Warehouse\StockTransferList::class)
    ->call('viewDetail', $transfer->id);

echo "viewDetail executed without exception. showDetailModal=" . ($component->get('showDetailModal') ? 'true' : 'false') . "\n";
