<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CrmService
{
    public function getCustomers(array $filters = [])
    {
        $query = Supplier::query()->whereIn('type', ['customer', 'both']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function getSuppliers(array $filters = [])
    {
        $query = Supplier::query()->whereIn('type', ['supplier', 'both']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate(20);
    }

    public function createCustomer(array $data): Supplier
    {
        return Supplier::create(array_merge($data, ['type' => 'customer', 'status' => $data['status'] ?? 'active']));
    }

    public function createSupplier(array $data): Supplier
    {
        return Supplier::create(array_merge($data, ['type' => 'supplier', 'status' => $data['status'] ?? 'active']));
    }

    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'po_number' => $this->generatePONumber(),
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'total_amount' => $data['total_amount'] ?? 0,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $qty = $item['quantity'] ?? 0;
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $po->update(['total_amount' => $total]);

            return $po->load('items.product', 'supplier');
        });
    }

    public function getPurchaseOrders(array $filters = [])
    {
        $query = PurchaseOrder::with(['supplier', 'items']);

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('order_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('order_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('order_date', 'desc')->paginate(20);
    }

    private function generatePONumber(): string
    {
        $date = now()->format('Ymd');
        $last = PurchaseOrder::whereDate('created_at', today())->count() + 1;
        return 'PO-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}