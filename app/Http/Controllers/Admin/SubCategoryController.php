<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubCategoryRequest $request, Category $category)
    {
        try {
            $this->categoryService->storeSubCategory($category, $request->validated());
            return redirect()->back()->with('success', 'Sub Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan sub kategori.')->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubCategoryRequest $request, SubCategory $subCategory)
    {
        try {
            $this->categoryService->updateSubCategory($subCategory, $request->validated());
            return redirect()->back()->with('success', 'Sub Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui sub kategori.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        try {
            $this->categoryService->deleteSubCategory($subCategory);
            return redirect()->back()->with('success', 'Sub Kategori dan semua soal didalamnya berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus sub kategori.');
        }
    }
}
