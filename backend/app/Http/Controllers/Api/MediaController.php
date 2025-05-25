<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Media;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of media for a specific conference.
     */
    public function index(Request $request, Conference $conference): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'collection' => 'nullable|string|in:images,documents,general',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $query = $conference->media();

        // Filter by collection if specified
        if ($request->filled('collection')) {
            $query->where('collection_name', $request->collection);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 15);
        $media = $query->paginate($perPage);

        // Transform media data
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
                'custom_properties' => $mediaItem->custom_properties,
                'uploaded_by' => $mediaItem->uploaded_by,
                'created_at' => $mediaItem->created_at,
                'updated_at' => $mediaItem->updated_at,
            ];
        });

        $media->setCollection($transformedMedia);

        return $this->paginatedResponse($media, null, 'Media retrieved successfully');
    }

    /**
     * Store a newly created media resource.
     */
    public function store(Request $request, Conference $conference): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200', // 50MB max
            'collection' => [
                'required',
                'string',
                Rule::in(['images', 'documents', 'general'])
            ],
            'name' => 'nullable|string|max:255',
            'custom_properties' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $file = $request->file('file');
        $collection = $request->collection;

        // Validate file type based on collection
        $mimeValidation = $this->validateMimeType($file, $collection);
        if (!$mimeValidation['valid']) {
            return $this->errorResponse($mimeValidation['message'], 422);
        }

        try {
            $mediaAdder = $conference->addMediaFromRequest('file')
                ->toMediaCollection($collection);

            // Set custom name if provided
            if ($request->filled('name')) {
                $mediaAdder->usingName($request->name);
            }

            // Set custom properties if provided
            if ($request->filled('custom_properties')) {
                $mediaAdder->withCustomProperties($request->custom_properties);
            }

            $media = $mediaAdder;

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
                'custom_properties' => $media->custom_properties,
                'uploaded_by' => $media->uploaded_by,
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];

            return $this->successResponse($transformedMedia, 'Media uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload media: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified media resource.
     */
    public function show(Conference $conference, Media $media): JsonResponse
    {
        // Ensure media belongs to the conference
        if ($media->model_id !== $conference->id || $media->model_type !== Conference::class) {
            return $this->errorResponse('Media not found for this conference', 404);
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
            'custom_properties' => $media->custom_properties,
            'uploaded_by' => $media->uploaded_by,
            'created_at' => $media->created_at,
            'updated_at' => $media->updated_at,
        ];

        return $this->successResponse($transformedMedia, 'Media retrieved successfully');
    }

    /**
     * Update the specified media resource.
     */
    public function update(Request $request, Conference $conference, Media $media): JsonResponse
    {
        // Ensure media belongs to the conference
        if ($media->model_id !== $conference->id || $media->model_type !== Conference::class) {
            return $this->errorResponse('Media not found for this conference', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'custom_properties' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        try {
            // Update name if provided
            if ($request->filled('name')) {
                $media->name = $request->name;
            }

            // Update custom properties if provided
            if ($request->filled('custom_properties')) {
                $media->custom_properties = $request->custom_properties;
            }

            $media->save();

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
                'custom_properties' => $media->custom_properties,
                'uploaded_by' => $media->uploaded_by,
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];

            return $this->successResponse($transformedMedia, 'Media updated successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update media: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified media resource.
     */
    public function destroy(Conference $conference, Media $media): JsonResponse
    {
        // Ensure media belongs to the conference
        if ($media->model_id !== $conference->id || $media->model_type !== Conference::class) {
            return $this->errorResponse('Media not found for this conference', 404);
        }

        try {
            $media->delete();
            return $this->successResponse(null, 'Media deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete media: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download the specified media file.
     */
    public function download(Conference $conference, Media $media): mixed
    {
        // Ensure media belongs to the conference
        if ($media->model_id !== $conference->id || $media->model_type !== Conference::class) {
            return $this->errorResponse('Media not found for this conference', 404);
        }

        try {
            return response()->download($media->getPath(), $media->file_name);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to download media: ' . $e->getMessage(), 500);
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

    /**
     * Validate MIME type based on collection.
     */
    private function validateMimeType($file, string $collection): array
    {
        $allowedMimeTypes = [
            'images' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'documents' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'general' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'video/mp4', 'video/avi', 'video/mov'
            ]
        ];

        $fileMimeType = $file->getMimeType();
        $allowed = $allowedMimeTypes[$collection] ?? [];

        if (!in_array($fileMimeType, $allowed)) {
            return [
                'valid' => false,
                'message' => "File type {$fileMimeType} is not allowed for collection {$collection}"
            ];
        }

        return ['valid' => true];
    }
}