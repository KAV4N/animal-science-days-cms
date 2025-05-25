<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Media;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicMediaController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of public media for a specific conference.
     */
    public function index(Request $request, string $conferenceSlug): JsonResponse
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found or not published', 404);
        }

        $validator = \Validator::make($request->all(), [
            'collection' => 'nullable|string|in:images,documents,general',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $query = $conference->media();

        // Filter by collection if specified
        if ($request->filled('collection')) {
            $query->where('collection_name', $request->collection);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 15);
        $media = $query->paginate($perPage);

        // Transform media data for public consumption
        $transformedMedia = $media->getCollection()->map(function ($mediaItem) {
            return [
                'id' => $mediaItem->id,
                'uuid' => $mediaItem->uuid,
                'collection_name' => $mediaItem->collection_name,
                'name' => $mediaItem->name,
                'file_name' => $mediaItem->file_name,
                'mime_type' => $mediaItem->mime_type,
                'size' => $mediaItem->size,
                'size_human' => $mediaItem->humanReadableSize,
                'url' => $mediaItem->getUrl(),
                'conversions' => $this->getConversions($mediaItem),
                'created_at' => $mediaItem->created_at,
            ];
        });

        $media->setCollection($transformedMedia);

        return $this->paginatedResponse($media, null, 'Media retrieved successfully');
    }

    /**
     * Display the specified public media resource.
     */
    public function show(string $conferenceSlug, string $mediaUuid): JsonResponse
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found or not published', 404);
        }

        $media = $conference->media()->where('uuid', $mediaUuid)->first();

        if (!$media) {
            return $this->errorResponse('Media not found', 404);
        }

        $transformedMedia = [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'collection_name' => $media->collection_name,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'size_human' => $media->humanReadableSize,
            'url' => $media->getUrl(),
            'conversions' => $this->getConversions($media),
            'created_at' => $media->created_at,
        ];

        return $this->successResponse($transformedMedia, 'Media retrieved successfully');
    }

    /**
     * Download the specified public media file.
     */
    public function download(string $conferenceSlug, string $mediaUuid): mixed
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found or not published', 404);
        }

        $media = $conference->media()->where('uuid', $mediaUuid)->first();

        if (!$media) {
            return $this->errorResponse('Media not found', 404);
        }

        try {
            return response()->download($media->getPath(), $media->file_name);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to download media', 500);
        }
    }

    /**
     * Serve media file directly (for embedding).
     */
    public function serve(string $conferenceSlug, string $mediaUuid): mixed
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found or not published', 404);
        }

        $media = $conference->media()->where('uuid', $mediaUuid)->first();

        if (!$media) {
            return $this->errorResponse('Media not found', 404);
        }

        try {
            $path = $media->getPath();
            
            if (!file_exists($path)) {
                return $this->errorResponse('File not found', 404);
            }

            return response()->file($path, [
                'Content-Type' => $media->mime_type,
                'Content-Disposition' => 'inline; filename="' . $media->file_name . '"'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to serve media', 500);
        }
    }

    /**
     * Serve media conversion (thumbnail, preview).
     */
    public function serveConversion(string $conferenceSlug, string $mediaUuid, string $conversion): mixed
    {
        $conference = Conference::where('slug', $conferenceSlug)
            ->where('is_published', true)
            ->first();

        if (!$conference) {
            return $this->errorResponse('Conference not found or not published', 404);
        }

        $media = $conference->media()->where('uuid', $mediaUuid)->first();

        if (!$media) {
            return $this->errorResponse('Media not found', 404);
        }

        if (!in_array($conversion, ['thumb', 'preview'])) {
            return $this->errorResponse('Invalid conversion type', 400);
        }

        if (!$media->hasGeneratedConversion($conversion)) {
            return $this->errorResponse('Conversion not available', 404);
        }

        try {
            $path = $media->getPath($conversion);
            
            if (!file_exists($path)) {
                return $this->errorResponse('Conversion file not found', 404);
            }

            return response()->file($path, [
                'Content-Type' => 'image/jpeg', // Conversions are typically JPEG
                'Content-Disposition' => 'inline; filename="' . $conversion . '_' . $media->file_name . '"'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to serve conversion', 500);
        }
    }

    /**
     * Get media conversions URLs.
     */
    private function getConversions(Media $media): array
    {
        $conversions = [];
        
        if ($media->hasGeneratedConversion('thumb')) {
            $conversions['thumb'] = $media->getUrl('thumb');
        }
        
        if ($media->hasGeneratedConversion('preview')) {
            $conversions['preview'] = $media->getUrl('preview');
        }

        return $conversions;
    }
}