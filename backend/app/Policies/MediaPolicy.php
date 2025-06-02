<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conference;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any media.
     */
    public function viewAny(User $user, Conference $conference): bool
    {
        // Super admins and admins can view all media
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // If conference is published, everyone can view
        if ($conference->is_published) {
            return true;
        }

        // For unpublished conferences, editors need to be assigned to the conference
        if ($user->hasRole('editor')) {
            return $user->conferences()->where('conference_id', $conference->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can view the media.
     */
    public function view(User $user, Media $media): bool
    {
        // Get the conference from the media model
        $conference = $this->getConferenceFromMedia($media);
        
        if (!$conference) {
            return false;
        }

        // Super admins and admins can view all media
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // If conference is published, everyone can view
        if ($conference->is_published) {
            return true;
        }

        // For unpublished conferences, editors need to be assigned to the conference
        if ($user->hasRole('editor')) {
            return $user->conferences()->where('conference_id', $conference->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user, Conference $conference): bool
    {
        // Super admins and admins can create media in any conference
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Editors can create media only if assigned to the conference
        if ($user->hasRole('editor')) {
            return $user->conferences()->where('conference_id', $conference->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the media.
     */
    public function update(User $user, Media $media): bool
    {
        // Get the conference from the media model
        $conference = $this->getConferenceFromMedia($media);
        
        if (!$conference) {
            return false;
        }

        // Super admins and admins can update all media
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // If conference is published, no one except super_admin/admin can update
        if ($conference->is_published) {
            return false;
        }

        // For unpublished conferences, editors can update if assigned to the conference
        if ($user->hasRole('editor')) {
            return $user->conferences()->where('conference_id', $conference->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media): bool
    {
        // Get the conference from the media model
        $conference = $this->getConferenceFromMedia($media);
        
        if (!$conference) {
            return false;
        }

        // Super admins and admins can delete all media
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // If conference is published, no one except super_admin/admin can delete
        if ($conference->is_published) {
            return false;
        }

        // For unpublished conferences, editors can delete if assigned to the conference
        if ($user->hasRole('editor')) {
            return $user->conferences()->where('conference_id', $conference->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can download the media.
     */
    public function download(User $user, Media $media): bool
    {
        // Download follows the same rules as view
        return $this->view($user, $media);
    }

    /**
     * Determine whether the user can serve/display the media.
     */
    public function serve(?User $user, Media $media): bool
    {
        // Get the conference from the media model
        $conference = $this->getConferenceFromMedia($media);
        
        if (!$conference) {
            return false;
        }

        // If user is not authenticated
        if (!$user) {
            // Only allow if conference is published
            return $conference->is_published;
        }

        // If user is authenticated, use the view policy
        return $this->view($user, $media);
    }

    /**
     * Determine if the user can manage all media (bulk operations, etc.).
     */
    public function manage(User $user, Conference $conference): bool
    {
        // Super admins and admins can manage all media
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Editors can manage media only if assigned to the conference and it's not published
        if ($user->hasRole('editor')) {
            return !$conference->is_published && 
                   $user->conferences()->where('conference_id', $conference->id)->exists();
        }

        return false;
    }

    /**
     * Get conference from media model.
     */
    private function getConferenceFromMedia(Media $media): ?Conference
    {
        if ($media->model_type === Conference::class) {
            return Conference::find($media->model_id);
        }

        return null;
    }

    /**
     * Determine if user can access media endpoints for a conference
     */
    public function access(User $user, Conference $conference): bool
    {
        return $this->viewAny($user, $conference);
    }
}