<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student');

        $userId = null;
        if ($studentId) {
            $student = \App\Models\Student::find($studentId);
            $userId = $student?->user_id;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $this->isMethod('POST')
                ? ['required', 'string', 'min:6']
                : ['nullable', 'string', 'min:6'],
            'class_id' => ['required', 'exists:classes,id'],
        ];
    }
}
