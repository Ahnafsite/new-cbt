<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentImportTemplate implements FromArray, WithHeadings, WithStyles
{
    private bool $includeClassColumn;
    private ?string $presetClassName;

    public function __construct(bool $includeClassColumn = true, ?string $presetClassName = null)
    {
        $this->includeClassColumn = $includeClassColumn;
        $this->presetClassName = $presetClassName;
    }

    public function headings(): array
    {
        $headers = ['nama', 'email', 'password'];

        if ($this->includeClassColumn) {
            $headers[] = 'kelas';
        }

        return $headers;
    }

    public function array(): array
    {
        if ($this->includeClassColumn) {
            $class1 = $this->presetClassName ?? 'XII IPA 1';
            $class2 = $this->presetClassName ?? 'XII IPA 2';

            return [
                ['Budi Santoso', 'budi@contoh.com', 'password123', $class1],
                ['Siti Aminah', 'siti@contoh.com', 'password123', $class2],
            ];
        }

        return [
            ['Budi Santoso', 'budi@contoh.com', 'password123'],
            ['Siti Aminah', 'siti@contoh.com', 'password123'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
    }
}
