<?php

namespace App\Services;

use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockCountService
{
    public function createCount(array $data): StockCount
    {
        return DB::transaction(function () use ($data) {
            $count = StockCount::create([
                'code' => $this->generateCode(),
                'type' => $data['type'] ?? 'monthly',
                'status' => 'in_progress',
                'note' => $data['note'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            $products = $this->getProductsForCount($data);
            foreach ($products as $product) {
                $inventory = $product->inventory;
                $count->items()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'warehouse_location' => $product->location,
                    'system_quantity' => $inventory ? $inventory->quantity : 0,
                    'actual_quantity' => null,
                                    ]);
            }

            return $count->load('items.product', 'creator');
        });
    }

    public function submitDailyCount(string $countCode, array $entries, int $userId): StockCount
    {
        return DB::transaction(function () use ($countCode, $entries, $userId) {
            $count = StockCount::where('code', $countCode)->firstOrFail();

            if ($count->status !== 'in_progress') {
                throw new \Exception('Phiên kiểm kê không còn mở.');
            }

            foreach ($entries as $entry) {
                $item = StockCountItem::where('stock_count_id', $count->id)
                    ->where('product_id', $entry['product_id'])
                    ->first();

                if (!$item || $item->actual_quantity !== null) {
                    continue;
                }

                $actual = (float) $entry['actual_quantity'];
                $system = (float) $item->system_quantity;
                $item->update([
                    'actual_quantity' => $actual,
                    'difference' => round($actual - $system, 2),
                ]);
            }

            $allDone = $count->items()->whereNull('actual_quantity')->doesntExist();
            if ($allDone) {
                $count->update(['status' => 'completed']);
            }

            return $count->load('items.product', 'creator');
        });
    }

    public function finalizeCount(int $countId): StockCount
    {
        return DB::transaction(function () use ($countId) {
            $count = StockCount::with('items')->findOrFail($countId);

            foreach ($count->items as $item) {
                if ($item->actual_quantity === null) {
                    continue;
                }

                $inventory = Inventory::firstOrCreate(['product_id' => $item->product_id]);
                $inventory->quantity = $item->actual_quantity;
                $inventory->save();

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'type' => 'adjust',
                    'quantity' => $item->difference,
                    'warehouse_location' => $item->warehouse_location,
                    'reference_type' => StockCount::class,
                    'reference_id' => $count->id,
                    'note' => "Kiểm kê {$count->code}",
                    'created_by' => $count->created_by,
                ]);
            }

            $count->update(['status' => 'finalized']);
            return $count->load('items.product');
        });
    }

    public function getTodayDailyItems(): \Illuminate\Database\Eloquent\Collection
    {
        $today = now()->toDateString();
        $count = StockCount::where('type', 'daily')
            ->whereDate('created_at', $today)
            ->where('status', 'in_progress')
            ->first();

        if (!$count) {
            $firstUser = User::first();
            $count = StockCount::create([
                'code' => $this->generateDailyCode(),
                'type' => 'daily',
                'status' => 'in_progress',
                'note' => 'Kiểm kê ngày tự động',
                'created_by' => $firstUser ? $firstUser->id : 1,
            ]);

            $products = Product::where('status', 'active')
                ->whereNotIn('id', function ($q) use ($today) {
                    $q->select('product_id')->from('stock_count_items')
                        ->join('stock_counts', 'stock_count_items.stock_count_id', '=', 'stock_counts.id')
                        ->where('stock_counts.type', 'daily')
                        ->whereDate('stock_counts.created_at', '<', $today)
                        ->where('stock_counts.status', '!=', 'cancelled');
                })
                ->orderBy('location')
                ->limit(10)
                ->get();

            foreach ($products as $product) {
                $inventory = $product->inventory;
                $count->items()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'warehouse_location' => $product->location,
                    'system_quantity' => $inventory ? $inventory->quantity : 0,
                    'actual_quantity' => null,
                                    ]);
            }
        }

        return $count->load('items.product');
    }

    private function getProductsForCount(array $data): \Illuminate\Database\Eloquent\Collection
    {
        $query = Product::where('status', 'active');

        if (!empty($data['category_id'])) {
            $query->where('category_id', $data['category_id']);
        }
        if (!empty($data['location'])) {
            $query->where('location', $data['location']);
        }
        if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
            $query->whereIn('id', $data['product_ids']);
        }

        return $query->with('inventory')->get();
    }

    private function generateCode(): string
    {
        $date = now()->format('Ymd');
        $last = StockCount::whereDate('created_at', today())->count() + 1;
        return 'SK-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    private function generateDailyCode(): string
    {
        return 'SK-D-' . now()->format('Ymd') . '-' . str_pad(StockCount::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
    }
}
