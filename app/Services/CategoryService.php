<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;
use App\Services\QuestionService;

class CategoryService
{
    private QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Store new Category.
     */
    public function storeCategory(array $data)
    {
        return Category::create([
            'title' => $data['title'],
            'desc' => $data['desc'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Update Category.
     */
    public function updateCategory(Category $category, array $data)
    {
        $category->update([
            'title' => $data['title'],
            'desc' => $data['desc'] ?? null,
        ]);
        return $category;
    }

    /**
     * Delete Category and all cascading SubCategories and their questions/images structurally.
     */
    public function deleteCategory(Category $category)
    {
        return DB::transaction(function () use ($category) {
            foreach ($category->subCategories as $subCategory) {
                // Delete all questions associated with the sub-category using the QuestionService
                // which handles physical image deletion inside storage explicitly
                foreach ($subCategory->questions as $question) {
                    $this->questionService->delete($question);
                }
            }
            // Once children questions are physically handled, sub-categories cascade mechanically
            $category->delete();
            return true;
        });
    }

    /**
     * Store new SubCategory.
     */
    public function storeSubCategory(Category $category, array $data)
    {
        return $category->subCategories()->create([
            'title' => $data['title'],
            'desc' => $data['desc'] ?? null,
        ]);
    }

    /**
     * Update SubCategory.
     */
    public function updateSubCategory(SubCategory $subCategory, array $data)
    {
        $subCategory->update([
            'title' => $data['title'],
            'desc' => $data['desc'] ?? null,
        ]);
        return $subCategory;
    }

    /**
     * Delete SubCategory and its questions/images structurally.
     */
    public function deleteSubCategory(SubCategory $subCategory)
    {
        return DB::transaction(function () use ($subCategory) {
            foreach ($subCategory->questions as $question) {
                $this->questionService->delete($question);
            }
            $subCategory->delete();
            return true;
        });
    }
}
