<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Student::query()
            ->with(['user:id,name,email', 'class:id,title,year'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['class_id'] ?? null, function ($query, $classId) {
                $query->where('class_id', $classId);
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function store(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('student');

            return Student::create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'],
            ]);
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $student->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            if (!empty($data['password'])) {
                $student->user->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            $student->update([
                'class_id' => $data['class_id'],
            ]);

            return $student->fresh(['user', 'class']);
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->delete();
            $user->delete();
        });
    }
}
