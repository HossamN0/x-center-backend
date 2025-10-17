<?php

namespace App\Http\Controllers;

use App\CourseAuthorization;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseImageRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseCollection;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use function Laravel\Prompts\select;

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

        $query = Course::with([
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

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $courses = $query->paginate($limit);

        if ($courses->total() === 0) {
            return response()->json([
                'data' => [],
                'message' => 'No courses found',
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'total' => $courses->total(),
                    'per_page' => $courses->perPage(),
                ]
            ], 200);
        }

        if ($courses->isEmpty() && $courses->currentPage() > $courses->lastPage()) {
            return response()->json([
                'data' => [],
                'message' => 'No courses on this page',
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'total' => $courses->total(),
                    'per_page' => $courses->perPage(),
                ]
            ], 200);
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
    public function store(StoreCourseRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validated = $request->validated();

        if (!$user->isInstructor()) {
            $validated['instructor_id'] = [
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
                },
                'enrollments' => function ($query) {
                    $query->select('id', 'course_id', 'student_id', 'status');
                }
            ]);

            if ($authorization) {
                $course->with([
                    'chapters' => function ($query) use ($user) {
                        $query->select('id', 'course_id', 'title', 'content')
                            ->with([
                                'exam' => function ($q) use ($user) {
                                    $q->select('id', 'chapter_id', 'status')
                                        ->withExists([
                                            'studentDegree as degree' => function ($degreeQuery) use ($user) {
                                                $degreeQuery->where('student_id', $user->id);
                                            }
                                        ]);
                                }
                            ]);
                    }
                ]);
            }

            $course = $course->findOrFail($id);
            $course['image'] = env('CLOUDFLARE_R2_URL') . '/' . $course['image'];

            if ($authorization && $course->chapters) {
                $course->chapters->transform(function ($chapter) {
                    if ($chapter->content) {
                        $chapter->content = env('CLOUDFLARE_R2_URL') . '/' . $chapter->content;
                    }
                    return $chapter;
                });
            }

            if ($user) {
                $enrollments = $course->enrollments->where('student_id', $user->id)->first();
                $course['enroll'] = $enrollments->status ?? false;
            }
            unset($course['enrollments']);
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
    public function editImage(UpdateCourseImageRequest $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $validated = $request->validated();
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
    public function update(UpdateCourseRequest $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $validated = $request->validated();
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
