<?php

namespace App\Services;

use App\Models\Conference;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
     * Release all locks for a specific user
     *
     * @param int $userId
     * @return int Number of locks released
     */
    public function releaseAllUserLocks(int $userId): int
    {
        $locksReleased = 0;
        
        // Query the cache table directly since we're using database driver
        $cacheEntries = DB::table('cache')
            ->where('key', 'like', $this->cacheKeyPrefix . '%')
            ->get();
        
        foreach ($cacheEntries as $entry) {
            $lockData = unserialize($entry->value);
            
            // Check if this is a valid lock entry and belongs to the user
            if (is_array($lockData) && isset($lockData['user_id']) && $lockData['user_id'] === $userId) {
                // Extract conference ID from cache key
                $conferenceId = str_replace($this->cacheKeyPrefix, '', $entry->key);
                
                // Remove the lock
                Cache::forget($entry->key);
                $locksReleased++;
            }
        }
        
        return $locksReleased;
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
     * Get all active locks for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserLocks(int $userId): array
    {
        $userLocks = [];
        
        // Query the cache table directly
        $cacheEntries = DB::table('cache')
            ->where('key', 'like', $this->cacheKeyPrefix . '%')
            ->get();
        
        foreach ($cacheEntries as $entry) {
            $lockData = unserialize($entry->value);
            
            // Check if this is a valid lock entry and belongs to the user
            if (is_array($lockData) && isset($lockData['user_id']) && $lockData['user_id'] === $userId) {
                // Check if lock hasn't expired
                $lockTime = Carbon::parse($lockData['locked_at']);
                if ($lockTime->diffInMinutes(now()) < $this->lockTimeout) {
                    $conferenceId = str_replace($this->cacheKeyPrefix, '', $entry->key);
                    $userLocks[] = [
                        'conference_id' => $conferenceId,
                        'lock_data' => $lockData
                    ];
                }
            }
        }
        
        return $userLocks;
    }

    /**
     * Clean up expired locks
     *
     * @return int Number of expired locks cleaned up
     */
    public function cleanupExpiredLocks(): int
    {
        $cleanedUp = 0;
        
        // Query the cache table directly
        $cacheEntries = DB::table('cache')
            ->where('key', 'like', $this->cacheKeyPrefix . '%')
            ->get();
        
        foreach ($cacheEntries as $entry) {
            $lockData = unserialize($entry->value);
            
            // Check if this is a valid lock entry
            if (is_array($lockData) && isset($lockData['locked_at'])) {
                $lockTime = Carbon::parse($lockData['locked_at']);
                
                // Check if lock has expired
                if ($lockTime->diffInMinutes(now()) >= $this->lockTimeout) {
                    Cache::forget($entry->key);
                    $cleanedUp++;
                }
            }
        }
        
        return $cleanedUp;
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