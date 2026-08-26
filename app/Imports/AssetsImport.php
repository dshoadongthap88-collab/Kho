<?php

namespace App\Imports;

use App\Models\Asset;
use App\Traits\ExcelColumnMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithColumnLimit;

/**
 * Nhập danh mục thiết bị từ Excel.
 *
 * Bản cũ dùng WithHeadingRow + WithValidation: tiêu đề bắt buộc nằm ở dòng 1 và
 * phải viết đúng từng chữ ('Mã thiết bị', 'Tên thiết bị'). File có dòng tiêu đề
 * báo cáo phía trên, hoặc viết 'Mã TB', là hỏng cả file — WithValidation ném
 * ngoại lệ nên KHÔNG dòng nào được nhập, mà thông báo lỗi lại bị nuốt.
 *
 * Bản này dùng chung bộ dò cột với danh mục vật tư: chấm điểm tìm dòng tiêu đề
 * thật trong 15 dòng đầu, và nhận nhiều cách viết tên cột.
 */
class AssetsImport implements ToCollection, WithColumnLimit, SkipsEmptyRows
{
    use ExcelColumnMapper;

    /** Thống kê để báo lại cho người dùng sau khi nhập xong */
    public int $rowsRead      = 0; // số dòng dữ liệu đọc được
    public int $skippedNoCode = 0; // dòng bị bỏ vì thiếu mã thiết bị
    public int $duplicateRows = 0; // dòng trùng mã với dòng phía trên trong cùng file
    public int $created       = 0; // thiết bị tạo mới
    public int $updated       = 0; // thiết bị đã có, được cập nhật

    /** Tên các cột đã nhận diện được, để người dùng đối chiếu */
    public array $detectedColumns = [];

    /**
     * Chỉ đọc tới cột AZ — file xuất từ phần mềm kế toán có thể có hơn 1000 cột
     * rỗng phía sau, đọc hết sẽ ngốn hàng trăm MB RAM.
     */
    public function endColumn(): string
    {
        return 'AZ';
    }

    /**
     * Từ khoá riêng cho thiết bị. Trait gốc dò vật tư nên phải ghi đè:
     * 'code' ở đây là MÃ THIẾT BỊ chứ không phải mã vật tư, và mã tài sản là
     * một cột riêng biệt.
     */
    protected function columnKeywords(): array
    {
        return [
            // Mã thiết bị — khoá chính khi nhập
            'code' => [
                'mathietbi', 'mamaymoc', 'mathietbimay', 'mamay', 'matb',
                'equipmentcode', 'machinecode', '=ma', '=code', '=id',
            ],
            'name' => [
                'tenthietbi', 'tenmaymoc', 'tenmay', 'tentb', 'tentaisan',
                'equipmentname', 'machinename', '=ten', '=name', '=thietbi',
            ],
            // Mã tài sản — cột riêng, KHÁC mã thiết bị
            'asset_code' => [
                'mataisan', 'mats', 'sotaisan', 'assetcode', 'assetno',
            ],
            'machine_type' => [
                'loaithietbi', 'loaimaymoc', 'loaimay', 'chungloai', 'nhomthietbi',
                'machinetype', '=loai', '=loaitb',
            ],
            'manager' => [
                'nguoiquanly', 'nguoiphutrach', 'nguoisudung', 'nguoivanhanh',
                'canboquanly', 'manager', '=quanly', '=phutrach',
            ],
            'warranty_status' => [
                'tinhtrangbaohanh', 'trangthaibaohanh', 'tinhtrang', 'trangthai',
                'baohanh', 'status', '=tt',
            ],
            'department' => [
                'bophan', 'phongban', 'donvi', 'donvisudung', 'department', '=bp',
            ],
            'model' => [
                'model', 'kieumay', 'kieuloai', 'dongmay',
            ],
            'serial_number' => [
                'serial', 'serialnumber', 'soserial', 'somay', 'sokhung', '=sn',
            ],
            'manufacturer' => [
                'hangsanxuat', 'hangsx', 'nhasanxuat', 'nuocsanxuat', 'manufacturer',
            ],
            'license_plate' => [
                'biensoxe', 'bienso', 'bienkiemsoat', 'licenseplate', '=bs',
            ],
        ];
    }

    public function collection(Collection $rows)
    {
        $header = $this->resolveHeader($rows);

        if (!$header) {
            throw new \RuntimeException(
                'Không tìm thấy dòng tiêu đề trong file. File cần có một dòng chứa ' .
                'tên cột như "Mã thiết bị" và "Tên thiết bị".'
            );
        }

        $columns = $header['columns'];

        if (!isset($columns['code'])) {
            throw new \RuntimeException(
                'Không tìm thấy cột mã thiết bị. Đã nhận diện được các cột: ' .
                implode(', ', array_map(
                    fn($f) => $f . ' ← "' . ($header['names'][$columns[$f]] ?? '?') . '"',
                    array_keys($columns)
                )) . '.'
            );
        }

        // Ghi lại tên cột đã nhận ra để hiển thị cho người dùng đối chiếu
        foreach ($columns as $field => $index) {
            $this->detectedColumns[$field] = $header['names'][$index] ?? '';
        }

        // Bước 1: đọc file thành mảng, dòng dưới ghi đè dòng trên nếu trùng mã
        $parsed = [];

        foreach ($rows as $i => $row) {
            if ($i < $header['dataStartRow']) {
                continue;
            }

            $code = trim((string) ($row[$columns['code']] ?? ''));

            if ($code === '') {
                $this->skippedNoCode++;
                continue;
            }

            $this->rowsRead++;

            if (isset($parsed[$code])) {
                $this->duplicateRows++;
            }

            $data = [];

            foreach ($columns as $field => $index) {
                if ($field === 'code') {
                    continue;
                }

                $value = trim((string) ($row[$index] ?? ''));

                if ($value !== '') {
                    $data[$field] = $value;
                }
            }

            $parsed[$code] = $data;
        }

        if (empty($parsed)) {
            return;
        }

        // Bước 2: nạp trước các thiết bị đã có, tránh truy vấn trong vòng lặp
        $codes = array_keys($parsed);
        $existing = [];

        foreach (array_chunk($codes, 500) as $chunk) {
            Asset::whereIn('equipment_code', $chunk)
                ->get(['id', 'equipment_code'])
                ->each(function ($asset) use (&$existing) {
                    $existing[$asset->equipment_code] = $asset->id;
                });
        }

        // Bước 3: ghi theo lô trong một giao dịch
        DB::transaction(function () use ($parsed, $existing) {
            foreach (array_chunk($parsed, 200, true) as $chunk) {
                foreach ($chunk as $code => $data) {
                    if (isset($existing[$code])) {
                        // Chỉ cập nhật các cột đọc được từ file, không đụng cột khác
                        if (!empty($data)) {
                            Asset::where('id', $existing[$code])->update($data);
                        }
                        $this->updated++;
                        continue;
                    }

                    $asset = new Asset();
                    $asset->equipment_code = $code;

                    foreach ($data as $field => $value) {
                        $asset->{$field} = $value;
                    }

                    // Giá trị mặc định cho cột bắt buộc mà file không có
                    $asset->name ??= $code;
                    $asset->department ??= 'KHO';
                    $asset->model ??= 'N/A';
                    $asset->warranty_status ??= 'Còn bảo hành';

                    $asset->save();
                    $this->created++;
                }
            }
        });
    }
}
