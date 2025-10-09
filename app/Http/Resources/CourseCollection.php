<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CourseCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($course) {
                return [
                    'id' => $course->id,
                    'status' => $course->status,
                    'title' => $course->title,
                    'subtitle' => $course->subtitle,
                    'image' => env('CLOUDFLARE_R2_URL') . '/' . $course->image,
                    'description' => $course->description,
                    'price' => $course->price,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'average_rating' => $course->reviews->avg('review_num'),
                    'instructor' => $course->instructor
                ];
            }),
            'pagination' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
            ]
        ];
    }

    public function paginationInformation($request, $paginated, $default)
    {
        // Remove the default pagination meta data
        unset($default['links']);
        unset($default['meta']);

        return $default;
    }
}
