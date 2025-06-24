<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\ConferenceLockService;
use App\Models\Conference;
use App\Mail\UserCredentials;
use App\Mail\UserAccountDeleted;  // Add this import
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;

    protected $lockService;

    public function __construct(ConferenceLockService $lockService)
    {
        $this->lockService = $lockService;
    }

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }
        
        $query = User::query()->with(['roles', 'university']);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('roles') && !empty($request->roles)) {
            $roles = explode(',', $request->roles);
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }

        // University filter
        if ($request->has('university_id') && !empty($request->university_id)) {
            $query->where('university_id', $request->university_id);
        }

        // Sort options with consistent ordering
        $sortField = in_array($request->sort_field, ['name', 'email', 'created_at', 'updated_at'])
                ? $request->sort_field
                : 'created_at';
        
        $sortOrder = in_array(strtolower($request->sort_order), ['asc', 'desc'])
                ? strtolower($request->sort_order)
                : 'desc';
        
        // Primary sort by the requested field
        $query->orderBy($sortField, $sortOrder);
        $query->orderBy('id', $sortOrder);

        // Pagination
        if ($request->has('page') || $request->has('per_page')) {
            $perPage = min(max(intval($request->per_page ?? 10), 1), 100);
            $users = $query->paginate($perPage)->withQueryString();
            return $this->paginatedResponse($users, UserResource::collection($users));
        } else {
            $users = $query->get();
            return $this->successResponse(
                UserResource::collection($users),
                'Users retrieved successfully'
            );
        }
    }

    public function current(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'university']);

        return $this->successResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:admin,editor',
            'university_id' => 'required|exists:universities,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        if ($request->role === 'admin' && !$request->user()->hasPermissionTo('manage.admin')) {
            return $this->errorResponse('You do not have permission to create admin users', 403);
        }
        
        if ($request->role === 'editor' && !$request->user()->hasPermissionTo('manage.editor')) {
            return $this->errorResponse('You do not have permission to create editor users', 403);
        }

        $password = $request->password ?? $this->generateRandomPassword();

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'university_id' => $request->university_id,
            'must_change_password' => true,
        ]);

        $newUser->assignRole($request->role);

        // Send welcome email with credentials and creator info
        $this->sendCredentialsEmail($newUser, $password, true, [], $request->user());

        return $this->successResponse(
            [
                'user' => new UserResource($newUser->load(['roles', 'university'])),
                'generated_password' => $request->password ? null : $password
            ],
            'User created successfully',
            201
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }
        
        // Check if current user is trying to edit a super_admin or admin
        $currentUser = $request->user();
        if (!$currentUser->hasRole('super_admin')) {
            if ($user->hasRole('super_admin')) {
                return $this->errorResponse('You cannot edit super admin users', 403);
            }
            
            if ($user->hasRole('admin') && $currentUser->hasRole('admin')) {
                return $this->errorResponse('You cannot edit other admin users', 403);
            }
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|required|string|in:admin,editor',
            'university_id' => 'required|exists:universities,id',
            'generate_password' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        if ($request->has('role') && $request->role !== $user->roles->first()->name) {
            if ($request->role === 'admin' && !$request->user()->hasPermissionTo('manage.admin')) {
                return $this->errorResponse('You do not have permission to assign admin role', 403);
            }
            
            if ($request->role === 'editor' && !$request->user()->hasPermissionTo('manage.editor')) {
                return $this->errorResponse('You do not have permission to assign editor role', 403);
            }
        }

        // Track what changes are being made
        $changedFields = [];
        $originalUser = $user->replicate();
        $originalRole = $user->roles->first()->name ?? null;
        $originalUniversity = $user->university->full_name ?? null;
        
        $generatedPassword = null;
        $passwordChanged = false;
        
        if ($request->has('name') && $request->name !== $user->name) {
            $changedFields['name'] = [
                'old' => $user->name,
                'new' => $request->name
            ];
            $user->name = $request->name;
        }
        
        if ($request->has('email') && $request->email !== $user->email) {
            $changedFields['email'] = [
                'old' => $user->email,
                'new' => $request->email
            ];
            $user->email = $request->email;
        }
        
        if ($request->has('generate_password') && $request->generate_password) {
            $generatedPassword = $this->generateRandomPassword();
            $user->password = Hash::make($generatedPassword);
            $user->must_change_password = true;
            $passwordChanged = true;
            $changedFields['password'] = [
                'old' => '********',
                'new' => $generatedPassword
            ];
        } else if ($request->has('password') && $request->password) {
            $generatedPassword = $request->password;
            $user->password = Hash::make($request->password);
            $user->must_change_password = true;
            $passwordChanged = true;
            $changedFields['password'] = [
                'old' => '********',
                'new' => $generatedPassword
            ];
        }
        
        if ($request->has('university_id') && $request->university_id != $user->university_id) {
            $newUniversity = \App\Models\University::find($request->university_id);
            $changedFields['university'] = [
                'old' => $originalUniversity,
                'new' => $newUniversity->full_name ?? 'Unknown'
            ];
            $user->university_id = $request->university_id;
        }
        
        // Check for role changes
        $roleChanged = false;
        if ($request->has('role') && !empty($user->roles) && $request->role !== $user->roles->first()->name) {
            $changedFields['role'] = [
                'old' => $originalRole,
                'new' => $request->role
            ];
            $roleChanged = true;
        }
        
        // Save user changes
        if (!empty($changedFields)) {
            $user->save();
            
            // Update role if changed
            if ($roleChanged) {
                $user->syncRoles([$request->role]);
                
                // If user was changed from editor to admin or super_admin, remove from conference editors
                if ($originalRole === 'editor' && in_array($request->role, ['admin', 'super_admin'])) {
                    $this->removeFromConferenceEditors($user);
                }
            }
            
            // Send email with updated information and modifier info
            $this->sendCredentialsEmail(
                $user->fresh(['roles', 'university']), 
                $generatedPassword, 
                false, 
                $changedFields,
                $request->user()  // Pass the current user who is making the changes
            );
        }

        $response = [
            'user' => new UserResource($user->load(['roles', 'university']))
        ];
        
        if ($generatedPassword) {
            $response['generated_password'] = $generatedPassword;
        }

        return $this->successResponse(
            $response,
            'User updated successfully'
        );
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('access.admin')) {
            return $this->errorResponse('Unauthorized', 403);
        }
        
        // Check if current user is trying to delete a super_admin or admin
        $currentUser = $request->user();
        if (!$currentUser->hasRole('super_admin')) {
            if ($user->hasRole('super_admin')) {
                return $this->errorResponse('You cannot delete super admin users', 403);
            }
            
            if ($user->hasRole('admin') && $currentUser->hasRole('admin')) {
                return $this->errorResponse('You cannot delete other admin users', 403);
            }
        }
        
        if ($user->hasRole('admin') && !$request->user()->hasPermissionTo('manage.admin')) {
            return $this->errorResponse('You do not have permission to delete admin users', 403);
        }
        
        if ($user->hasRole('editor') && !$request->user()->hasPermissionTo('manage.editor')) {
            return $this->errorResponse('You do not have permission to delete editor users', 403);
        }
        
        if ($user->hasRole('super_admin')) {
            return $this->errorResponse('Super admin users cannot be deleted', 403);
        }
        
        if ($user->id === auth()->id()) {
            return $this->errorResponse('You cannot delete your own account', 403);
        }

        // Load user relationships before deletion
        $userWithRelations = $user->load(['roles', 'university']);

        $conferences = Conference::where('created_by', $user->id)
            ->orWhereHas('editors', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->get();
    
        foreach ($conferences as $conference) {
            $this->lockService->releaseLock($conference->id, $user->id);
        }

        // Send account deletion notification email before deleting the user
        $this->sendAccountDeletionEmail($userWithRelations, $currentUser);

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
    
    /**
     * Remove user from all conference editor relationships
     */
    protected function removeFromConferenceEditors(User $user): void
    {
        try {
            // Get all conferences where the user is an editor
            $conferences = Conference::whereHas('editors', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->get();

            foreach ($conferences as $conference) {
                // Release any locks the user might have on the conference
                $this->lockService->releaseLock($conference->id, $user->id);
                
                // Remove the user from the conference editors
                $conference->editors()->detach($user->id);
            }

            Log::info('User removed from conference editors', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'conferences_count' => $conferences->count(),
                'conference_ids' => $conferences->pluck('id')->toArray()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove user from conference editors', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send credentials email to user
     */
    protected function sendCredentialsEmail(User $user, string $password = null, bool $isNewUser = false, array $changedFields = [], User $createdBy = null): void
    {
        try {
            Mail::to($user->email)->send(new UserCredentials($user, $password, $isNewUser, $changedFields, $createdBy));
            /*
            Log::info('Credentials email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_new_user' => $isNewUser,
                'changed_fields' => array_keys($changedFields),
                'created_by' => $createdBy ? $createdBy->id : null
            ]);
            */
        } catch (\Exception $e) {
            /*
            Log::error('Failed to send credentials email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'is_new_user' => $isNewUser,
                'changed_fields' => array_keys($changedFields),
                'created_by' => $createdBy ? $createdBy->id : null
            ]);
            */
        }
    }
    
    /**
     * Send account deletion notification email to user
     */
    protected function sendAccountDeletionEmail(User $user, User $deletedBy): void
    {
        try {
            Mail::to($user->email)->send(new UserAccountDeleted($user, $deletedBy));
            /*
            Log::info('Account deletion email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => $deletedBy->id
            ]);
            */
        } catch (\Exception $e) {
            /*
            Log::error('Failed to send account deletion email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => $deletedBy->id,
                'error' => $e->getMessage()
            ]);
            */
        }
    }
    
    protected function generateRandomPassword($length = 12): string
    {
        $uppercase = chr(rand(65, 90));
        $lowercase = chr(rand(97, 122));
        $number = (string)rand(0, 9);
        $special = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '=', '[', ']', '{', '}', ';', ':', ',', '.', '?'];
        $specialChar = $special[array_rand($special)];
        
        $remaining = $length - 4;
        $rest = Str::random($remaining);
        
        $password = str_shuffle($uppercase . $lowercase . $number . $specialChar . $rest);
        
        return $password;
    }
}