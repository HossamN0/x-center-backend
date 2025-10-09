<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseEnrollmentsController extends Controller
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
        $user = JWTAuth::parseToken()->authenticate();
        $rules = [
            'course_id' => 'required|exists:courses,id',
        ];

        if (!$user->isStudent()) {
            $rules['student_id'] = [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $userById = User::find($value);
                    if (!$userById || !$userById->isStudent()) {
                        $fail('The student must be a student');
                    }
                }
            ];
        }

        $validated = $request->validate($rules);
        if ($user && $user->isStudent()) {
            $validated['student_id'] = $user->id;
        } else {
            $validated['student_id'] = $request->student_id;
        }

        $enrollment = CourseEnrollment::create($validated);
        return response()->json([
            'message' => 'Enrollment created successfully',
            'data' => $enrollment
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
