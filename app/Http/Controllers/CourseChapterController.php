<?php

namespace App\Http\Controllers;

use App\CourseAuthorization;
use App\Http\Resources\CourseChapterCollection;
use App\Models\CourseChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseChapterController extends Controller
{

    use CourseAuthorization;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $authorization = $this->authorizeCourseAccess($id, $user);
        if ($authorization !== true) {
            return response()->json([
                'message' => 'You are not authorized to access this course'
            ], 403);
        }

        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $status = $request->get('status');

        $query = CourseChapter::where('course_id', $id);
        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }
        $chapters = $query->paginate($limit);
        if (!$chapters->count()) {
            return response()->json(['message' => 'No chapters found'], 404);
        }
        return new CourseChapterCollection($chapters);
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
            'course_id' => 'required|exists:courses,id',
            'status' => ['sometimes', Rule::in(['opened', 'closed'])],
            'title' => 'required|string|max:225',
            'content' => 'required|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('content')) {
            $validated['content'] = $request->file('content')->store('chapters', 'r2');
        }

        $chapter = CourseChapter::create($validated);
        return response()->json([
            'message' => 'Chapter created successfully',
            'data' => $chapter
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $authorization = $this->authorizeChapterAccess($id, $user);
        if ($authorization !== true) {
            return response()->json([
                'message' => 'You are not authorized to access this chapter'
            ], 403);
        }

        $chapter = CourseChapter::findOrFail($id);
        return response()->json([
            'message' => 'Chapter retrieved successfully',
            'data' => $chapter
        ], 200);
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
        try {
            $rules = [
                'title' => 'sometimes|string|max:225',
                'status' => ['sometimes', Rule::in(['opened', 'closed'])],
            ];

            $courseChapter = CourseChapter::findOrFail($id);
            $validated = $request->validate($rules);
            $courseChapter->update($validated);

            return response()->json([
                'message' => 'Chapter updated successfully',
                'data' => $courseChapter
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
            $chapter = CourseChapter::findOrFail($id);
            Storage::disk('r2')->delete($chapter->content);
            $chapter->delete();
            return response()->json([
                'message' => 'Chapter deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
