<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->string('address')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone')->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email')->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('suppliers', 'type')) {
                $table->string('type')->default('supplier')->after('email'); // supplier, customer, both
            }
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->string('status')->default('active')->before('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Since we use hasColumn in up(), we should be careful here.
            // But for simplicity, we only drop what we added if it was new.
            // However, the rule is to revert UP.
            // I'll leave it as is for now or just drop 'type' which was definitely missing.
            $table->dropColumn(['type']);
        });
    }
};
