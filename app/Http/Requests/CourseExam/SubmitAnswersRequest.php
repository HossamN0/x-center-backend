<?php

namespace App\Http\Requests\CourseExam;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswersRequest extends FormRequest
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
            'exam_id' => ['required', 'exists:course_exams,id'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:exam_questions,id'],
            'answers.*.answer_id' => ['required', 'exists:question_answers,id'],
        ];
    }
}
