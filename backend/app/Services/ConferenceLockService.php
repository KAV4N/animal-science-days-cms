<?php

namespace App\Services;

use App\Models\Conference;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ConferenceLockService
{
    /**
     * The key prefix used for cache entries
     */
    protected string $cacheKeyPrefix = 'conference_lock:';
    
    /**
     * Lock timeout in minutes
     */
    protected int $lockTimeout = 30;

    /**
     * Attempt to acquire a lock for a conference
     *
     * @param Conference $conference
     * @param User $user
     * @return bool|array
     */
    public function acquireLock(Conference $conference, User $user): bool|array
    {
        $cacheKey = $this->getCacheKey($conference->id);

        $lockData = Cache::get($cacheKey);
        
        if ($lockData) {
            if ($lockData['user_id'] === $user->id) {
                $this->refreshLock($conference->id, $user->id);
                return true;
            }
            
            $lockTime = Carbon::parse($lockData['locked_at']);
            if ($lockTime->diffInMinutes(now()) >= $this->lockTimeout) {
                Cache::forget($cacheKey);
            } else {
                return $lockData;
            }
        }
        
        // Create a new lock
        $lockData = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'locked_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes($this->lockTimeout)->toIso8601String(),
        ];
        
        Cache::put($cacheKey, $lockData, now()->addMinutes($this->lockTimeout));
        return true;
    }
    
    /**
     * Release lock for a conference
     *
     * @param int $conferenceId
     * @param int $userId
     * @return bool
     */
    public function releaseLock(int $conferenceId, int $userId): bool
    {
        $cacheKey = $this->getCacheKey($conferenceId);
        $lockData = Cache::get($cacheKey);
        
        // Only the lock owner or an admin can release the lock
        if ($lockData && $lockData['user_id'] === $userId) {
            Cache::forget($cacheKey);
            return true;
        }
        
        return false;
    }

    /**
     * Force release a lock (for admins)
     *
     * @param int $conferenceId
     * @return bool
     */
    public function forceReleaseLock(int $conferenceId): bool
    {
        $cacheKey = $this->getCacheKey($conferenceId);
        Cache::forget($cacheKey);
        return true;
    }
    
    /**
     * Check if a conference is locked
     *
     * @param int $conferenceId
     * @return array|null
     */
    public function checkLock(int $conferenceId): ?array
    {
        $cacheKey = $this->getCacheKey($conferenceId);
        $lockData = Cache::get($cacheKey);
        
        if ($lockData) {
            $lockTime = Carbon::parse($lockData['locked_at']);
            
            // Check if lock has expired
            if ($lockTime->diffInMinutes(now()) >= $this->lockTimeout) {
                Cache::forget($cacheKey);
                return null;
            }
            
            return $lockData;
        }
        
        return null;
    }
    
    /**
     * Refresh the lock timer
     *
     * @param int $conferenceId
     * @param int $userId
     * @return bool
     */
    public function refreshLock(int $conferenceId, int $userId): bool
    {
        $cacheKey = $this->getCacheKey($conferenceId);
        $lockData = Cache::get($cacheKey);
        
        if ($lockData && $lockData['user_id'] === $userId) {
            $lockData['locked_at'] = now()->toIso8601String();
            $lockData['expires_at'] = now()->addMinutes($this->lockTimeout)->toIso8601String();
            
            Cache::put($cacheKey, $lockData, now()->addMinutes($this->lockTimeout));
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the cache key for a conference
     *
     * @param int $conferenceId
     * @return string
     */
    protected function getCacheKey(int $conferenceId): string
    {
        return $this->cacheKeyPrefix . $conferenceId;
    }
}