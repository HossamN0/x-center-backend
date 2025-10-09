<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CourseChapterCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    public function paginationInformation($request, $paginated, $default)
    {
        // Remove the default pagination meta data
        unset($default['links']);
        unset($default['meta']['links']);

        return $default;
    }
}
