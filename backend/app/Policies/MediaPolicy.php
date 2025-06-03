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
            return $media->model; 
        }

        return null;
    }
}