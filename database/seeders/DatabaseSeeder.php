<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AssetDailyOdosTableSeeder::class,
            AssetsTableSeeder::class,
            BomsTableSeeder::class,
            CategoriesTableSeeder::class,
            ChatMessagesTableSeeder::class,
            HousesTableSeeder::class,
            InventoriesTableSeeder::class,
            InventoryTransactionsTableSeeder::class,
            MaintenanceBomItemsTableSeeder::class,
            MaintenanceBomsTableSeeder::class,
            MaintenanceRulesTableSeeder::class,
            ProductsTableSeeder::class,
            ProjectsTableSeeder::class,
            PurchasePlanHistoriesTableSeeder::class,
            PurchasePlansTableSeeder::class,
            SettingsTableSeeder::class,
            StockInItemsTableSeeder::class,
            StockInsTableSeeder::class,
            StockOutItemsTableSeeder::class,
            StockOutsTableSeeder::class,
            StockRecoveriesTableSeeder::class,
            StockTransferItemsTableSeeder::class,
            StockTransfersTableSeeder::class,
            SuppliersTableSeeder::class,
            SystemModulesTableSeeder::class,
            UsersTableSeeder::class,
        ]);
    }
}
