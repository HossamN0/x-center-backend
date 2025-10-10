<?php

namespace App\Http\Requests\CourseChapters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChapterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'status' => ['sometimes', Rule::in(['opened', 'closed'])],
            'title' => 'required|string|max:225',
            'content' => 'required|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:2048',
        ];
    }
}
