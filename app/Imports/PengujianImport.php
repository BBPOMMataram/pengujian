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
            // Filter out empty rows
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
            // Generate friendly column names for 2-column or multi-column data
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

        $now = now();
        $records = [];
        $rowNum = 1;

        foreach ($dataRows as $row) {
            $mappedData = [];
            $dataColumnValue = null;

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
            }

            // The QR code contains the information from the adjacent column
            $qrContent = $dataColumnValue ?: ('SMP-' . str_pad((string)$rowNum, 4, '0', STR_PAD_LEFT));

            $records[] = [
                'batch_id' => $this->batchId,
                'filename' => $this->filename,
                'row_number' => $rowNum++,
                'qr_token' => Str::random(32),
                'qr_content' => $qrContent,
                'data' => json_encode($mappedData),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            foreach (array_chunk($records, 200) as $chunk) {
                PengujianItem::insert($chunk);
            }
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
