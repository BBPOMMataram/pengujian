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
        $now = now();
        $records = [];

        foreach ($rows as $row) {
            // Filter out empty rows
            $filtered = array_filter($row->toArray(), fn($v) => !is_null($v) && trim((string)$v) !== '');
            if (empty($filtered)) {
                continue;
            }

            $records[] = [
                'batch_id' => $this->batchId,
                'filename' => $this->filename,
                'row_number' => $rowNum++,
                'qr_token' => Str::random(32),
                'data' => json_encode($row->toArray()),
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
}
