<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Dò cột trong file Excel theo tên tiêu đề.
 *
 * Hai nguyên tắc chính:
 *
 * 1. Duyệt theo THỨ TỰ TỪ KHOÁ (từ khoá cụ thể nhất đứng trước), không duyệt
 *    theo thứ tự cột. Tiền tố '=' nghĩa là khớp tuyệt đối cả tên cột, dùng cho
 *    các từ khoá quá ngắn dễ đụng nhau ('ma', 'lo', 'sl', 'kho'...).
 *    Trước đây 'kho' nuốt "Tồn kho" thành Vị trí, 'lo' nuốt "Location" thành
 *    Số lô, 'gia' nuốt "Ngày giao" thành Đơn giá.
 *
 * 2. Dòng tiêu đề được CHẤM ĐIỂM chứ không lấy dòng đầu tiên có chứa chữ giống
 *    mã vật tư. File "Báo cáo tổng hợp Xuất Nhập Tồn" có dòng
 *    "Tìm kiếm ( Theo tên gọi, hoặc mã VT )" đứng trên dòng tiêu đề thật, và
 *    cách dò cũ vớ ngay dòng đó rồi lấy cột số làm mã vật tư.
 *    Cũng hỗ trợ tiêu đề 2 tầng (ô gộp):
 *        dòng A: Mã Vật Tư | Tên vật tư | ĐVT | ... | Tổng hợp khối lượng | Tổng hợp khối lượng |
 *        dòng B:           |            |     | ... | Thực nhập           | Xuất kho            | Tồn kho
 */
trait ExcelColumnMapper
{
    /** Số dòng đầu file được xét làm ứng viên dòng tiêu đề */
    protected int $headerSearchDepth = 15;

    /**
     * Từ khoá nhận diện từng cột. Lớp dùng trait có thể ghi đè để đổi thứ tự ưu
     * tiên — ví dụ phiếu nhập kho ưu tiên cột "Thực nhập" hơn cột "Tồn kho".
     */
    protected function columnKeywords(): array
    {
        return [
            'code'       => ['masanpham', 'masp', 'mavattu', 'mavt', 'mathang', 'mahang', 'mahh', 'productcode', 'itemcode', '=ma', '=code', '=id'],
            'name'       => ['tensanpham', 'tensp', 'tenvattu', 'tenvt', 'tenmathang', 'tenhang', 'tenhh', 'productname', 'itemname', '=ten', '=name', '=hanghoa', '=mota', '=description'],
            'unit'       => ['donvitinh', '=dvt', '=donvi', '=unit', '=uom'],
            'quantity'   => ['soluongton', 'tonkhocuoi', 'tonkho', 'soluong', 'quantity', 'thucnhap', 'thucnhan', 'khoiluong', '=sl', '=ton', '=qty', '=kl'],
            'location'   => ['vitrikho', 'vitri', 'noichua', 'khuvuc', 'khochua', 'location', '=kho', '=ke'],
            'brand'      => ['hangsanxuat', 'hangsx', 'nhasanxuat', 'thuonghieu', 'brand'],
            'batch'      => ['solo', 'lotno', 'batchno', 'batch', 'macodencc', '=lo'],
            'expiry'     => ['hansudung', 'handung', 'ngayhethan', 'expiry', '=hsd'],
            'min_stock'  => ['tontoithieu', 'tonmin', 'minstock', 'dinhmuctoithieu'],
            'unit_price' => ['dongia', 'unitprice', 'price', '=gia', '=giavon'],
        ];
    }

    /**
     * Tìm dòng tiêu đề thật trong các dòng đầu file.
     *
     * @return array|null [
     *     'columns'      => [tên trường => chỉ số cột],
     *     'names'        => [chỉ số cột => tên cột đọc được trong file],
     *     'dataStartRow' => chỉ số dòng dữ liệu đầu tiên,
     * ]
     */
    protected function resolveHeader($rows): ?array
    {
        $total = count($rows);
        $depth = min($this->headerSearchDepth, $total);
        $best  = null;

        for ($i = 0; $i < $depth; $i++) {
            $names = $this->headerNames($rows[$i] ?? []);
            if (empty($names)) {
                continue;
            }

            $columns = $this->mapColumns($names);

            // Phải nhận ra được cột mã vật tư và ít nhất 2 cột thì mới coi là tiêu đề
            if (!isset($columns['code']) || count($columns) < 2) {
                continue;
            }

            $candidate = ['columns' => $columns, 'names' => $names, 'dataStartRow' => $i + 1];
            $score     = count($columns);

            // Thử ghép thêm dòng kế tiếp cho trường hợp tiêu đề 2 tầng (ô gộp)
            if ($i + 1 < $total) {
                $nextNames     = $this->headerNames($rows[$i + 1]);
                $mergedNames   = $this->mergeHeaderNames($names, $nextNames);
                $mergedColumns = $this->mapColumns($mergedNames);

                // Ghép khi tầng dưới làm rõ thêm được cột, hoặc khi tầng dưới rõ
                // ràng là tiêu đề phụ chứ không phải dòng dữ liệu. Điều kiện thứ
                // hai là cần thiết: báo cáo Xuất Nhập Tồn có tầng trên ghi
                // "Tổng hợp khối lượng" cho cả 3 cột, ghép hay không đều nhận ra
                // 4 cột — nhưng chỉ khi ghép mới phân biệt được Thực nhập / Xuất
                // kho / Tồn kho.
                $better = count($mergedColumns) > $score
                    || (count($mergedColumns) === $score && $this->looksLikeHeaderContinuation($nextNames));

                if ($better && isset($mergedColumns['code'])) {
                    $candidate = ['columns' => $mergedColumns, 'names' => $mergedNames, 'dataStartRow' => $i + 2];
                    $score     = count($mergedColumns);
                }
            }

            if ($best === null || $score > $best['score']) {
                $best = $candidate + ['score' => $score];
            }
        }

        return $best;
    }

    /** Lấy tên các cột có nội dung của một dòng: [chỉ số cột => tên] */
    protected function headerNames($row): array
    {
        $names = [];
        foreach ($row as $colIndex => $value) {
            if ($value === null) continue;
            $name = trim((string) $value);
            if ($name !== '') {
                $names[$colIndex] = $name;
            }
        }

        return $names;
    }

    /**
     * Dòng này là tiêu đề phụ (tầng dưới của ô gộp) hay là dòng dữ liệu?
     * Tiêu đề phụ toàn là tên cột nên phần lớn ô khớp từ khoá; dòng dữ liệu chứa
     * mã hàng, tên hàng, số lượng nên hầu như không ô nào khớp.
     */
    protected function looksLikeHeaderContinuation(array $names): bool
    {
        if (count($names) < 2) {
            return false;
        }

        $matched = 0;
        foreach ($names as $name) {
            $slug = Str::slug((string) $name, '');
            if ($slug !== '' && $this->matchesAnyKeyword($slug)) {
                $matched++;
            }
        }

        return $matched >= 2 && $matched * 2 >= count($names);
    }

    /**
     * Những chữ thường gặp trên dòng tiêu đề nhưng không ứng với trường nào cần
     * nhập. Chỉ dùng để nhận ra "dòng này là tiêu đề", không dùng để gán cột.
     */
    protected function extraHeaderLabels(): array
    {
        return [
            'xuatkho', 'nhapkho', 'tonkhodauky', 'tonkhocuoiky', 'dauky', 'cuoiky',
            'nhomvattu', 'thongsokythuat', 'chinhanh', 'kho', 'thanhtien', 'ghichu',
            'stt', 'luyke', 'congdon', 'phatsinh', 'dutru', 'dangchoxuat', 'khadung',
        ];
    }

    /** Tên cột này có khớp từ khoá nào không (bất kể trường nào) */
    protected function matchesAnyKeyword(string $slug): bool
    {
        foreach ($this->columnKeywords() as $keywords) {
            foreach ($keywords as $keyword) {
                if (str_starts_with($keyword, '=')) {
                    if ($slug === substr($keyword, 1)) return true;
                } elseif (str_contains($slug, $keyword)) {
                    return true;
                }
            }
        }

        foreach ($this->extraHeaderLabels() as $label) {
            if (str_contains($slug, $label)) {
                return true;
            }
        }

        return false;
    }

    /** Ghép tiêu đề tầng trên với tầng dưới cho các ô gộp */
    protected function mergeHeaderNames(array $top, array $bottom): array
    {
        $merged = $top;
        foreach ($bottom as $colIndex => $name) {
            $merged[$colIndex] = isset($top[$colIndex]) ? $top[$colIndex] . ' ' . $name : $name;
        }

        return $merged;
    }

    /**
     * Gán mỗi trường vào đúng một cột. Một cột chỉ phục vụ một trường.
     *
     * @return array [tên trường => chỉ số cột]
     */
    protected function mapColumns(array $headerNames): array
    {
        $normalized = [];
        foreach ($headerNames as $colIndex => $name) {
            $slug = Str::slug((string) $name, '');
            if ($slug !== '') {
                $normalized[$colIndex] = $slug;
            }
        }

        $columns = [];
        foreach ($this->columnKeywords() as $field => $keywords) {
            foreach ($keywords as $keyword) {
                $exact  = str_starts_with($keyword, '=');
                $needle = $exact ? substr($keyword, 1) : $keyword;

                foreach ($normalized as $colIndex => $slug) {
                    if (in_array($colIndex, $columns, true)) {
                        continue; // cột này đã dành cho trường khác
                    }

                    if ($exact ? $slug === $needle : str_contains($slug, $needle)) {
                        $columns[$field] = $colIndex;
                        continue 3;
                    }
                }
            }
        }

        return $columns;
    }

    /** Đọc giá trị một trường từ dòng dữ liệu; trả về null nếu ô trống */
    protected function cell($row, array $columns, string $field)
    {
        if (!isset($columns[$field])) {
            return null;
        }

        $value = $row[$columns[$field]] ?? null;
        if ($value === null) {
            return null;
        }

        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : $value;
    }

    /**
     * Chuẩn hoá số lượng: bỏ dấu phân cách nghìn, nhận cả dấu phẩy thập phân,
     * và GIỮ dấu âm (tồn kho âm là dữ liệu có thật trong báo cáo).
     */
    protected function normalizeQuantity($quantity): ?float
    {
        if ($quantity === null) {
            return null;
        }

        if (is_int($quantity) || is_float($quantity)) {
            return (float) $quantity;
        }

        $val = trim((string) $quantity);
        if ($val === '' || $val === '-') {
            return null;
        }

        $negative = str_starts_with($val, '-') || str_starts_with($val, '(');
        $val      = preg_replace('/[^\d.,]/', '', $val);

        if ($val === '') {
            return null;
        }

        if (str_contains($val, ',') && str_contains($val, '.')) {
            if (strrpos($val, ',') > strrpos($val, '.')) {
                $val = str_replace(',', '.', str_replace('.', '', $val));
            } else {
                $val = str_replace(',', '', $val);
            }
        } elseif (str_contains($val, ',')) {
            $parts = explode(',', $val);
            if (count($parts) == 2 && (strlen($parts[1]) == 1 || strlen($parts[1]) == 2)) {
                $val = str_replace(',', '.', $val);
            } else {
                $val = str_replace(',', '', $val);
            }
        }

        $number = floatval($val);

        return $negative ? -$number : $number;
    }

    /** Chuẩn hoá hạn dùng về Y-m-d, chấp nhận cả số serial của Excel */
    protected function normalizeExpiry($expiry): ?string
    {
        if (!$expiry) {
            return null;
        }

        if (is_numeric($expiry)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expiry)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse(str_replace('/', '-', $expiry))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
