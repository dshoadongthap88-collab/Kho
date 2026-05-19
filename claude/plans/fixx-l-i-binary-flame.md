# Context
This change addresses inconsistent terminology where 'sản ph' (product) appears throughout the ERP system. The goal is to standardize to 'vật tư' (material) across all modules for consistency and improved user experience.

## Implementation Plan
1. Fix StockTransferForm searchProduct method to properly return Product objects as arrays
2. Update dropdown in stock-transfer-form.blade.php to display "mã vật tư - tên vật tư" format
3. Ensure all modules use consistent "vật tư" terminology instead of "sản phẩm"

## Critical Files to Modify
- app/Livewire/Warehouse/StockTransferForm.php: Fix searchProduct method (lines 46-66)
- resources/views/livewire/warehouse/stock-transfer-form.blade.php: Update autocomplete dropdown display
- Multiple other warehouse-related files for consistent terminology

## Verification Steps
1. Refresh Laravel server (port 8000)
2. Open stock transfer form → enter "BST" in search field
3. Confirm dropdown shows "BST001 - Cây bút sơn trắng" format
4. Check inventory module for terminology consistency