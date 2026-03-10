<?php

namespace App\Imports;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];
    private ?int $classId;

    public function __construct(?int $classId = null)
    {
        $this->classId = $classId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $row->toArray();

            // Determine class
            $classId = $this->classId;

            if (!$classId && isset($data['kelas'])) {
                $class = Classes::where('title', $data['kelas'])->first();
                if (!$class) {
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'messages' => ["Kelas '{$data['kelas']}' tidak ditemukan."],
                    ];
                    continue;
                }
                $classId = $class->id;
            }

            if (!$classId) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'messages' => ['Kelas tidak ditentukan.'],
                ];
                continue;
            }

            $validator = Validator::make($data, [
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'messages' => $validator->errors()->all(),
                ];
                continue;
            }

            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('student');

            Student::create([
                'user_id' => $user->id,
                'class_id' => $classId,
            ]);

            $this->imported++;
        }
    }
}
