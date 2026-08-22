<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration thay thế cho logic ALTER TABLE động trong StockOutForm::mount().
 * Các cột này trước đây bị tạo on-the-fly trong mỗi HTTP request — rất nguy hiểm.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_outs', 'project_name'))     $table->string('project_name')->nullable();
            if (!Schema::hasColumn('stock_outs', 'document_number'))  $table->string('document_number')->nullable();
            if (!Schema::hasColumn('stock_outs', 'license_plate'))    $table->string('license_plate')->nullable();
            if (!Schema::hasColumn('stock_outs', 'km_number'))        $table->string('km_number')->nullable();
            if (!Schema::hasColumn('stock_outs', 'operating_hours'))  $table->string('operating_hours')->nullable();
            if (!Schema::hasColumn('stock_outs', 'device_name'))      $table->string('device_name')->nullable();
            if (!Schema::hasColumn('stock_outs', 'department'))       $table->string('department')->nullable();
            if (!Schema::hasColumn('stock_outs', 'warehouse_keeper')) $table->string('warehouse_keeper')->nullable();
            if (!Schema::hasColumn('stock_outs', 'supervisor_qltb'))  $table->string('supervisor_qltb')->nullable();
            if (!Schema::hasColumn('stock_outs', 'supervisor_ca'))    $table->string('supervisor_ca')->nullable();
            if (!Schema::hasColumn('stock_outs', 'repair_staff'))     $table->string('repair_staff')->nullable();
            if (!Schema::hasColumn('stock_outs', 'operator_name'))    $table->string('operator_name')->nullable();
        });

        Schema::table('stock_out_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_out_items', 'requested_quantity')) $table->decimal('requested_quantity', 15, 4)->nullable();
            if (!Schema::hasColumn('stock_out_items', 'recovered_quantity')) $table->decimal('recovered_quantity', 15, 4)->nullable();
            if (!Schema::hasColumn('stock_out_items', 'item_note'))          $table->string('item_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $cols = ['project_name','document_number','license_plate','km_number',
                     'operating_hours','device_name','department','warehouse_keeper',
                     'supervisor_qltb','supervisor_ca','repair_staff','operator_name'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('stock_outs', $col)) $table->dropColumn($col);
            }
        });

        Schema::table('stock_out_items', function (Blueprint $table) {
            foreach (['requested_quantity','recovered_quantity','item_note'] as $col) {
                if (Schema::hasColumn('stock_out_items', $col)) $table->dropColumn($col);
            }
        });
    }
};
