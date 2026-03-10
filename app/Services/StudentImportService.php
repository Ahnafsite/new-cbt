<?php

namespace App\Services;

use App\Imports\StudentImport;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportService
{
    /**
     * Preview rows from an uploaded Excel file.
     * Validates each row and returns valid/invalid data.
     */
    public function preview(UploadedFile $file, ?int $classId = null): array
    {
        $rows = Excel::toArray(new \App\Imports\StudentPreviewImport, $file, null, \Maatwebsite\Excel\Excel::XLSX);
        $data = $rows[0] ?? []; // First sheet

        $validRows = [];
        $invalidRows = [];

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // header is row 1
            $rowData = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

            // Determine class
            $resolvedClassId = $classId;
            $className = null;

            if (!$resolvedClassId && isset($rowData['kelas'])) {
                $class = Classes::where('title', $rowData['kelas'])->first();
                if ($class) {
                    $resolvedClassId = $class->id;
                    $className = $class->title;
                }
            } elseif ($resolvedClassId) {
                $className = Classes::find($resolvedClassId)?->title;
            }

            $errors = [];

            // Validate required fields
            $validator = Validator::make($rowData, [
                'nama' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'nama.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
            }

            // Check unique email
            if (!empty($rowData['email'])) {
                $exists = User::where('email', $rowData['email'])->exists();
                if ($exists) {
                    $errors[] = "Email '{$rowData['email']}' sudah terdaftar.";
                }
            }

            // Check class
            if (!$resolvedClassId && !$classId) {
                if (empty($rowData['kelas'])) {
                    $errors[] = 'Kolom kelas wajib diisi.';
                } else {
                    $errors[] = "Kelas '{$rowData['kelas']}' tidak ditemukan.";
                }
            }

            $rowResult = [
                'row' => $rowNumber,
                'nama' => $rowData['nama'] ?? '',
                'email' => $rowData['email'] ?? '',
                'password' => !empty($rowData['password']) ? '••••••' : '',
                'kelas' => $className ?? ($rowData['kelas'] ?? ''),
            ];

            if (!empty($errors)) {
                $rowResult['errors'] = $errors;
                $invalidRows[] = $rowResult;
            } else {
                $validRows[] = $rowResult;
            }
        }

        return [
            'valid' => $validRows,
            'invalid' => $invalidRows,
            'total' => count($data),
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
        ];
    }

    /**
     * Import students from an uploaded Excel file.
     */
    public function import(UploadedFile $file, ?int $classId = null): array
    {
        $import = new StudentImport($classId);

        Excel::import($import, $file, null, \Maatwebsite\Excel\Excel::XLSX);

        return [
            'imported' => $import->imported,
            'errors' => $import->errors,
        ];
    }
}
