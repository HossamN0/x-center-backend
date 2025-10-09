<?php

namespace App\Http\Controllers;

use App\CourseAuthorization;
use App\Http\Resources\CourseCollection;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseController extends Controller
{
    use CourseAuthorization;
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
            },
            'reviews' => function ($query) {
                $query->select('id', 'course_id', 'student_id', 'review_num', 'description')
                    ->with([
                        'student' => function ($query) {
                            $query->select('id', 'first_name', 'last_name', 'email', 'phone');
                        }
                    ]);
            }
        ])->paginate($limit);
        if (!$courses->count()) {
            return response()->json(['message' => 'No courses found'], 404);
        }
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        if (!$user->isInstructor()) {
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
        $validated['image'] = $request->file('image')->store('courses', 'r2');
        if ($user->isInstructor()) {
            $validated['instructor_id'] = $user->id;
        } else {
            $validated['instructor_id'] = $request->instructor_id;
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
        $user = null;
        try {
            if ($token = JWTAuth::getToken()) {
                $user = JWTAuth::authenticate($token);
            }

            $authorization = $this->authorizeCourseAccess($id, $user);
            $course = Course::with([
                'instructor' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'email', 'phone');
                },
                'reviews' => function ($query) {
                    $query->select('id', 'course_id', 'student_id', 'review_num', 'description')
                        ->with([
                            'student' => function ($query) {
                                $query->select('id', 'first_name', 'last_name', 'email', 'phone');
                            }
                        ]);
                }
            ]);

            if ($authorization) {
                $course->with([
                    'chapters' => function ($query) {
                        $query->select('id', 'course_id', 'title', 'content');
                    }
                ]);
            }

            $course = $course->findOrFail($id);
            $course['image'] = env('CLOUDFLARE_R2_URL') . '/' . $course['image'];

            return response()->json([
                'message' => 'Course retrieved successfully',
                'data' => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editImage(Request $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            if ($request->hasFile('image')) {
                Storage::disk('r2')->delete($course->image);
                $validated['image'] = $request->file('image')->store('courses', 'r2');
            }
            $course->update($validated);

            return response()->json([
                'message' => 'Image updated successfully',
                'data' => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $rules = [
                'status' => ['sometimes', Rule::in(['active', 'in_active'])],
                'price' => 'sometimes|integer',
                'title' => 'sometimes|string|max:225',
                'subtitle' => 'sometimes|string|max:225',
                'description' => 'sometimes|string',
            ];
            $validated = $request->validate($rules);
            $course->update($validated);

            return response()->json([
                'message' => 'Course updated successfully',
                'data' => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::findOrFail($id);
            Storage::disk('r2')->delete($course->image);
            $course->delete();
            return response()->json([
                'message' => 'Course deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
