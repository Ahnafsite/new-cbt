<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use App\Models\Classes;
use App\Models\User;
use App\Services\ClassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClassController extends Controller
{
    public function __construct(
        private readonly ClassService $classService
    ) {
    }

    public function index(Request $request): Response
    {
        $classes = $this->classService->list($request->only(['search', 'per_page']));

        $teachers = User::role(['super_admin', 'teacher'])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes,
            'teachers' => $teachers,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(ClassRequest $request): RedirectResponse
    {
        $this->classService->store($request->validated());

        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(ClassRequest $request, Classes $class): RedirectResponse
    {
        $this->classService->update($class, $request->validated());

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classes $class): RedirectResponse
    {
        $this->classService->delete($class);

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }
}
