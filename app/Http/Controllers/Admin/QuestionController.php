<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Models\SubCategory;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    private QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function index(Request $request): Response
    {
        $questions = Question::with(['subCategory.category', 'creator:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->sub_category_id, function ($query, $subCategoryId) {
                $query->where('sub_category_id', $subCategoryId);
            })
            ->latest()
            ->paginate($request->input('per_page', 15))
            ->withQueryString();

        $subCategories = \App\Models\SubCategory::orderBy('title')->get(['id', 'title']);

        return Inertia::render('Admin/Questions/Index', [
            'questions' => $questions,
            'subCategories' => $subCategories,
            'filters' => $request->only(['search', 'sub_category_id']),
        ]);
    }

    public function create(): Response
    {
        $categories = \App\Models\Category::with('subCategories')->orderBy('title')->get();

        return Inertia::render('Admin/Questions/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(QuestionRequest $request)
    {
        try {
            $this->questionService->store($request->validated());
            return redirect()->route('questions.index')->with('success', 'Pertanyaan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pertanyaan. ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Question $question): Response
    {
        $question->load(['answers', 'images']);

        $categories = \App\Models\Category::with('subCategories')->orderBy('title')->get();

        return Inertia::render('Admin/Questions/Edit', [
            'question' => $question,
            'categories' => $categories,
        ]);
    }

    public function update(QuestionRequest $request, Question $question)
    {
        try {
            $this->questionService->update($question, $request->validated());
            return redirect()->route('questions.index')->with('success', 'Pertanyaan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui pertanyaan. ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Question $question)
    {
        try {
            $this->questionService->delete($question);
            return redirect()->route('questions.index')->with('success', 'Pertanyaan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pertanyaan. ' . $e->getMessage());
        }
    }
}
