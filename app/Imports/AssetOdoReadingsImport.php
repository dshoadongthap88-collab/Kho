<?php

namespace App\Imports;

use App\Models\AssetOdoReading;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Str;

class AssetOdoReadingsImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function model(array $row)
    {
        // Mapping columns: mã tài sản, số giờ, ngày đọc, người vận hành, tình trạng, ghi chú
        $productCode = trim($row['ma_tai_san'] ?? $row['ma'] ?? $row['code'] ?? '');
        $product = Product::where('code', $productCode)
            ->whereIn('type', ['product_produced', 'product_purchased'])
            ->first();

        if (!$product) {
            // Skip nếu không tìm thấy tài sản
            return null;
        }

        $readingDate = $this->parseDate($row['ngay_doc'] ?? $row['ngay'] ?? $row['reading_date'] ?? now()->format('Y-m-d'));
        $currentHours = (float)($row['so_gio'] ?? $row['gio'] ?? $row['current_hours'] ?? 0);
        $operator = trim($row['nguon_van_hanh'] ?? $row['operator'] ?? $row['van_hanh'] ?? '');
        $status = $this->normalizeStatus($row['tinh_trang'] ?? $row['status'] ?? 'normal');
        $notes = trim($row['ghi_chu'] ?? $row['notes'] ?? '');

        // Kiểm tra đã có reading cho ngày này chưa
        $existing = AssetOdoReading::where('product_id', $product->id)
            ->where('reading_date', $readingDate)
            ->first();

        if ($existing) {
            // Cập nhật bản ghi hiện có
            $existing->update([
                'current_hours' => $currentHours,
                'operator' => $operator,
                'status' => $status,
                'notes' => $notes,
            ]);
            return null;
        }

        return new AssetOdoReading([
            'product_id' => $product->id,
            'reading_date' => $readingDate,
            'current_hours' => $currentHours,
            'operator' => $operator,
            'status' => $status,
            'notes' => $notes,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.ma_tai_san' => ['required', 'exists:products,code'],
            '*.so_gio' => ['required', 'numeric', 'min:0'],
            '*.ngay_doc' => ['required', 'date'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.ma_tai_san.required' => 'Mã tài sản là bắt buộc',
            '*.ma_tai_san.exists' => 'Mã tài sản không tồn tại trong hệ thống',
            '*.so_gio.required' => 'Số giờ là bắt buộc',
            '*.so_gio.numeric' => 'Số giờ phải là số',
            '*.ngay_doc.required' => 'Ngày đọc là bắt buộc',
            '*.ngay_doc.date' => 'Ngày đọc không hợp lệ',
        ];
    }

    private function parseDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        // Thử các định dạng ngày thường gặp
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) == $value) {
                return $date->format('Y-m-d');
            }
        }

        return now()->format('Y-m-d');
    }

    private function normalizeStatus($status)
    {
        $status = strtolower(trim($status));
        $map = [
            'maintenance_required' => 'maintenance_required',
            'can bao duong' => 'maintenance_required',
            'cần bảo dưỡng' => 'maintenance_required',
            'maintenance_done' => 'maintenance_done',
            'da bao duong' => 'maintenance_done',
            'đã bảo dưỡng' => 'maintenance_done',
            'normal' => 'normal',
            'binh thuong' => 'normal',
            'bình thường' => 'normal',
        ];

        return $map[$status] ?? 'normal';
    }
}
