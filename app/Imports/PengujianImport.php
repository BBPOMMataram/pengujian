<?php

namespace App\Imports;

use App\Models\PengujianItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PengujianImport implements ToCollection, WithHeadingRow
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
        $rowNum = 1;
        foreach ($rows as $row) {
            // Filter out empty rows
            $filtered = array_filter($row->toArray(), fn($v) => !is_null($v) && trim((string)$v) !== '');
            if (empty($filtered)) {
                continue;
            }

            // Extract best candidate for code and name
            $code = null;
            $name = null;

            foreach ($row as $key => $val) {
                $k = strtolower((string)$key);
                if (!$code && (str_contains($k, 'kode') || str_contains($k, 'code') || str_contains($k, 'no_') || str_contains($k, 'nomor'))) {
                    $code = (string)$val;
                }
                if (!$name && (str_contains($k, 'nama') || str_contains($k, 'sample') || str_contains($k, 'sampel') || str_contains($k, 'produk') || str_contains($k, 'item'))) {
                    $name = (string)$val;
                }
            }

            if (!$code) {
                $code = 'SMP-' . str_pad((string)$rowNum, 4, '0', STR_PAD_LEFT);
            }
            if (!$name) {
                $firstVal = reset($filtered);
                $name = (string)$firstVal;
            }

            PengujianItem::create([
                'batch_id' => $this->batchId,
                'filename' => $this->filename,
                'row_number' => $rowNum++,
                'code' => $code,
                'name' => $name,
                'qr_token' => Str::random(32),
                'data' => $row->toArray(),
            ]);
        }
    }
}
