<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentPreviewImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // Do nothing. This class is only used to format the data into an array with headers.
    }
}
