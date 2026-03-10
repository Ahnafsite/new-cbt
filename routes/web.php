<?php

use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ClassStudentController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Admin Master Data Routes
    Route::resource('classes', ClassController::class)->except(['create', 'edit', 'show']);

    // Class Students (nested)
    Route::get('classes/{class}/students', [ClassStudentController::class, 'index'])->name('classes.students.index');
    Route::post('classes/{class}/students', [ClassStudentController::class, 'store'])->name('classes.students.store');
    Route::get('classes/{class}/students/template', [ClassStudentController::class, 'downloadTemplate'])->name('classes.students.template');
    Route::post('classes/{class}/students/import/preview', [ClassStudentController::class, 'importPreview'])->name('classes.students.import.preview');
    Route::post('classes/{class}/students/import', [ClassStudentController::class, 'import'])->name('classes.students.import');
    Route::put('classes/{class}/students/{student}', [ClassStudentController::class, 'update'])->name('classes.students.update');
    Route::delete('classes/{class}/students/{student}', [ClassStudentController::class, 'destroy'])->name('classes.students.destroy');

    Route::post('students/import/preview', [StudentController::class, 'importPreview'])->name('students.import.preview');
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
    Route::resource('students', StudentController::class)->except(['create', 'edit', 'show']);

    Route::resource('rooms', RoomController::class)->except(['create', 'edit', 'show']);
    Route::get('admin/settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::put('admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Categories & SubCategories
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['create', 'edit', 'show']);
    Route::post('categories/{category}/sub-categories', [\App\Http\Controllers\Admin\SubCategoryController::class, 'store'])->name('categories.subcategories.store');
    Route::put('sub-categories/{subCategory}', [\App\Http\Controllers\Admin\SubCategoryController::class, 'update'])->name('subcategories.update');
    Route::delete('sub-categories/{subCategory}', [\App\Http\Controllers\Admin\SubCategoryController::class, 'destroy'])->name('subcategories.destroy');

    // Questions
    Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class)->except(['show']);
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
});

require __DIR__ . '/settings.php';

