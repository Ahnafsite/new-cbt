<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentImportTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentImportRequest;
use App\Http\Requests\StudentRequest;
use App\Models\Classes;
use App\Models\Student;
use App\Services\StudentImportService;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
        private readonly StudentImportService $studentImportService,
    ) {
    }

    public function index(Request $request): Response
    {
        $students = $this->studentService->list(
            $request->only(['search', 'class_id', 'per_page'])
        );

        $classes = Classes::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => $request->only(['search', 'class_id']),
        ]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $this->studentService->store($request->validated());

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->studentService->update($student, $request->validated());

        return redirect()->back()->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->studentService->delete($student);

        return redirect()->back()->with('success', 'Siswa berhasil dihapus.');
    }

    public function importPreview(StudentImportRequest $request)
    {
        $file = $request->file('file');
        $result = $this->studentImportService->preview($file);

        // Store file in temp for subsequent import
        $path = $file->store('imports/temp', 'local');

        return response()->json([
            ...$result,
            'temp_path' => $path,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'temp_path' => 'required|string',
        ]);

        $fullPath = storage_path('app/private/' . $request->temp_path);

        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'File impor tidak ditemukan. Silakan unggah ulang.');
        }

        $file = new \Illuminate\Http\UploadedFile($fullPath, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $result = $this->studentImportService->import($file);

        // Clean up temp file
        @unlink($fullPath);

        $message = "Berhasil mengimpor {$result['imported']} siswa.";

        if (!empty($result['errors'])) {
            $errorCount = count($result['errors']);
            $message .= " {$errorCount} baris memiliki kesalahan.";
            return redirect()->back()->with('warning', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        return Excel::download(new StudentImportTemplate(true), 'template_impor_siswa.xlsx');
    }
}
