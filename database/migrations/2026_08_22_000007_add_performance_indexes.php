<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm indexes để tối ưu hiệu năng các query thường dùng:
 * - Search theo code/name (LIKE prefix)
 * - Filter theo status, created_at, product_id
 * - JOIN giữa stock_out_items ↔ products ↔ inventories
 */
return new class extends Migration
{
    public function up(): void
    {
        // products: tìm theo code (autocomplete, search)
        $this->addIndex('products', 'products_code_index', ['code']);
        // products: tìm theo name prefix
        $this->addIndex('products', 'products_name_index', ['name']);
        // products: filter status + type (hay dùng cùng nhau)
        $this->addIndex('products', 'products_status_type_index', ['status', 'type']);

        // inventories: JOIN products.id = inventories.product_id
        $this->addIndex('inventories', 'inventories_product_id_index', ['product_id']);
        // inventories: filter + sort theo quantity
        $this->addIndex('inventories', 'inventories_quantity_index', ['quantity']);

        // stock_ins: filter theo ngày + tìm code
        $this->addIndex('stock_ins', 'stock_ins_created_at_index', ['created_at']);
        $this->addIndex('stock_ins', 'stock_ins_code_index', ['code']);
        $this->addIndex('stock_ins', 'stock_ins_stock_in_date_index', ['stock_in_date']);

        // stock_outs: filter theo ngày + tìm code
        $this->addIndex('stock_outs', 'stock_outs_created_at_index', ['created_at']);
        $this->addIndex('stock_outs', 'stock_outs_code_index', ['code']);
        $this->addIndex('stock_outs', 'stock_outs_status_index', ['status']);
        $this->addIndex('stock_outs', 'stock_outs_customer_name_index', ['customer_name']);

        // stock_out_items: JOIN với products + inventory + filter ngày
        $this->addIndex('stock_out_items', 'stock_out_items_product_id_index', ['product_id']);
        $this->addIndex('stock_out_items', 'stock_out_items_stock_out_id_index', ['stock_out_id']);
        $this->addIndex('stock_out_items', 'stock_out_items_created_at_index', ['created_at']);
        // Composite index cho query GROUP BY product_id + filter ngày
        $this->addIndex('stock_out_items', 'stock_out_items_product_created_index', ['product_id', 'created_at']);

        // stock_in_items: JOIN với stock_in
        $this->addIndex('stock_in_items', 'stock_in_items_stock_in_id_index', ['stock_in_id']);
        $this->addIndex('stock_in_items', 'stock_in_items_product_id_index', ['product_id']);

        // inventory_transactions: filter theo product_id + ngày (dùng nhiều trong StockReport)
        $this->addIndex('inventory_transactions', 'inv_tx_product_created_index', ['product_id', 'created_at']);
        $this->addIndex('inventory_transactions', 'inv_tx_created_at_index', ['created_at']);

        // delivery_reports: filter theo status + delivered_at
        $this->addIndex('delivery_reports', 'delivery_reports_status_index', ['status']);
        $this->addIndex('delivery_reports', 'delivery_reports_delivered_at_index', ['delivered_at']);
        $this->addIndex('delivery_reports', 'delivery_reports_payment_status_index', ['payment_status']);
        $this->addIndex('delivery_reports', 'delivery_reports_stock_out_id_index', ['stock_out_id']);

        // suppliers: tìm theo name
        $this->addIndex('suppliers', 'suppliers_name_index', ['name']);

        // purchase_plans: filter theo product_id + status
        $this->addIndex('purchase_plans', 'purchase_plans_product_id_index', ['product_id']);
        $this->addIndex('purchase_plans', 'purchase_plans_status_index', ['status']);

        // assets: tìm theo equipment_code
        $this->addIndex('assets', 'assets_equipment_code_index', ['equipment_code']);

        // stock_counts: filter theo status
        $this->addIndex('stock_counts', 'stock_counts_created_at_index', ['created_at']);

        // users: tìm theo email, phone, username
        $this->addIndex('users', 'users_phone_index', ['phone']);
    }

    /**
     * Thêm index an toàn — bỏ qua nếu đã tồn tại.
     */
    private function addIndex(string $table, string $indexName, array $columns): void
    {
        try {
            if (!Schema::hasTable($table)) return;
            Schema::table($table, function (Blueprint $t) use ($indexName, $columns) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Index đã tồn tại hoặc bảng không có cột — bỏ qua
        }
    }

    public function down(): void
    {
        $indexes = [
            'products' => ['products_code_index', 'products_name_index', 'products_status_type_index'],
            'inventories' => ['inventories_product_id_index', 'inventories_quantity_index'],
            'stock_ins' => ['stock_ins_created_at_index', 'stock_ins_code_index', 'stock_ins_stock_in_date_index'],
            'stock_outs' => ['stock_outs_created_at_index', 'stock_outs_code_index', 'stock_outs_status_index', 'stock_outs_customer_name_index'],
            'stock_out_items' => ['stock_out_items_product_id_index', 'stock_out_items_stock_out_id_index', 'stock_out_items_created_at_index', 'stock_out_items_product_created_index'],
            'stock_in_items' => ['stock_in_items_stock_in_id_index', 'stock_in_items_product_id_index'],
            'inventory_transactions' => ['inv_tx_product_created_index', 'inv_tx_created_at_index'],
            'delivery_reports' => ['delivery_reports_status_index', 'delivery_reports_delivered_at_index', 'delivery_reports_payment_status_index', 'delivery_reports_stock_out_id_index'],
            'suppliers' => ['suppliers_name_index'],
            'purchase_plans' => ['purchase_plans_product_id_index', 'purchase_plans_status_index'],
            'assets' => ['assets_equipment_code_index'],
            'stock_counts' => ['stock_counts_created_at_index'],
            'users' => ['users_phone_index'],
        ];

        foreach ($indexes as $table => $idxList) {
            try {
                Schema::table($table, function (Blueprint $t) use ($idxList) {
                    foreach ($idxList as $idx) {
                        try { $t->dropIndex($idx); } catch (\Exception $e) {}
                    }
                });
            } catch (\Exception $e) {}
        }
    }
};
