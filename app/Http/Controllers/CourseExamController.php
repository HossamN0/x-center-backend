<?php

namespace App\Http\Controllers;

use App\Models\CourseExam;
use App\Models\ExamQuestion;
use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:course_chapters,id',
            'start_time' => 'required|date|date_format:Y-m-d H:i:s|after_or_equal:now',
            'end_time' => 'required|date|date_format:Y-m-d H:i:s|after:start_time',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'questions.*.answers' => 'required|array|min:1',
            'questions.*.answers.*.answer' => 'required|string|max:255',
            'questions.*.answers.*.is_correct' => ['required', Rule::in(['0', '1'])],
        ]);

        DB::beginTransaction();

        try {
            $exam = CourseExam::create([
                'chapter_id' => $validated['chapter_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]);

            foreach ($validated['questions'] as $index => $questionData) {
                $imagePath = null;
                if ($request->hasFile("questions.{$index}.image")) {
                    $imagePath = $request->file("questions.{$index}.image")->store('questions', 'r2');
                }
                $question = ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question' => $questionData['question'],
                    'image' => $imagePath,
                ]);

                foreach ($questionData['answers'] as $answerData) {
                    QuestionAnswer::create([
                        'question_id' => $question->id,
                        'answer' => $answerData['answer'],
                        'is_correct' => $answerData['is_correct'],
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Exam created successfully',
                'data' => $exam
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CourseExam $courseExam)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseExam $courseExam)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseExam $courseExam)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseExam $courseExam)
    {
        //
    }
}
