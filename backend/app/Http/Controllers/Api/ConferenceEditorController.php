<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conference\ConferenceEditorStoreRequest;
use App\Http\Resources\Conference\ConferenceEditorResource;
use App\Http\Resources\User\UserResource;
use App\Models\Conference;
use App\Models\ConferenceEditor;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConferenceEditorController extends Controller
{
    use ApiResponse;

    public function index(Conference $conference): JsonResponse
    {
        $editors = $conference->editors()->with('university')->get();

        return $this->successResponse(
            UserResource::collection($editors),
            'Conference editors retrieved successfully'
        );
    }

    public function store(ConferenceEditorStoreRequest $request, Conference $conference): JsonResponse
    {
        $editor = $conference->conferenceEditors()->create([
            'user_id' => $request->user_id,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        return $this->successResponse(
            new ConferenceEditorResource($editor->load('user', 'assignedByUser')),
            'Editor added successfully',
            201
        );
    }

    public function destroy(Conference $conference, User $user): JsonResponse
    {
        $deleted = ConferenceEditor::where('conference_id', $conference->id)
            ->where('user_id', $user->id)
            ->delete();

        if (!$deleted) {
            return $this->errorResponse('Editor not found', 404);
        }

        return $this->successResponse(null, 'Editor removed successfully');
    }
}