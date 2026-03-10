<?php

namespace App\Services;

use App\Models\Classes;
use Illuminate\Pagination\LengthAwarePaginator;

class ClassService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Classes::query()
            ->with('user:id,name')
            ->withCount('students')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function store(array $data): Classes
    {
        return Classes::create($data);
    }

    public function update(Classes $class, array $data): Classes
    {
        $class->update($data);
        return $class->fresh();
    }

    public function delete(Classes $class): void
    {
        $class->delete();
    }
}
