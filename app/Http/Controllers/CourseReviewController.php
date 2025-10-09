<?php

namespace App\Http\Controllers;

use App\CourseAuthorization;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseReviewController extends Controller
{
    use CourseAuthorization;
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
        $user = JWTAuth::parseToken()->authenticate();
        if (!Course::find($request->course_id)) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        $authorized = $this->authorizeCourseAccess($request->course_id, $user);
        if ($authorized !== true) {
            return response()->json([
                'message' => 'You are not authorized to access this course'
            ], 403);
        }
        if (!$user->isStudent()) {
            return response()->json(['message' => 'You are not a student'], 403);
        }
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'description' => 'required|string',
            'review_num' => 'required|integer',
        ];
        $validated = $request->validate($rules);
        $validated['student_id'] = $user->id;

        $review = CourseReview::create($validated);

        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $review = CourseReview::findOrFail($id);
        $rules = [
            'description' => 'required|string',
            'review_num' => 'required|integer',
        ];
        $validated = $request->validate($rules);
        $review->update($validated);
        return response()->json([
            'message' => 'Review updated successfully',
            'data' => $review
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = CourseReview::find($id);
        $review->delete();
        return response()->json([
            'message' => 'Review deleted successfully'
        ], 200);
    }
}
