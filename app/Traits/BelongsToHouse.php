<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToHouse
{
    /**
     * Các bảng dữ liệu THAM CHIẾU dùng chung mọi dự án.
     *
     * Danh mục nhóm và nhà cung cấp không phải chứng từ của riêng dự án nào —
     * gán chúng về một dự án thì các dự án khác mất sạch danh sách để chọn.
     * Với những bảng này, bản ghi có house_id = NULL nghĩa là "dùng chung",
     * và vẫn đọc được từ mọi dự án. Bản ghi có house_id thì chỉ dự án đó thấy.
     */
    protected static array $bangDungChung = ['categories', 'suppliers'];

    public static function bootBelongsToHouse()
    {
        static::addGlobalScope('house', function (Builder $builder) {
            $houseId = session('current_house') ?? (auth()->user()?->current_house_id);

            if (!$houseId) {
                return;
            }

            $table = $builder->getModel()->getTable();

            if (in_array($table, static::$bangDungChung, true)) {
                // Của dự án này HOẶC dùng chung
                $builder->where(function ($q) use ($table, $houseId) {
                    $q->where($table . '.house_id', $houseId)
                      ->orWhereNull($table . '.house_id');
                });

                return;
            }

            $builder->where($table . '.house_id', $houseId);
        });

        static::creating(function ($model) {
            $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
            if ($houseId && !$model->house_id) {
                $model->house_id = $houseId;
            }
        });
    }

    public function house()
    {
        return $this->belongsTo(\App\Models\House::class);
    }
}
