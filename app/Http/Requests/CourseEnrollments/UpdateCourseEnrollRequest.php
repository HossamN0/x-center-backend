<?php

namespace App\Http\Requests\CourseEnrollments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseEnrollRequest extends FormRequest
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
            'progress' => 'sometimes|numeric|min:0|max:100',
            'status' => ['sometimes', Rule::in(['pending', 'accepted', 'rejected'])],
        ];
    }
}
