<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Báo Cáo Ngày</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; line-height: 1.5; padding: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { font-weight: bold; background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 30%; }
        @page { size: auto; margin: 0; }
        @media print {
            .no-print { display: none; }
            body { padding: 15mm; margin: 0; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body onload="initPrint()">
<div id="print-content">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; cursor: pointer;">In Phiếu</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #ccc; cursor: pointer;">Đóng</button>
    </div>

    <div style="margin-bottom: 20px;">
        <div style="font-weight: bold; font-size: 18px; text-transform: uppercase;">PHÒNG KỸ THUẬT SỮA CHỮA</div>
        <div style="font-weight: bold; font-size: 14px;">
            DỰ ÁN: {{ session('current_house', 1) == 2 ? 'HẬU NGHĨA' : (session('current_house', 1) == 3 ? 'CẦN GIỜ' : (session('current_house', 1) == 4 ? 'CẦN GIUỘC' : 'HÓC MÔN')) }}
        </div>
    </div>

    @php
        $reportType = $reportType ?? 'all';
        $showImportSummary = in_array($reportType, ['all', 'import'], true);
        $showExportSummary = in_array($reportType, ['all', 'export'], true);
        $reportTitle = match ($reportType) {
            'import' => 'BÁO CÁO NGÀY - NHẬP KHO',
            'export' => 'BÁO CÁO NGÀY - XUẤT KHO',
            default => 'BÁO CÁO CHI TIẾT GIAO DỊCH',
        };
    @endphp

    <div class="header" style="text-align: center; margin-bottom: 30px;">
        <div class="title" style="font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 10px 0;">{{ $reportTitle }}</div>
        <div class="subtitle">
            @if($dateFrom === $dateTo)
                Ngày báo cáo: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
            @else
                Kỳ báo cáo: Từ {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @endif
        </div>
        <div style="font-style: italic; margin-top: 5px;">Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @if($showImportSummary || $showExportSummary)
        <table>
            <thead>
                <tr>
                    <th colspan="2" class="text-center">TỔNG HỢP GIAO DỊCH VẬT TƯ</th>
                </tr>
            </thead>
            <tbody>
                @if($showImportSummary)
                    <tr>
                        <td width="70%">Tổng số lượng mã vật tư đã NHẬP KHO:</td>
                        <td width="30%" class="text-center font-bold">{{ $reportData['stockInCount'] }}</td>
                    </tr>
                @endif
                @if($showExportSummary)
                    <tr>
                        <td>Tổng số lượng mã vật tư đã XUẤT KHO:</td>
                        <td class="text-center font-bold">{{ $reportData['stockOutCount'] }}</td>
                    </tr>
                @endif
                @if($reportType === 'all')
                    <tr>
                        <td>Tổng số lượng mã vật tư đã CHUYỂN KHO:</td>
                        <td class="text-center font-bold">{{ $reportData['stockTransferCount'] }}</td>
                    </tr>
                    <tr>
                        <td>Tổng số lượng mã vật tư đã THU HỒI:</td>
                        <td class="text-center font-bold">{{ $reportData['stockRecoveryCount'] }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    @if($showExportSummary)
        <table>
            <thead>
                <tr>
                    <th colspan="2" class="text-center">THỐNG KÊ ĐƠN XUẤT KHO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="70%">Tổng số đơn xuất trong ngày:</td>
                    <td width="30%" class="text-center font-bold">{{ $reportData['totalStockOutOrders'] }}</td>
                </tr>
                <tr>
                    <td>Số mã Tài sản xuất cho dự án:</td>
                    <td class="text-center font-bold">{{ $reportData['assetExportCount'] }}</td>
                </tr>
                <tr>
                    <td>Số mã Vật tư xuất cho dự án:</td>
                    <td class="text-center font-bold">{{ $reportData['materialExportCount'] }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($showImportSummary)
        <table>
            <thead>
                <tr>
                    <th colspan="2" class="text-center">THỐNG KÊ NHẬP KHO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="70%">Tổng số đơn nhập kho:</td>
                    <td width="30%" class="text-center font-bold">{{ $reportData['totalStockInOrders'] }}</td>
                </tr>
                <tr>
                    <td>Tổng số mã vật tư nhập kho:</td>
                    <td class="text-center font-bold">{{ $reportData['stockInCount'] }}</td>
                </tr>
                <tr>
                    <td>Tổng số nhà cung cấp đã giao:</td>
                    <td class="text-center font-bold">{{ $reportData['supplierDeliveryCount'] }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if(isset($detailed) && $detailed)
        <div style="page-break-before: always;"></div>

        @if(in_array($reportType, ['all', 'import'], true))
            <div class="header" style="text-align: center; margin-bottom: 20px; margin-top: 20px;">
                <div class="title" style="font-size: 20px; font-weight: bold; text-transform: uppercase;">DANH SÁCH CHI TIẾT NHẬP KHO</div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 4%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">STT</th>
                        <th style="width: 12%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Ngày nhập</th>
                        <th style="width: 12%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Số phiếu</th>
                        <th style="width: 18%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Nhà cung cấp</th>
                        <th style="width: 13%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Mã vật tư</th>
                        <th style="width: 21%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Tên vật tư</th>
                        <th style="width: 9%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Số lượng</th>
                        <th style="width: 11%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Vị trí</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($stockInItems ?? collect()) as $index => $item)
                        <tr>
                            <td class="text-center" style="border: 1px solid black; padding: 5px;">{{ $index + 1 }}</td>
                            <td class="text-center" style="border: 1px solid black; padding: 5px;">{{ optional($item->stockIn?->stock_in_date)->format('d/m/Y') ?: '-' }}</td>
                            <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">{{ $item->stockIn->code ?? '-' }}</td>
                            <td style="border: 1px solid black; padding: 5px;">{{ $item->stockIn->supplier_name ?? '-' }}</td>
                            <td class="text-center" style="border: 1px solid black; padding: 5px;">{{ $item->product->code ?? '-' }}</td>
                            <td style="border: 1px solid black; padding: 5px;">{{ $item->product->name ?? '-' }}</td>
                            <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">{{ (float)$item->quantity }} {{ $item->product->unit ?? '' }}</td>
                            <td style="border: 1px solid black; padding: 5px;">{{ $item->warehouse_location ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 20px; border: 1px solid black;">Không có dữ liệu nhập kho</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        @if(in_array($reportType, ['all', 'export'], true))
            @if($reportType === 'all')
                <div style="page-break-before: always;"></div>
            @endif
            <div class="header" style="text-align: center; margin-bottom: 20px; margin-top: 20px;">
                <div class="title" style="font-size: 20px; font-weight: bold; text-transform: uppercase;">DANH SÁCH CHI TIẾT XUẤT KHO</div>
            </div>

            @php
                $groupedStockOutItems = ($stockOutItems ?? collect())->groupBy(function($item) {
                    return $item->stockOut->document_number ?: ($item->stockOut->code ?? 'KHÁC');
                });
            @endphp

            @forelse($groupedStockOutItems as $docNum => $items)
                @php
                    $stockOut = $items->first()->stockOut;
                    $groupAssetCount = $items->pluck('stockOut.asset_code')->filter()->unique()->count();
                    $groupProductCount = $items->pluck('product_id')->filter()->unique()->count();
                @endphp

                <div style="margin-top: 15px; border: 1px solid #ddd; padding: 10px;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; page-break-after: avoid;">
                        <div style="width: 50%;">Số Phiếu ĐNSC/BD: <span style="font-weight: normal; font-size: 14px;">{{ $docNum !== 'KHÁC' ? $docNum : '..........................................' }}</span></div>
                        <div style="width: 50%;">Nhân viên sửa chữa: <span style="font-weight: normal; font-size: 14px;">{{ $stockOut->repair_staff ?: '..........................................' }}</span></div>
                    </div>
                    <div style="font-size: 11px; font-style: italic; margin-bottom: 10px; color: #555; page-break-after: avoid;">
                        Tổng mã tài sản: {{ $groupAssetCount }} | Tổng mã vật tư: {{ $groupProductCount }} | Số lượng dòng xuất: {{ $items->count() }}
                    </div>

                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th style="width: 5%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">STT</th>
                                <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Mã tài sản</th>
                                <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Mã vật tư</th>
                                <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Số lượng</th>
                                <th style="width: 25%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">BP sử dụng</th>
                                <th style="width: 25%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                <tr>
                                    <td class="text-center" style="border: 1px solid black; padding: 5px;">{{ $index + 1 }}</td>
                                    <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">{{ $item->stockOut->asset_code ?: '-' }}</td>
                                    <td class="text-center" style="border: 1px solid black; padding: 5px;">{{ $item->product->code ?? '-' }}</td>
                                    <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">{{ (float)$item->quantity }} {{ $item->product->unit ?? '' }}</td>
                                    <td style="border: 1px solid black; padding: 5px;">{{ $item->stockOut->department ?: '-' }}</td>
                                    <td style="border: 1px solid black; padding: 5px;">{{ $item->item_note ?? $item->stockOut->note ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <table>
                    <tr>
                        <td class="text-center" style="padding: 20px; border: 1px solid black;">Không có dữ liệu xuất kho</td>
                    </tr>
                </table>
            @endforelse
        @endif
    @endif

    @if(false && isset($detailed) && $detailed && isset($transactions) && $transactions->count() > 0)
        <div style="page-break-before: always;"></div>
        <div class="header" style="text-align: center; margin-bottom: 20px; margin-top: 20px;">
            <div class="title" style="font-size: 20px; font-weight: bold; text-transform: uppercase;">CHI TIẾT CÁC GIAO DỊCH TRONG NGÀY</div>
        </div>
        
        @php
            // Group transactions by document_number
            $groupedTransactions = [];
            foreach($transactions as $tx) {
                $docNum = 'KHÁC';
                $repairStaff = '';
                if ($tx->reference && get_class($tx->reference) === 'App\Models\StockOut') {
                    $docNum = $tx->reference->document_number ?: 'KHÁC';
                    $repairStaff = $tx->reference->repair_staff ?: '';
                }
                
                if (!isset($groupedTransactions[$docNum])) {
                    $groupedTransactions[$docNum] = [
                        'repairStaff' => $repairStaff,
                        'items' => collect()
                    ];
                }
                
                $groupedTransactions[$docNum]['items']->push($tx);
            }
        @endphp

        @foreach($groupedTransactions as $docNum => $group)
            @php
                $groupAssetCount = $group['items']->filter(function($tx) {
                    return $tx->reference && isset($tx->reference->asset_code) && !empty($tx->reference->asset_code);
                })->pluck('reference.asset_code')->unique()->count();

                $groupProductCount = $group['items']->filter(function($tx) {
                    return $tx->product_id;
                })->pluck('product_id')->unique()->count();
            @endphp

            <div style="margin-top: 15px; border: 1px solid #ddd; padding: 10px;">
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; page-break-after: avoid;">
                    <div style="width: 50%;">Số Phiếu ĐNSC/BD: <span style="font-weight: normal; font-size: 14px;">{{ $docNum !== 'KHÁC' ? $docNum : '..........................................' }}</span></div>
                    <div style="width: 50%;">Nhân viên sửa chữa: <span style="font-weight: normal; font-size: 14px;">{{ $group['repairStaff'] ?: '..........................................' }}</span></div>
                </div>
                <div style="font-size: 11px; font-style: italic; margin-bottom: 10px; color: #555; page-break-after: avoid;">
                    Tổng mã tài sản: {{ $groupAssetCount }} | Tổng mã vật tư: {{ $groupProductCount }} | Số lượng giao dịch: {{ $group['items']->count() }}
                </div>

                <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px;">
                    <thead>
                        <tr>
                            <th style="width: 5%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">STT</th>
                            <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">MÃ TÀI SẢN</th>
                            <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">MÃ VẬT TƯ</th>
                            <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">SỐ LƯỢNG</th>
                            <th style="width: 25%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">BP SỬ DỤNG</th>
                            <th style="width: 25%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">GHI CHÚ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($group['items'] as $index => $tx)
                            <tr>
                                <td class="text-center" style="border: 1px solid black; padding: 5px;">{{ $index + 1 }}</td>
                                <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">
                                    {{ ($tx->reference && isset($tx->reference->asset_code)) ? $tx->reference->asset_code : '-' }}
                                </td>
                                <td class="text-center" style="border: 1px solid black; padding: 5px;">
                                    {{ $tx->product->code ?? '-' }}
                                </td>
                                <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">
                                    {{ (float)$tx->quantity }} {{ $tx->product->unit ?? '' }}
                                </td>
                                <td style="border: 1px solid black; padding: 5px;">
                                    {{ ($tx->reference && isset($tx->reference->department)) ? $tx->reference->department : '-' }}
                                </td>
                                <td style="border: 1px solid black; padding: 5px;">
                                    {{ $tx->item_note ?? $tx->note ?? '' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 20px; border: 1px solid black;">Không có dữ liệu hiển thị</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <div class="signature">
            <strong>Người lập báo cáo</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
            <strong>{{ Auth::user()->name ?? 'Chưa xác định' }}</strong>
        </div>
        <div class="signature">
            <strong>Trưởng bộ phận</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
        </div>
    </div>
</div>
    
    <script>
        function initPrint() {
            const urlParams = new URLSearchParams(window.location.search);
            const isZalo = urlParams.get('zalo') === '1';
            
            if (isZalo) {
                // Hide buttons before PDF generation
                document.querySelector('.no-print').style.display = 'none';
                
                setTimeout(() => {
                    var element = document.getElementById('print-content');
                    var opt = {
                      margin:       [15, 15, 15, 15],
                      filename:     'Bao_Cao_Tu_{{ $dateFrom }}_Den_{{ $dateTo }}.pdf',
                      image:        { type: 'jpeg', quality: 0.98 },
                      html2canvas:  { scale: 2, useCORS: true, logging: false },
                      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                    };
                    
                    try {
                        html2pdf().set(opt).from(element).save().then(() => {
                            alert('Đã tải xuống file PDF Báo Cáo. \n\nHệ thống sẽ mở Zalo Desktop. Bạn hãy KÉO THẢ file PDF vừa tải vào đoạn chat để gửi nhé!');
                            window.location.href = 'zalo://';
                            setTimeout(() => { window.close(); }, 3000);
                        }).catch(err => {
                            console.error('Lỗi tạo PDF:', err);
                            alert('Lỗi tạo PDF. Vui lòng tải lại trang.');
                        });
                    } catch (e) {
                        console.error('Lỗi html2pdf:', e);
                        alert('Không thể tạo PDF. Trình duyệt của bạn có thể đang chặn script.');
                    }
                }, 800);
            } else {
                window.print();
            }
        }
    </script>
</body>
</html>
