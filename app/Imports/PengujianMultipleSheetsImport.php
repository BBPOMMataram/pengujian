<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;

class PengujianMultipleSheetsImport implements WithMultipleSheets, WithCalculatedFormulas, HasReferencesToOtherSheets
{
    protected string $fileGroupId;
    protected string $filename;
    protected array $sheetNames;

    public function __construct(string $fileGroupId, string $filename, array $sheetNames)
    {
        $this->fileGroupId = $fileGroupId;
        $this->filename = $filename;
        $this->sheetNames = $sheetNames;
    }

    public function sheets(): array
    {
        $sheets = [];
        $isMulti = count($this->sheetNames) > 1;

        foreach ($this->sheetNames as $name) {
            $batchId = $isMulti 
                ? ($this->fileGroupId . '__' . Str::slug($name))
                : $this->fileGroupId;

            $sheets[$name] = new PengujianImport($batchId, $this->filename, $name, $this->fileGroupId);
        }

        return $sheets;
    }
}

