<?php

namespace App\Services;

use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Google\Cloud\DocumentAI\V1\RawDocument;

class GoogleDocumentAIService
{
    protected $projectId;
    protected $location;
    protected $processorId;

    public function __construct()
    {
        $this->projectId = env('GOOGLE_DOCUMENT_AI_PROJECT_ID');
        $this->location = env('GOOGLE_DOCUMENT_AI_LOCATION', 'us');
        $this->processorId = env('GOOGLE_DOCUMENT_AI_PROCESSOR_ID');
    }

    /**
     * Process a PDF document using Google Document AI
     *
     * @param string $filePath Absolute path to the PDF file
     * @return array Array of parsed rows (code, name, unit, quantity)
     */
    public function processPdf($filePath)
    {
        if (!$this->projectId || !$this->processorId) {
            // Trả về dữ liệu giả lập (Mock Data) để test tính năng CRUD lưới khi chưa có API Key
            return [
                [
                    'code' => 'VAP02348',
                    'name' => 'Mỡ bôi trơn Licas Grease No2 EP2',
                    'unit' => 'Kg',
                    'quantity' => 3400,
                    'scanned_name' => 'Mỡ bôi trơn Licas Grease No2 EP2'
                ],
                [
                    'code' => 'VAP2179',
                    'name' => 'Dầu hộp số Total Dynatrans ACX 30',
                    'unit' => 'LIT',
                    'quantity' => 416,
                    'scanned_name' => 'Dầu hộp số Total Dynatrans ACX 30'
                ]
            ];
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        // Initialize the client
        $client = new DocumentProcessorServiceClient();

        try {
            // Read the file
            $fileContent = file_get_contents($filePath);

            // Set the document format
            $rawDocument = new RawDocument([
                'content' => $fileContent,
                'mime_type' => 'application/pdf',
            ]);

            // Define the resource name
            $name = $client->processorName($this->projectId, $this->location, $this->processorId);

            // Create the process request
            $request = new ProcessRequest([
                'name' => $name,
                'raw_document' => $rawDocument,
            ]);

            // Call the Document AI API
            $response = $client->processDocument($request);
            $document = $response->getDocument();

            if (!$document) {
                throw new \Exception('No document returned from Document AI.');
            }

            return $this->extractStockInRows($document);
        } finally {
            $client->close();
        }
    }

    /**
     * Parse the Document AI response to extract our specific 4 columns
     */
    protected function extractStockInRows($document)
    {
        $text = $document->getText();
        $pages = $document->getPages();
        $parsedRows = [];

        foreach ($pages as $page) {
            $tables = $page->getTables();
            
            foreach ($tables as $table) {
                $headerRows = $table->getHeaderRows();
                $bodyRows = $table->getBodyRows();
                
                // Identify columns based on headers
                $colMapping = [
                    'code' => -1,
                    'name' => -1,
                    'unit' => -1,
                    'quantity' => -1,
                ];

                if (count($headerRows) > 0) {
                    $headerCells = $headerRows[0]->getCells();
                    foreach ($headerCells as $index => $cell) {
                        $cellText = strtolower($this->getTextFromLayout($cell->getLayout(), $text));
                        $cellText = str_replace(' ', '', $this->removeVietnameseAccents($cellText));
                        
                        if (str_contains($cellText, 'mahang') || str_contains($cellText, 'mavattu') || str_contains($cellText, 'masp') || str_contains($cellText, 'mavt') || str_contains($cellText, 'mahh')) {
                            $colMapping['code'] = $index;
                        } elseif (str_contains($cellText, 'tenhang') || str_contains($cellText, 'tenvattu') || str_contains($cellText, 'tensp') || str_contains($cellText, 'tenvt') || str_contains($cellText, 'tenhh')) {
                            $colMapping['name'] = $index;
                        } elseif (str_contains($cellText, 'dvt') || str_contains($cellText, 'donvitinh') || str_contains($cellText, 'donvi')) {
                            $colMapping['unit'] = $index;
                        } elseif (str_contains($cellText, 'soluongnhan') || str_contains($cellText, 'slnhan') || str_contains($cellText, 'soluong') || str_contains($cellText, 'thucnhan') || str_contains($cellText, 'thucnhap')) {
                            if ($colMapping['quantity'] === -1 || str_contains($cellText, 'nhan') || str_contains($cellText, 'nhap')) {
                                $colMapping['quantity'] = $index;
                            }
                        }
                    }
                }
                
                // Fallback if headers aren't detected properly
                if ($colMapping['code'] === -1) $colMapping['code'] = 0;
                if ($colMapping['name'] === -1) $colMapping['name'] = 1;
                if ($colMapping['unit'] === -1) $colMapping['unit'] = 2;
                if ($colMapping['quantity'] === -1) $colMapping['quantity'] = 3;

                // Process body rows
                foreach ($bodyRows as $row) {
                    $cells = $row->getCells();
                    
                    $code = $colMapping['code'] !== -1 && isset($cells[$colMapping['code']]) 
                        ? $this->getTextFromLayout($cells[$colMapping['code']]->getLayout(), $text) 
                        : '';
                        
                    $name = $colMapping['name'] !== -1 && isset($cells[$colMapping['name']]) 
                        ? $this->getTextFromLayout($cells[$colMapping['name']]->getLayout(), $text) 
                        : '';
                        
                    $unit = $colMapping['unit'] !== -1 && isset($cells[$colMapping['unit']]) 
                        ? $this->getTextFromLayout($cells[$colMapping['unit']]->getLayout(), $text) 
                        : '';
                        
                    $qtyRaw = $colMapping['quantity'] !== -1 && isset($cells[$colMapping['quantity']]) 
                        ? $this->getTextFromLayout($cells[$colMapping['quantity']]->getLayout(), $text) 
                        : '';

                    $code = trim(preg_replace('/\s+/', ' ', $code));
                    $name = trim(preg_replace('/\s+/', ' ', $name));
                    $unit = trim(preg_replace('/\s+/', ' ', $unit));
                    
                    // Break at 'Tổng cộng'
                    if (str_contains(strtolower($code), 'tổng') || str_contains(strtolower($name), 'tổng') || str_contains(strtolower($code), 'cộng') || str_contains(strtolower($name), 'cộng')) {
                        break 2;
                    }

                    // Parse quantity
                    $qtyVal = '';
                    if (preg_match('/\b\d+([.,]\d+)?\b/', $qtyRaw, $matches)) {
                        $rawNumber = $matches[0];
                        if (str_contains($rawNumber, ',') && str_contains($rawNumber, '.')) {
                            $lastDot = strrpos($rawNumber, '.');
                            $lastComma = strrpos($rawNumber, ',');
                            if ($lastComma > $lastDot) {
                                $rawNumber = str_replace('.', '', $rawNumber);
                                $rawNumber = str_replace(',', '.', $rawNumber);
                            } else {
                                $rawNumber = str_replace(',', '', $rawNumber);
                            }
                        } elseif (str_contains($rawNumber, ',')) {
                            $parts = explode(',', $rawNumber);
                            if (strlen(end($parts)) === 3) {
                                $rawNumber = str_replace(',', '', $rawNumber);
                            } else {
                                $rawNumber = str_replace(',', '.', $rawNumber);
                            }
                        }
                        $qtyVal = is_numeric($rawNumber) ? floatval($rawNumber) : '';
                    }

                    // Strict VAP code validation
                    if (preg_match('/\b(VAP[A-Z0-9]*|[A-Z0-9]{4,})\b/i', $code, $m)) {
                        $code = strtoupper($m[1]);
                    } else {
                        $code = '';
                    }

                    if (!empty($code) && !empty($qtyVal)) {
                        $parsedRows[] = [
                            'code' => $code,
                            'name' => $name,
                            'unit' => $unit,
                            'quantity' => $qtyVal,
                            'scanned_name' => $name
                        ];
                    }
                }
            }
        }

        return $parsedRows;
    }

    protected function getTextFromLayout($layout, $text)
    {
        if (!$layout || !$layout->getTextAnchor()) {
            return '';
        }

        $textSegments = $layout->getTextAnchor()->getTextSegments();
        $cellText = '';
        foreach ($textSegments as $segment) {
            $startIndex = (int) $segment->getStartIndex();
            $endIndex = (int) $segment->getEndIndex();
            
            $cellText .= mb_substr($text, $startIndex, $endIndex - $startIndex);
        }
        return trim($cellText);
    }

    protected function removeVietnameseAccents($str)
    {
        $str = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/i', 'a', $str);
        $str = preg_replace('/[éèẻẽẹêếềểễệ]/i', 'e', $str);
        $str = preg_replace('/[íìỉĩị]/i', 'i', $str);
        $str = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/i', 'o', $str);
        $str = preg_replace('/[úùủũụưứừửữự]/i', 'u', $str);
        $str = preg_replace('/[ýỳỷỹỵ]/i', 'y', $str);
        $str = preg_replace('/[đ]/i', 'd', $str);
        return $str;
    }
}
