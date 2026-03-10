<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => ['required', 'string', 'in:multiple_choice,true_false,short_essay,essay'],
            'title' => ['required', 'string'],
            'sub_category_id' => ['required', 'exists:sub_categories,id'],
            'point' => ['required', 'integer', 'min:1'],
            'difficulty' => ['required', 'integer', 'min:1', 'max:5'],
            'question_images' => ['nullable', 'array', 'max:5'],
            'question_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];

        // Dynamic rules for multiple_choice
        if ($this->input('type') === 'multiple_choice') {
            $rules['answers'] = ['required', 'array', 'min:2'];
            $rules['answers.*.title'] = ['required_without:answers.*.image', 'nullable', 'string'];
            $rules['answers.*.image'] = ['nullable']; // Either string (existing) or file
            $rules['answers.*.is_true'] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Pertanyaan wajib diisi.',
            'type.required' => 'Tipe pertanyaan wajib dipilih.',
            'type.in' => 'Tipe pertanyaan tidak valid.',
            'sub_category_id.required' => 'Sub kategori wajib dipilih.',
            'sub_category_id.exists' => 'Sub kategori tidak valid.',
            'point.required' => 'Poin pertanyaan wajib diisi.',
            'difficulty.required' => 'Tingkat kesulitan wajib diisi.',
            'question_images.*.image' => 'File harus berupa gambar.',
            'question_images.*.max' => 'Ukuran gambar maksimal 2MB.',

            // Answer specific messages
            'answers.required' => 'Opsi jawaban wajib diisi untuk pilihan ganda.',
            'answers.min' => 'Minimal harus ada 2 opsi jawaban.',
            'answers.*.title.required_without' => 'Teks jawaban atau gambar jawaban wajib diisi.',
        ];
    }
}
