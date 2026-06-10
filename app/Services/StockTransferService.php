<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransferService
{
    public function createTransfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            $transfer = StockTransfer::create([
                'transfer_code' => $this->generateCode(),
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => 'pending',
                'note' => $data['note'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'product_code' => $product->code ?? null,
                    'product_name' => $product->name ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $product->unit ?? null,
                    'note' => $item['note'] ?? null,
                ]);
            }

            return $transfer->load('items.product', 'creator', 'fromWarehouse', 'toWarehouse');
        });
    }

    public function confirmTransfer(int $transferId, int $confirmedBy): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $confirmedBy) {
            $transfer = StockTransfer::with('items')->findOrFail($transferId);

            if ($transfer->status !== 'pending') {
                throw new \Exception('Phiếu chuyển kho đã được xử lý.');
            }

            foreach ($transfer->items as $item) {
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $item->product_id],
                    ['warehouse_location' => $transfer->toWarehouse->name]
                );
                if ($inventory->wasRecentlyCreated && $inventory->warehouse_location !== $transfer->toWarehouse->name) {
                    $inventory->update(['warehouse_location' => $transfer->toWarehouse->name]);
                }
                $inventory->increment('quantity', $item->quantity);

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'type' => 'transfer_in',
                    'quantity' => $item->quantity,
                    'warehouse_location' => $transfer->toWarehouse->name,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'note' => "Chuyển kho từ {$transfer->fromWarehouse->name} đến {$transfer->toWarehouse->name}",
                    'created_by' => $confirmedBy,
                ]);
            }

            $transfer->update([
                'status' => 'completed',
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => now(),
            ]);

            return $transfer->load('items.product', 'creator', 'fromWarehouse', 'toWarehouse', 'confirmer');
        });
    }

    public function cancelTransfer(int $transferId, int $cancelledBy): StockTransfer
    {
        $transfer = StockTransfer::findOrFail($transferId);

        if ($transfer->status === 'completed') {
            throw new \Exception('Không thể hủy phiếu đã hoàn thành.');
        }

        $transfer->update([
            'status' => 'cancelled',
            'cancelled_by' => $cancelledBy,
            'cancelled_at' => now(),
        ]);

        return $transfer->load('items.product', 'creator', 'fromWarehouse', 'toWarehouse');
    }

    private function generateCode(): string
    {
        $date = now()->format('Ymd');
        $last = StockTransfer::whereDate('created_at', today())->count() + 1;
        return 'ST-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}