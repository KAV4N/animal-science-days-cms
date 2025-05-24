<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conference\ConferenceResource;
use App\Http\Resources\Conference\DecadeResource;
use App\Models\Conference;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\PageMenu\PageMenuResource;

class PublicConferenceController extends Controller
{
    use ApiResponse;

    /**
     * Get published conferences with filtering
     */
    public function index(Request $request): JsonResponse
    {
        
        if ($request->has('latest')) {
            return $this->latest();
        }
        
        $query = Conference::query()
            ->where('is_published', true);
        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('university_id') && !empty($request->university_id)) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->has('start_date_after')) {
            $query->where('start_date', '>=', $request->start_date_after);
        }

        if ($request->has('end_date_before')) {
            $query->where('end_date', '<=', $request->end_date_before);
        }

        // Apply sorting
        $sortField = in_array($request->sort_field, ['name', 'title', 'start_date', 'end_date', 'created_at']) 
            ? $request->sort_field 
            : 'start_date';
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'desc';
        $query->orderBy($sortField, $sortOrder);

        // Load relationships
        $query->with(['university']);

        // Handle pagination
        if ($request->has('page') || $request->has('per_page')) {
            $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
            $conferences = $query->paginate($perPage)->withQueryString();
            
            return $this->paginatedResponse(
                $conferences, 
                ConferenceResource::collection($conferences),
                'Published conferences retrieved successfully'
            );
        } else {
            $conferences = $query->get();
            
            return $this->successResponse(
                ConferenceResource::collection($conferences),
                'Published conferences retrieved successfully'
            );
        }
    }
  
    /**
     * Get latest conference with its published pages and page data
     */
    public function latest(): JsonResponse
    {
        $conference = Conference::where('is_published', true)
            ->where('is_latest', true)
            ->with(['university'])
            ->first();

        if (!$conference) {
            // Fallback to most recent published conference if no latest is marked
            $conference = Conference::where('is_published', true)
                ->with(['university'])
                ->orderBy('start_date', 'desc')
                ->first();
        }

        if (!$conference) {
            return $this->errorResponse('No published conferences found', 404);
        }

        // Get published pages with their page data for this conference
        $pages = $conference->pageMenus()
            ->where('is_published', true)
            ->with(['pageData' => function($query) {
                $query->where('is_published', true)
                    ->orderBy('order', 'asc');
            }])
            ->orderBy('order', 'asc')
            ->get();

        $conferenceData = new ConferenceResource($conference);
        $conferenceArray = $conferenceData->toArray(request());
        $conferenceArray['pages'] = PageMenuResource::collection($pages);

        return $this->successResponse(
            $conferenceArray,
            'Latest conference with pages and content retrieved successfully'
        );
    }

    /**
     * Get a specific published conference by slug with its published pages and page data
     */
    public function show(string $slug): JsonResponse
    {
        $conference = Conference::where('slug', $slug)
            ->where('is_published', true)
            ->with(['university'])
            ->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found', 404);
        }

        // Get published pages with their page data for this conference
        $pages = $conference->pageMenus()
            ->where('is_published', true)
            ->with(['pageData' => function($query) {
                $query->where('is_published', true)
                    ->orderBy('order', 'asc');
            }])
            ->orderBy('order', 'asc')
            ->get();

        $conferenceData = new ConferenceResource($conference);
        $conferenceArray = $conferenceData->toArray(request());
        $conferenceArray['pages'] = PageMenuResource::collection($pages);

        return $this->successResponse(
            $conferenceArray,
            'Conference with pages and content retrieved successfully'
        );
    }

    /**
     * Get conferences by decade
     */
    public function getByDecade(Request $request, int $decade): JsonResponse
    {
        // Validate decade format (e.g., 1990, 2000)
        if ($decade < 1900 || $decade > 2100) {
            return $this->errorResponse('Invalid decade. Year must be between 1900 and 2100', 400);
        }
        
        // Extract the start year from the decade parameter
        $startYear = $decade;
        $endYear = $startYear + 9;
        
        // Create date range for the decade
        $startDate = "{$startYear}-01-01";
        $endDate = "{$endYear}-12-31";
        
        $query = Conference::query()
            ->where('is_published', true)
            ->where(function($q) use ($startDate, $endDate) {
                // Conference dates that overlap with the decade range
                $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                // Or where the conference spans the entire decade
                ->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
            });
        
        // Apply sorting
        $sortField = in_array($request->sort_field, ['name', 'title', 'start_date', 'end_date', 'created_at']) 
            ? $request->sort_field 
            : 'start_date';
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc']) 
            ? strtolower($request->sort_order) 
            : 'desc';
        $query->orderBy($sortField, $sortOrder);
        
        // Load relationships
        $query->with(['university']);
        
        // Get all conferences for the decade
        $conferences = $query->get();
        
        return $this->successResponse(
            ConferenceResource::collection($conferences),
            "Conferences from {$decade}s retrieved successfully"
        );
    }
    
    /**
     * Get available decades with conference counts
     */
    public function getDecades(): JsonResponse
    {
        // Get all published conferences
        $conferences = Conference::where('is_published', true)
            ->get(['start_date', 'end_date']);
        
        $decadesData = [];
        
        // Process each conference to determine which decades it belongs to
        foreach ($conferences as $conference) {
            $startYear = (int) $conference->start_date->format('Y');
            $endYear = (int) $conference->end_date->format('Y');
            
            // Calculate the decades this conference spans
            $startDecade = floor($startYear / 10) * 10;
            $endDecade = floor($endYear / 10) * 10;
            
            // Add all decades this conference spans to our collection
            for ($decade = $startDecade; $decade <= $endDecade; $decade += 10) {
                $decadeKey = $decade;
                
                if (!isset($decadesData[$decadeKey])) {
                    $decadesData[$decadeKey] = [
                        'decade' => $decade,
                        'count' => 0
                    ];
                }
                
                $decadesData[$decadeKey]['count']++;
            }
        }
        
        // Sort decades chronologically
        ksort($decadesData);
        
        return $this->successResponse(
            DecadeResource::collection(collect(array_values($decadesData))),
            'Conference decades retrieved successfully'
        );
    }
}