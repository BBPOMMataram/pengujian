<?php

namespace App\Imports;

use App\Models\PengujianItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class PengujianImport implements ToCollection
{
    protected string $batchId;
    protected string $filename;

    public function __construct(string $batchId, string $filename)
    {
        $this->batchId = $batchId;
        $this->filename = $filename;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Clean rows by converting collections/arrays and trimming
        $cleanedRows = [];
        foreach ($rows as $r) {
            $rowArray = is_array($r) ? $r : $r->toArray();
            // Check if entire row is empty
            $nonEmpty = array_filter($rowArray, fn($v) => !is_null($v) && trim((string)$v) !== '');
            if (!empty($nonEmpty)) {
                $cleanedRows[] = array_values($rowArray);
            }
        }

        if (empty($cleanedRows)) {
            return;
        }

        // Check if row 0 is a header row
        $firstRow = $cleanedRows[0];
        $isHeader = $this->detectHeaderRow($firstRow);

        $headers = [];
        $dataRows = [];

        if ($isHeader) {
            foreach ($firstRow as $idx => $colName) {
                $trimmed = trim((string)$colName);
                if ($trimmed === '') {
                    $headers[$idx] = 'QR Code';
                } else {
                    $headers[$idx] = $trimmed;
                }
            }
            $dataRows = array_slice($cleanedRows, 1);
        } else {
            // Generate friendly column names
            $maxCols = max(array_map('count', $cleanedRows));
            for ($i = 0; $i < $maxCols; $i++) {
                if ($i === 0) {
                    $headers[$i] = 'Informasi Data';
                } elseif ($i === 1) {
                    $headers[$i] = 'QR Code';
                } else {
                    $headers[$i] = 'Kolom ' . ($i + 1);
                }
            }
            $dataRows = $cleanedRows;
        }

        $rowNum = 1;
        foreach ($dataRows as $row) {
            $mappedData = [];
            $dataColumnValue = null;
            $code = null;
            $name = null;

            foreach ($headers as $colIdx => $headerName) {
                $val = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : '';
                
                // If this is the QR code column (or empty 2nd column)
                $isQrCol = str_contains(strtolower($headerName), 'qr');
                if ($isQrCol) {
                    $mappedData[$headerName] = '(Otomatis Di-generate)';
                    continue;
                }

                $mappedData[$headerName] = $val;

                if ($dataColumnValue === null && $val !== '') {
                    $dataColumnValue = $val;
                }

                // Check for explicit code or name
                $hLower = strtolower($headerName);
                if (!$code && (str_contains($hLower, 'kode') || str_contains($hLower, 'code') || str_contains($hLower, 'no') || str_contains($hLower, 'id'))) {
                    $code = $val;
                }
                if (!$name && (str_contains($hLower, 'nama') || str_contains($hLower, 'sampel') || str_contains($hLower, 'produk') || str_contains($hLower, 'sample'))) {
                    $name = $val;
                }
            }

            // Fallback for code and name
            if (!$code) {
                $code = $dataColumnValue ?: ('SMP-' . str_pad((string)$rowNum, 4, '0', STR_PAD_LEFT));
            }
            if (!$name) {
                $name = $dataColumnValue ?: 'Sampel #' . $rowNum;
            }

            // The QR code contains the information from the adjacent column
            $qrContent = $dataColumnValue ?: $code;

            PengujianItem::create([
                'batch_id' => $this->batchId,
                'filename' => $this->filename,
                'row_number' => $rowNum++,
                'code' => $code,
                'name' => $name,
                'qr_token' => Str::random(32),
                'qr_content' => $qrContent,
                'data' => $mappedData,
            ]);
        }
    }

    private function detectHeaderRow(array $row): bool
    {
        $headerKeywords = ['kode', 'code', 'nama', 'name', 'sampel', 'sample', 'data', 'informasi', 'info', 'no', 'nomor', 'qr', 'hasil', 'parameter', 'kategori', 'analis', 'tanggal'];
        foreach ($row as $cell) {
            $c = strtolower(trim((string)$cell));
            foreach ($headerKeywords as $kw) {
                if ($c === $kw || str_contains($c, $kw)) {
                    return true;
                }
            }
        }
        return false;
    }
}
