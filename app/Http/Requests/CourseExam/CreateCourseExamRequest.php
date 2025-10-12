<?php

namespace App\Http\Requests\CourseExam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCourseExamRequest extends FormRequest
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
            'chapter_id' => 'required|exists:course_chapters,id',
            'start_time' => 'required|date|date_format:Y-m-d H:i:s|after_or_equal:now',
            'end_time' => 'required|date|date_format:Y-m-d H:i:s|after:start_time',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.answers' => 'required|array|min:1',
            'questions.*.answers.*.answer' => 'required|string|max:255',
            'questions.*.answers.*.is_correct' => ['required', Rule::in(['0', '1'])],
        ];
    }
}
