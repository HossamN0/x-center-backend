<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseCollection;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $status = $request->get('status');

        $query = Course::query();
        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('subtitle', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }
        $courses = Course::with([
            'instructor' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'phone');
            }
        ])->paginate($limit);

        return new CourseCollection($courses);
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
            'status' => ['sometimes', Rule::in(['active', 'in_active'])],
            'price' => 'required|integer',
            'title' => 'required|string|max:225',
            'subtitle' => 'required|string|max:225',
            'description' => 'required|string',
        ];

        if ($user->isAdmin()) {
            $rules['instructor_id'] = [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || !$user->isInstructor()) {
                        $fail('The selected instructor_id does not belong to an instructor.');
                    }
                }
            ];
        }

        $validated = $request->validate($rules);

        if ($user->isInstructor()) {
            $validated['instructor_id'] = $user->id;
        }

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully',
            'data' => $course
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
