<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePlan extends Model
{
    use HasFactory;

    protected static function booted()
    {
        // Ghi lại lịch sử khi vừa tạo mới kế hoạch
        static::created(function ($plan) {
            \App\Models\PurchasePlanHistory::create([
                'purchase_plan_id' => $plan->id,
                'old_status' => null,
                'new_status' => $plan->status,
                'old_quantity' => 0,
                'new_quantity' => $plan->delivered_quantity ?? 0,
                'notes' => trim('Khởi tạo đề xuất ban đầu. ' . $plan->notes),
                'changed_by' => auth()->check() ? auth()->id() : null,
            ]);
        });

        static::updated(function ($plan) {
            $changes = [];
            $notes = '';
            
            if ($plan->isDirty('status')) {
                $changes['old_status'] = $plan->getOriginal('status');
                $changes['new_status'] = $plan->status;
                $notes .= "Trạng thái thay đổi từ {$plan->getOriginal('status')} sang {$plan->status}. ";
            }
            
            if ($plan->isDirty('delivered_quantity')) {
                $changes['old_quantity'] = $plan->getOriginal('delivered_quantity');
                $changes['new_quantity'] = $plan->delivered_quantity;
                $notes .= "Số lượng giao thay đổi từ {$plan->getOriginal('delivered_quantity')} sang {$plan->delivered_quantity}. ";
            }

            if ($plan->isDirty('proposed_quantity')) {
                $notes .= "Số lượng đề xuất thay đổi từ {$plan->getOriginal('proposed_quantity')} sang {$plan->proposed_quantity}. ";
            }
            
            if ($plan->isDirty('is_archived') && $plan->is_archived) {
                $notes .= "Đã chốt sổ (lưu trữ) ngày. ";
            }

            if (!empty($changes) || $plan->isDirty('proposed_quantity') || $plan->isDirty('is_archived')) {
                // Thêm ghi chú gốc của plan nếu có sự thay đổi quan trọng
                if ($plan->isDirty('notes')) {
                    $notes .= " Ghi chú: " . $plan->notes;
                }

                \App\Models\PurchasePlanHistory::create([
                    'purchase_plan_id' => $plan->id,
                    'old_status' => $changes['old_status'] ?? $plan->status,
                    'new_status' => $changes['new_status'] ?? $plan->status,
                    'old_quantity' => $changes['old_quantity'] ?? $plan->delivered_quantity,
                    'new_quantity' => $changes['new_quantity'] ?? $plan->delivered_quantity,
                    'notes' => trim($notes),
                    'changed_by' => auth()->check() ? auth()->id() : null,
                ]);
            }
        });
    }

    protected $fillable = [
        'product_id',
        'proposed_quantity',
        'delivered_quantity',
        'expected_delivery_date',
        'status',
        'urgency',
        'notes',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'proposed_quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
    ];

    /**
     * Relationship: A purchase plan belongs to a product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
