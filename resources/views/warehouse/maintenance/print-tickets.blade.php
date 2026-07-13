<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Phiếu Bảo Dưỡng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background-color: white;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            .page-break:last-child {
                page-break-after: auto;
            }
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-4 no-print flex justify-between items-center bg-white p-4 rounded shadow">
            <h1 class="text-xl font-bold text-gray-800">In {{ $tickets->count() }} Phiếu Bảo Dưỡng</h1>
            <div class="flex gap-2">
                <button onclick="window.history.back()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">Quay lại</button>
                <button onclick="window.print()" class="px-4 py-2 bg-sky-600 text-white font-bold rounded shadow hover:bg-sky-700 transition">🖨️ In Ngay</button>
            </div>
        </div>

        @foreach($tickets as $ticket)
            @php
                // Tìm BOM tương ứng với cấp bảo dưỡng
                $cycleNum = (int) filter_var($ticket->maintenance_rule_id, FILTER_SANITIZE_NUMBER_INT);
                $bom = null;
                if ($cycleNum > 0) {
                    $bom = \App\Models\MaintenanceBom::with('items.product')->where('asset_id', $ticket->asset_id)->where('cycle', $cycleNum)->first();
                }
            @endphp
            <div class="bg-white p-10 shadow-sm border border-gray-200 rounded-lg mb-8 page-break">
                <!-- Header -->
                <div class="flex justify-between items-start border-b-2 border-slate-800 pb-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-black uppercase text-slate-800">Phiếu Yêu Cầu Bảo Dưỡng</h2>
                        <p class="text-sm text-slate-500 mt-1">Cấp bảo dưỡng định kỳ (BOM)</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-sky-800">Số: {{ $ticket->ticket_code }}</div>
                        <div class="text-sm">Ngày in: {{ now()->format('d/m/Y') }}</div>
                    </div>
                </div>

                <!-- Info -->
                <div class="grid grid-cols-2 gap-6 mb-8 text-sm">
                    <div>
                        <table class="w-full">
                            <tr><td class="py-1 font-bold w-1/3">Thiết bị:</td><td class="py-1 uppercase text-sky-800 font-bold">{{ $ticket->asset->name ?? 'N/A' }}</td></tr>
                            <tr><td class="py-1 font-bold">Mã tài sản:</td><td class="py-1 font-mono font-medium">{{ $ticket->asset->asset_code ?? 'N/A' }}</td></tr>
                            <tr><td class="py-1 font-bold">Tài xế/Vận hành:</td><td class="py-1">{{ $ticket->staff_name ?? '.........................................' }}</td></tr>
                        </table>
                    </div>
                    <div>
                        <table class="w-full">
                            <tr><td class="py-1 font-bold w-1/3">Ngày bảo dưỡng:</td><td class="py-1">{{ $ticket->maintenance_date ? \Carbon\Carbon::parse($ticket->maintenance_date)->format('d/m/Y') : '.....................' }}</td></tr>
                            <tr><td class="py-1 font-bold">Cấp bảo dưỡng:</td><td class="py-1 font-bold text-red-600">{{ $ticket->maintenance_rule_id ?? 'N/A' }}</td></tr>
                            <tr><td class="py-1 font-bold">Giờ máy (ODO):</td><td class="py-1 font-bold">{{ number_format($ticket->maintenance_odo) }}</td></tr>
                        </table>
                    </div>
                </div>

                <!-- Materials Table -->
                <div class="mb-8">
                    <h3 class="text-base font-bold mb-3 uppercase text-slate-700">1. Danh sách vật tư xuất kho</h3>
                    <table class="w-full border-collapse border border-slate-300 text-sm">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-center w-10">STT</th>
                                <th class="border border-slate-300 px-3 py-2 text-left">Tên vật tư / Phụ tùng</th>
                                <th class="border border-slate-300 px-3 py-2 text-center w-24">ĐVT</th>
                                <th class="border border-slate-300 px-3 py-2 text-center w-24">SL Định mức</th>
                                <th class="border border-slate-300 px-3 py-2 text-center w-24">SL Thực tế xuất</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($bom && $bom->items->count() > 0)
                                @foreach($bom->items as $i => $item)
                                    <tr>
                                        <td class="border border-slate-300 px-3 py-2 text-center">{{ $i + 1 }}</td>
                                        <td class="border border-slate-300 px-3 py-2 font-medium">{{ $item->product->name ?? 'N/A' }}</td>
                                        <td class="border border-slate-300 px-3 py-2 text-center">{{ $item->product->unit ?? 'Cái' }}</td>
                                        <td class="border border-slate-300 px-3 py-2 text-center font-bold">{{ number_format($item->quantity, 1) }}</td>
                                        <td class="border border-slate-300 px-3 py-2 text-center text-slate-300">......</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="border border-slate-300 px-3 py-8 text-center text-slate-500 italic">Không tìm thấy dữ liệu định mức BOM cho mốc này hoặc chưa cấu hình.</td>
                                </tr>
                            @endif
                            <!-- Trống vài dòng để ghi thêm -->
                            @for($j = 1; $j <= 3; $j++)
                                <tr>
                                    <td class="border border-slate-300 px-3 py-3"></td>
                                    <td class="border border-slate-300 px-3 py-3"></td>
                                    <td class="border border-slate-300 px-3 py-3"></td>
                                    <td class="border border-slate-300 px-3 py-3"></td>
                                    <td class="border border-slate-300 px-3 py-3"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Maintenance Tasks -->
                <div class="mb-12">
                    <h3 class="text-base font-bold mb-3 uppercase text-slate-700">2. Nội dung công việc thực hiện (Ghi chú thêm)</h3>
                    <div class="w-full border border-slate-300 h-24 rounded"></div>
                </div>

                <!-- Signatures -->
                <div class="grid grid-cols-4 text-center text-sm mt-8">
                    <div>
                        <p class="font-bold">Người lập phiếu</p>
                        <p class="italic text-slate-500 text-xs mt-1">(Ký, ghi rõ họ tên)</p>
                    </div>
                    <div>
                        <p class="font-bold">Thủ kho</p>
                        <p class="italic text-slate-500 text-xs mt-1">(Ký, ghi rõ họ tên)</p>
                    </div>
                    <div>
                        <p class="font-bold">Thợ bảo dưỡng</p>
                        <p class="italic text-slate-500 text-xs mt-1">(Ký, ghi rõ họ tên)</p>
                    </div>
                    <div>
                        <p class="font-bold">Quản lý / Giám sát</p>
                        <p class="italic text-slate-500 text-xs mt-1">(Ký, ghi rõ họ tên)</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
