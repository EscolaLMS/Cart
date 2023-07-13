<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;
use Maatwebsite\Excel\Excel;

class ExportFormatEnum extends BasicEnum
{
    const CSV = 'csv';
    const XLS = 'xls';
    const XLSX = 'xlsx';

    public function getFilename(string $basename): string
    {
        return match ($this->value) {
            self::XLS => $basename . '.xls',
            self::XLSX => $basename . '.xlsx',
            default =>  $basename . '.csv',
        };
    }

    public function getWriterType(): string
    {
        return match ($this->value) {
            self::XLS => Excel::XLS,
            self::XLSX => Excel::XLSX,
            default => Excel::CSV,
        };
    }
}
