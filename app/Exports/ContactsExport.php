<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Lọc theo house + chỉ lấy các cột cần thiết để tránh load thừa
        return Supplier::select('id','name','address','phone','contact_person','email','type','department','status','created_at')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Mã Đối tác',
            'Tên Khách hàng/NCC',
            'Địa chỉ',
            'Số điện thoại',
            'Người liên hệ',
            'Email',
            'Phân loại',
            'Bộ phận',
            'Tình trạng',
            'Ngày tạo'
        ];
    }

    public function map($contact): array
    {
        $type = match($contact->type) {
            'customer' => 'Khách hàng',
            'supplier' => 'Nhà cung cấp',
            'internal' => 'Nội bộ',
            default => 'Cả hai',
        };

        $status = $contact->status === 'active' ? 'Hoạt động' : 'Ngừng hoạt động';

        return [
            $contact->id,
            $contact->name,
            $contact->address,
            $contact->phone,
            $contact->contact_person,
            $contact->email,
            $type,
            $contact->department,
            $status,
            $contact->created_at ? $contact->created_at->format('d/m/Y H:i') : '',
        ];
    }
}
