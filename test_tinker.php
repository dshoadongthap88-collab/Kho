$user = \App\Models\User::first();
if (!$user) { clone $user; /* just mock */ }
$request = \Illuminate\Http\Request::create('/api/stock-in/import', 'POST');
$request->setUserResolver(function() use ($user) { return $user; });
$file = new \Illuminate\Http\UploadedFile('d:/Project/docs/import_hang_hoa.csv', 'import_hang_hoa.csv', 'text/csv', null, true);
$request->files->set('file', $file);
$controller = app(\App\Http\Controllers\Api\StockInController::class);
try {
    $response = $controller->import($request);
    echo "=== TEST RESULT ===\n";
    echo json_encode($response->getData(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n===================\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
