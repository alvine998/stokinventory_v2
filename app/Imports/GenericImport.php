<?php

namespace App\Imports;

use Closure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GenericImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function __construct(private Closure $handler) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            ($this->handler)($row->toArray());
        }
    }
}
