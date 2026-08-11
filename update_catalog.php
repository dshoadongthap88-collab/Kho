<?php
$content = file_get_contents('app/Livewire/Warehouse/ProductCatalog.php');

// Replace properties
$content = str_replace(
    "public \$minStocks = [];",
    "public \$minStocks = [];\n    public \$maxStocks = [];",
    $content
);

$content = str_replace(
    "public \$dateFrom = '';\n    public \$dateTo = '';",
    "",
    $content
);

$content = str_replace(
    "protected \$queryString = ['search', 'dateFrom', 'dateTo', 'filterMode', 'activeTab'];",
    "protected \$queryString = ['search', 'filterMode', 'activeTab'];",
    $content
);

// Remove ->when($this->dateFrom... ) and ->when($this->dateTo... )
$content = preg_replace('/->when\(\$this->dateFrom, function\(\$q\)\s*\{\s*\$q->where\(\'created_at\', \'>=\', \$this->dateFrom \. \' 00:00:00\'\);\s*\}\)/', '', $content);
$content = preg_replace('/->when\(\$this->dateTo, function\(\$q\)\s*\{\s*\$q->where\(\'created_at\', \'<=\', \$this->dateTo \. \' 23:59:59\'\);\s*\}\)/', '', $content);

// Add initialization of maxStocks
$search = <<<'EOD'
                if (!isset($this->minStocks[$product->id])) {
                    $this->minStocks[$product->id] = $product->min_stock > 0 ? $product->min_stock : '';
                }
EOD;
$replace = <<<'EOD'
                if (!isset($this->minStocks[$product->id])) {
                    $this->minStocks[$product->id] = $product->min_stock > 0 ? $product->min_stock : '';
                }
                if (!isset($this->maxStocks[$product->id])) {
                    $this->maxStocks[$product->id] = $product->max_stock > 0 ? $product->max_stock : '';
                }
EOD;
$content = str_replace($search, $replace, $content);

// Add saveMaxStocks method
$saveMaxStocksMethod = <<<'EOD'

    public function saveMaxStocks()
    {
        try {
            $count = 0;
            $warnings = [];

            foreach ($this->maxStocks as $productId => $value) {
                $product = App\Models\Product::with('inventory')->find($productId);
                if ($product) {
                    $newVal = (float)($value ?: 0);
                    $currentQty = (float)($product->inventory?->quantity ?? 0);

                    if ($newVal > 0 && $newVal < $currentQty) {
                        $warnings[] = "{$product->code} (tồn: {$currentQty}, max: {$newVal})";
                    }

                    if ($product->max_stock != $newVal) {
                        $product->update(['max_stock' => $newVal]);
                        $count++;
                    }
                }
            }

            if (count($warnings) > 0) {
                session()->flash('warning', 'Đã lưu ' . $count . ' vật tư. CẢNH BÁO: ' . count($warnings) . ' mặt hàng đang vượt mức tồn tối đa: ' . implode(', ', $warnings));
            } else {
                session()->flash('message', "Đã lưu thành công định mức tồn tối đa cho {$count} vật tư!");
            }
            $this->dispatch('max-stocks-saved');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
EOD;
$content = str_replace("public function saveMinStocks()\n    {", $saveMaxStocksMethod . "\n\n    public function saveMinStocks()\n    {", $content);

file_put_contents('app/Livewire/Warehouse/ProductCatalog.php', $content);
echo "Updated ProductCatalog.php\n";
