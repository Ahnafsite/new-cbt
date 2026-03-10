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

class ClassStudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
        private readonly StudentImportService $studentImportService,
    ) {
    }

    public function index(Request $request, Classes $class): Response
    {
        $students = Student::with(['user:id,name,email'])
            ->where('class_id', $class->id)
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('Admin/Classes/Students', [
            'schoolClass' => $class,
            'students' => $students,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StudentRequest $request, Classes $class): RedirectResponse
    {
        $data = $request->validated();
        $data['class_id'] = $class->id;

        $this->studentService->store($data);

        return redirect()->back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function update(StudentRequest $request, Classes $class, Student $student): RedirectResponse
    {
        $this->studentService->update($student, $request->validated());

        return redirect()->back()->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Classes $class, Student $student): RedirectResponse
    {
        $this->studentService->delete($student);

        return redirect()->back()->with('success', 'Siswa berhasil dihapus dari kelas.');
    }

    public function importPreview(StudentImportRequest $request, Classes $class)
    {
        $file = $request->file('file');
        $result = $this->studentImportService->preview($file, $class->id);

        // Store file in temp for subsequent import
        $path = $file->store('imports/temp', 'local');

        return response()->json([
            ...$result,
            'temp_path' => $path,
        ]);
    }

    public function import(Request $request, Classes $class): RedirectResponse
    {
        $request->validate([
            'temp_path' => 'required|string',
        ]);

        $fullPath = storage_path('app/private/' . $request->temp_path);

        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'File impor tidak ditemukan. Silakan unggah ulang.');
        }

        $file = new \Illuminate\Http\UploadedFile($fullPath, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $result = $this->studentImportService->import($file, $class->id);

        // Clean up temp file
        @unlink($fullPath);

        $message = "Berhasil mengimpor {$result['imported']} siswa ke kelas {$class->title}.";

        if (!empty($result['errors'])) {
            $errorCount = count($result['errors']);
            $message .= " {$errorCount} baris memiliki kesalahan.";
            return redirect()->back()->with('warning', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    public function downloadTemplate(Classes $class)
    {
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $class->title);
        return Excel::download(
            new StudentImportTemplate(true, $class->title),
            "template_impor_siswa_{$safeTitle}.xlsx"
        );
    }
}
