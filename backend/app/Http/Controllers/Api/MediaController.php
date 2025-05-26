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
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $transformedMedia = $media->getCollection()->map(function ($mediaItem) use ($conference) {
            return [
                'id' => $mediaItem->id,
                'uuid' => $mediaItem->uuid,
                'collection_name' => $mediaItem->collection_name,
                'name' => $mediaItem->name,
                'file_name' => $mediaItem->file_name,
                'mime_type' => $mediaItem->mime_type,
                'size' => $mediaItem->size,
                'size_human' => $mediaItem->humanReadableSize,
                'url' => route('api.media.serve', ['conference' => $conference->id, 'mediaId' => $mediaItem->id]),
                'download_url' => route('api.media.download', ['conference' => $conference->id, 'mediaId' => $mediaItem->id]),
                'conversions' => $this->getConversions($mediaItem),
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
            $mediaAdder = $conference->addMediaFromRequest('file');
            
            // Set custom name if provided
            if ($request->filled('name')) {
                $mediaAdder->usingName($request->name);
            }
            
            $media = $mediaAdder->toMediaCollection($collection);

            $transformedMedia = [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'collection_name' => $media->collection_name,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'size_human' => $media->humanReadableSize,
                'url' => route('api.media.serve', ['conference' => $conference->id, 'mediaId' => $media->id]),
                'download_url' => route('api.media.download', ['conference' => $conference->id, 'mediaId' => $media->id]),
                'conversions' => $this->getConversions($media),
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
            'url' => route('api.media.serve', ['conference' => $conference->id, 'mediaId' => $media->id]),
            'download_url' => route('api.media.download', ['conference' => $conference->id, 'mediaId' => $media->id]),
            'conversions' => $this->getConversions($media),
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
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        try {
            // Update name if provided
            if ($request->filled('name')) {
                $media->name = $request->name;
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
                'url' => route('api.media.serve', ['conference' => $conference->id, 'mediaId' => $media->id]),
                'download_url' => route('api.media.download', ['conference' => $conference->id, 'mediaId' => $media->id]),
                'conversions' => $this->getConversions($media),
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
     * Download the specified media file with proper filename and extension.
     */
    public function download(Conference $conference, $mediaId): StreamedResponse|JsonResponse
    {
        // Find media by ID manually instead of relying on route model binding
        $media = $conference->media()->where('id', $mediaId)->first();
        
        if (!$media) {
            return $this->errorResponse('Media not found for this conference', 404);
        }

        try {
            $path = $media->getPath();
            
            // Check if file exists
            if (!file_exists($path)) {
                return $this->errorResponse('File not found on disk', 404);
            }
            
            // Check if file is readable
            if (!is_readable($path)) {
                return $this->errorResponse('File is not readable', 500);
            }
            
            // Get the file extension from the original file
            $extension = File::extension($path);
            
            // Create download filename with proper extension
            $downloadName = $this->createDownloadFilename($media, $extension);
            
            // Log download attempt for debugging
            \Log::info('Media download attempt', [
                'media_id' => $mediaId,
                'conference_id' => $conference->id,
                'original_filename' => $media->file_name,
                'download_filename' => $downloadName,
                'extension' => $extension,
                'mime_type' => $media->mime_type,
                'path' => $path
            ]);
            
            // Use Storage facade for better file handling
            $relativePath = str_replace(storage_path('app/'), '', $path);
            
            return Storage::download($relativePath, $downloadName, [
                'Content-Type' => $media->mime_type,
                'Content-Length' => $media->size,
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Media download failed', [
                'media_id' => $mediaId,
                'conference_id' => $conference->id,
                'path' => $media->getPath(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Failed to download media: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Serve/display the specified media file inline.
     */
    public function serve(Conference $conference, $mediaId): mixed
    {
        // Find media by ID manually instead of relying on route model binding
        $media = $conference->media()->where('id', $mediaId)->first();
        
        if (!$media) {
            return $this->errorResponse('Media not found for this conference', 404);
        }

        try {
            $path = $media->getPath();
            
            // Check if file exists
            if (!file_exists($path)) {
                return $this->errorResponse('File not found on disk', 404);
            }
            
            // Check if file is readable
            if (!is_readable($path)) {
                return $this->errorResponse('File is not readable', 500);
            }
            
            // Get file contents
            $fileContent = file_get_contents($path);
            
            if ($fileContent === false) {
                return $this->errorResponse('Unable to read file', 500);
            }
            
            // Set appropriate headers for inline display
            $headers = [
                'Content-Type' => $media->mime_type,
                'Content-Length' => $media->size,
                'Content-Disposition' => 'inline; filename="' . ($media->name ?: $media->file_name) . '"',
                'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
                'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
                'Last-Modified' => gmdate('D, d M Y H:i:s \G\M\T', filemtime($path)),
                'ETag' => '"' . md5_file($path) . '"',
            ];
            
            // Add CORS headers if needed for cross-origin requests
            $headers['Access-Control-Allow-Origin'] = '*';
            $headers['Access-Control-Allow-Methods'] = 'GET, HEAD, OPTIONS';
            $headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization';
            
            return response($fileContent, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Media serve failed', [
                'media_id' => $mediaId,
                'conference_id' => $conference->id,
                'path' => $media->getPath(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Failed to serve media: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a proper download filename with extension.
     */
    private function createDownloadFilename(Media $media, string $extension): string
    {
        // Use custom name if available, otherwise use original file name
        $baseName = $media->name ?: pathinfo($media->file_name, PATHINFO_FILENAME);
        
        // Clean the filename (remove special characters that might cause issues)
        $baseName = preg_replace('/[^A-Za-z0-9\-_\s]/', '', $baseName);
        $baseName = trim($baseName);
        
        // If we don't have an extension, try to determine it from mime type
        if (empty($extension)) {
            $extension = $this->getExtensionFromMimeType($media->mime_type);
        }
        
        // Ensure we have an extension
        if (empty($extension)) {
            // Fallback: try to get extension from original filename
            $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
        }
        
        // Build the final filename
        $filename = $baseName;
        if (!empty($extension)) {
            $filename .= '.' . $extension;
        }
        
        return $filename;
    }

    /**
     * Get file extension from MIME type.
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $mimeToExtension = [
            // Images
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            
            // Documents
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            
            // Text files
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/rtf' => 'rtf',
            
            // Videos
            'video/mp4' => 'mp4',
            'video/avi' => 'avi',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            
            // Audio
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            
            // Archives
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-7z-compressed' => '7z',
        ];

        return $mimeToExtension[$mimeType] ?? '';
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