<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithColumnLimit;

/**
 * Đối tượng rỗng dùng cho Excel::toArray() khi chỉ cần đọc file ra mảng.
 *
 * Tồn tại để khai báo giới hạn cột: file báo cáo xuất từ phần mềm kế toán có
 * hơn 1000 cột rỗng phía sau, đọc hết ngốn ~870MB RAM và làm chết tiến trình
 * PHP. Giới hạn tới cột AZ đưa mức tiêu thụ về khoảng 80MB.
 */
class SheetReader implements WithColumnLimit, SkipsEmptyRows
{
    public function endColumn(): string
    {
        return 'AZ';
    }
}
