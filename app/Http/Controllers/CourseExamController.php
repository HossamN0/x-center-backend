<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseExam\CreateCourseExamRequest;
use App\Models\CourseExam;
use App\Models\ExamQuestion;
use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    public function store(CreateCourseExamRequest $request)
    {
        $validated = $request->validated();

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
    public function show(string $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $exam = CourseExam::with('chapter.course.enrollments')->findOrFail($id);
        $course = $exam->chapter->course;
        $isInstructor = $user->isInstructor() && $course->instructor_id === $user->id;
        $isAdmin = $user->isAdmin();
        $isEnrolled = $course->enrollments->contains('student_id', $user->id);

        if (!($isInstructor || $isAdmin || $isEnrolled)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $exam->load([
            'questions' => function ($q) use ($isInstructor, $isAdmin) {
                if ($isInstructor || $isAdmin) {
                    $q->with('answers');
                }
            }
        ]);

        return response()->json([
            'questions' => $exam->questions
        ], 200);
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
