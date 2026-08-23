<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Dò cột trong file Excel theo tên tiêu đề.
 *
 * Quy tắc: duyệt theo THỨ TỰ TỪ KHOÁ (từ khoá cụ thể nhất đứng trước), không
 * duyệt theo thứ tự cột. Khi một cột đã khớp thì trả về giá trị của đúng cột đó
 * — kể cả khi ô đang trống — để không bị "rớt" sang cột kế bên và lấy nhầm dữ
 * liệu (vd: ô Vị trí trống thì trước đây bị lấy nhầm số ở cột Tồn kho).
 *
 * Tiền tố '=' nghĩa là khớp tuyệt đối cả tên cột, dùng cho các từ khoá quá ngắn
 * dễ đụng nhau ('ma', 'lo', 'sl', 'kho'...).
 */
trait ExcelColumnMapper
{
    private function findValue($row, array $keywords)
    {
        foreach ($keywords as $keyword) {
            $exact  = str_starts_with($keyword, '=');
            $needle = $exact ? substr($keyword, 1) : $keyword;

            foreach ($row as $key => $value) {
                // Str::slug bỏ dấu tiếng Việt + viết thường (vd: "Mã SP" -> "masp")
                $normalizedKey = Str::slug((string) $key, '');
                if ($normalizedKey === '') continue;

                $matched = $exact
                    ? $normalizedKey === $needle
                    : str_contains($normalizedKey, $needle);

                if ($matched) {
                    return ($value === null || $value === '') ? null : $value;
                }
            }
        }

        return null;
    }

    /** Cột Mã vật tư */
    private function findCode($row)
    {
        return $this->findValue($row, [
            'masanpham', 'masp', 'mavattu', 'mavt', 'mathang', 'mahang', 'mahh',
            'productcode', 'itemcode', '=ma', '=code', '=id',
        ]);
    }

    /** Cột Tên vật tư */
    private function findName($row)
    {
        return $this->findValue($row, [
            'tensanpham', 'tensp', 'tenvattu', 'tenvt', 'tenmathang', 'tenhang', 'tenhh',
            'productname', 'itemname', '=ten', '=name', '=hanghoa', '=mota', '=description',
        ]);
    }

    /** Cột Đơn vị tính */
    private function findUnit($row)
    {
        return $this->findValue($row, ['donvitinh', '=dvt', '=donvi', '=unit', '=uom']);
    }

    /**
     * Cột Vị trí / Nơi chứa.
     * KHÔNG dùng từ khoá lỏng 'kho' vì nó khớp luôn "Tồn kho", "Số lượng tồn kho"
     * và làm cột Vị trí hiển thị ra con số tồn.
     */
    private function findLocation($row)
    {
        return $this->findValue($row, [
            'vitrikho', 'vitri', 'noichua', 'khuvuc', 'khochua', 'location', '=kho', '=ke',
        ]);
    }

    /** Cột Số lượng / Tồn kho */
    private function findQuantity($row)
    {
        return $this->findValue($row, [
            'soluongton', 'soluong', 'tonkho', 'quantity', 'thucnhan',
            '=sl', '=ton', '=qty', '=slton',
        ]);
    }

    /** Cột Hãng sản xuất */
    private function findBrand($row)
    {
        return $this->findValue($row, ['hangsanxuat', 'hangsx', 'nhasanxuat', 'thuonghieu', 'brand']);
    }

    /** Cột Số lô */
    private function findBatch($row)
    {
        return $this->findValue($row, ['solo', 'lotno', 'batchno', 'batch', '=lo']);
    }

    /** Cột Hạn sử dụng */
    private function findExpiry($row)
    {
        return $this->findValue($row, ['hansudung', 'handung', 'ngayhethan', 'expiry', '=hsd']);
    }

    /** Cột Tồn tối thiểu */
    private function findMinStock($row)
    {
        return $this->findValue($row, ['tontoithieu', 'tonmin', 'minstock', 'dinhmuctoithieu']);
    }
}
